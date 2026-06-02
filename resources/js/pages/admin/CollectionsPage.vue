<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type CollectionRow = {
    id: number;
    start: string;
    end: string;
    access_token: string;
    linkOneDoc: string;
    url: string;
    is_active: boolean;
    is_upcoming: boolean;
};

type CompanyRow = {
    id: number;
    name: string;
    slug: string;
    email: string;
    employee_count: number | null;
    created_at: string | null;
    is_public: boolean;
    trophy: boolean;
    collections: CollectionRow[];
};

type CompanyFilter = 'active-first' | 'active-only' | 'incoming-only' | 'created-desc' | 'created-asc';

type AppState = { csrfToken: string };

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

const companies = ref<CompanyRow[]>([]);
const loadingCompanies = ref(true);
const loadError = ref<string | null>(null);
const searchQuery = ref('');
const companyFilter = ref<CompanyFilter>('active-first');
const deletingCompanyId = ref<number | null>(null);
const disabledLinkMessage = ref<string | null>(null);
const copyMessage = ref<string | null>(null);
let disabledLinkTimer: number | undefined;
let copyMessageTimer: number | undefined;

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

async function fetchCompanies() {
    loadingCompanies.value = true;
    loadError.value = null;
    try {
        const res = await fetch('/admin/api/companies', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
            companies.value = await res.json();
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Erreur lors du chargement des campagnes.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loadingCompanies.value = false;
    }
}

function goToCreate(event: Event) {
    event.preventDefault();
    navigate('/admin/companies/create');
}

function activeCollections(company: CompanyRow): CollectionRow[] {
    return company.collections
        .filter((collection) => collection.is_active)
        .sort((left, right) => new Date(left.start).getTime() - new Date(right.start).getTime());
}

function upcomingCollections(company: CompanyRow): CollectionRow[] {
    return company.collections
        .filter((collection) => collection.is_upcoming)
        .sort((left, right) => new Date(left.start).getTime() - new Date(right.start).getTime());
}

function inactiveCollections(company: CompanyRow): CollectionRow[] {
    return company.collections
        .filter((collection) => !collection.is_active && !collection.is_upcoming)
        .sort((left, right) => new Date(right.start).getTime() - new Date(left.start).getTime());
}

function hasActiveCollection(company: CompanyRow): boolean {
    return activeCollections(company).length > 0;
}

function hasUpcomingCollection(company: CompanyRow): boolean {
    return upcomingCollections(company).length > 0;
}

function companyActionPath(company: CompanyRow): string {
    return `/admin/companies/${company.id}/edit?newCollection=1`;
}

function companyActionLabel(company: CompanyRow): string {
    return 'Nouvelle campagne';
}

function companyCreatedTimestamp(company: CompanyRow): number {
    return company.created_at ? new Date(company.created_at).getTime() : 0;
}

const displayedCompanies = computed(() => {
    const query = searchQuery.value.trim().toLowerCase();
    let results = companies.value.filter((company) => {
        if (!query) {
            return true;
        }

        return [
            company.name,
            company.email,
            company.slug,
        ].some((value) => value.toLowerCase().includes(query));
    });

    if (companyFilter.value === 'active-only') {
        results = results.filter(hasActiveCollection);
    }

    if (companyFilter.value === 'incoming-only') {
        results = results.filter(hasUpcomingCollection);
    }

    const sortByCreatedDesc = (left: CompanyRow, right: CompanyRow) => (
        companyCreatedTimestamp(right) - companyCreatedTimestamp(left)
    );

    const sortByCreatedAsc = (left: CompanyRow, right: CompanyRow) => (
        companyCreatedTimestamp(left) - companyCreatedTimestamp(right)
    );

    if (companyFilter.value === 'created-desc') {
        return [...results].sort(sortByCreatedDesc);
    }

    if (companyFilter.value === 'created-asc') {
        return [...results].sort(sortByCreatedAsc);
    }

    return [...results].sort((left, right) => {
        const companyRank = (company: CompanyRow) => {
            if (hasActiveCollection(company)) {
                return 0;
            }

            if (hasUpcomingCollection(company)) {
                return 1;
            }

            return 2;
        };

        const rankDiff = companyRank(left) - companyRank(right);

        if (rankDiff !== 0) {
            return rankDiff;
        }

        return sortByCreatedDesc(left, right);
    });
});

function showDisabledLinkMessage() {
    disabledLinkMessage.value = "Cette collecte est terminée. Le lien public renvoie une 404.";

    if (disabledLinkTimer) {
        window.clearTimeout(disabledLinkTimer);
    }

    disabledLinkTimer = window.setTimeout(() => {
        disabledLinkMessage.value = null;
    }, 3500);
}

function absoluteCollectionUrl(collection: CollectionRow): string {
    return new URL(collection.url, window.location.origin).toString();
}

async function copyCollectionUrl(collection: CollectionRow) {
    try {
        await navigator.clipboard.writeText(absoluteCollectionUrl(collection));
        copyMessage.value = 'URL complète copiée.';
    } catch {
        copyMessage.value = 'Impossible de copier l’URL.';
    }

    if (copyMessageTimer) {
        window.clearTimeout(copyMessageTimer);
    }

    copyMessageTimer = window.setTimeout(() => {
        copyMessage.value = null;
    }, 2500);
}

async function deleteCompany(company: CompanyRow) {
    if (!window.confirm(`Supprimer la campagne "${company.name}" ?`)) {
        return;
    }

    deletingCompanyId.value = company.id;
    loadError.value = null;

    try {
        const res = await fetch(`/admin/companies/${company.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (res.ok) {
            companies.value = companies.value.filter((item) => item.id !== company.id);
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Erreur lors de la suppression de la campagne.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        deletingCompanyId.value = null;
    }
}

onMounted(fetchCompanies);
</script>

<template>
    <AdminLayout>
        <!-- Header -->
        <div v-if="flashMessage" class="alert alert-success mb-6">
            <span class="cooper-baseline">{{ flashMessage }}</span>
        </div>
        <div v-if="disabledLinkMessage" class="toast toast-end toast-top z-50">
            <div class="alert alert-warning shadow-sm">
                <span class="cooper-baseline">{{ disabledLinkMessage }}</span>
            </div>
        </div>
        <div v-if="copyMessage" class="toast toast-end toast-top z-50">
            <div class="alert alert-success shadow-sm">
                <span class="cooper-baseline">{{ copyMessage }}</span>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-between">
            <h1 class="cooper-text-baseline text-2xl font-semibold">Campagnes</h1>
            <a href="/admin/companies/create" class="btn btn-primary btn-sm font-cooper" @click="goToCreate">
                <span class="material-symbols-outlined" style="font-size: 18px;" aria-hidden="true">add</span>
                <span class="cooper-baseline">Créer une nouvelle campagne</span>
            </a>
        </div>

        <section class="mb-6 rounded-box border border-base-300 bg-base-200/35 p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <label class="input input-bordered flex w-full max-w-xl items-center gap-3 bg-white">
                    <span class="material-symbols-outlined text-base-content/45" aria-hidden="true">search</span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="cooper-input-baseline w-full font-cooper"
                        placeholder="Rechercher par entreprise ou email"
                    />
                </label>

                <label class="flex items-center gap-3 self-start lg:self-auto">
                    <span class="cooper-baseline text-sm font-medium text-base-content/55">Filtrer</span>
                    <select v-model="companyFilter" class="select select-bordered bg-white font-cooper">
                        <option value="active-first">Campagnes actives d'abord</option>
                        <option value="active-only">Campagnes actives uniquement</option>
                        <option value="incoming-only">Campagnes à venir uniquement</option>
                        <option value="created-desc">Date de création · plus récentes</option>
                        <option value="created-asc">Date de création · plus anciennes</option>
                    </select>
                </label>
            </div>
        </section>

        <!-- List -->
        <div v-if="loadingCompanies" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
        <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>
        <p v-else-if="companies.length === 0" class="cooper-text-baseline text-sm text-base-content/50">Aucune campagne. Créez-en une.</p>
        <p v-else-if="displayedCompanies.length === 0" class="cooper-text-baseline text-sm text-base-content/50">Aucun résultat pour ce filtre.</p>

        <div v-else class="space-y-4">
            <div
                v-for="company in displayedCompanies"
                :key="company.id"
                class="rounded-box border border-base-300 bg-base-100 p-5"
            >
                <!-- Company row -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="cooper-text-baseline font-semibold">{{ company.name }}</p>
                        <p class="cooper-text-baseline mt-0.5 text-sm text-base-content/50">
                            <span>{{ company.slug }}</span>
                            · {{ company.email }}
                            <span v-if="company.employee_count"> · {{ company.employee_count }} employés</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <a
                            :href="companyActionPath(company)"
                            class="btn btn-ghost btn-sm font-cooper"
                            @click.prevent="navigate(companyActionPath(company))"
                        >
                            <span class="cooper-baseline">{{ companyActionLabel(company) }}</span>
                        </a>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm border-red-600 font-cooper text-red-700 hover:border-red-700 hover:bg-red-700 hover:text-white"
                            :disabled="deletingCompanyId === company.id"
                            @click="deleteCompany(company)"
                        >
                            <span class="cooper-baseline">{{ deletingCompanyId === company.id ? '...' : 'Supprimer' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Collections -->
                <div class="mt-4">
                    <p class="cooper-text-baseline mb-2 text-xs font-medium tracking-wider text-base-content/40 uppercase">
                        Collecte active
                    </p>
                    <p v-if="company.collections.length === 0" class="cooper-text-baseline text-sm text-base-content/40">Aucune collecte.</p>
                    <p v-else-if="activeCollections(company).length === 0" class="cooper-text-baseline text-sm text-base-content/40">
                        Aucune collecte active pour le moment.
                    </p>

                    <div
                        v-for="col in activeCollections(company)"
                        :key="col.id"
                        class="mt-1 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 flex-1 items-center gap-4">
                                <span class="cooper-baseline shrink-0 text-sm font-medium text-emerald-800">
                                    {{ formatDate(col.start) }} → {{ formatDate(col.end) }}
                                </span>
                                <a :href="col.url" target="_blank" class="link link-primary min-w-0 truncate text-sm">
                                    <span class="cooper-baseline">{{ col.url }}</span>
                                </a>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <span v-if="company.trophy || !company.is_public" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-white px-3 py-1.5 text-xs font-medium text-[#5A002A]">
                                    <svg v-if="company.trophy" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076A10.744 10.744 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/></svg>
                                    <span class="cooper-baseline">{{ company.trophy ? 'Participation au Prix du Cœur' : 'Participation anonyme' }}</span>
                                </span>
                                <div class="flex items-center gap-1">
                                    <button
                                        type="button"
                                        title="Copier l’URL complète"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-emerald-700 transition-colors hover:bg-white hover:text-emerald-900"
                                        @click="copyCollectionUrl(col)"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="9" y="9" width="13" height="13" rx="2" />
                                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                        </svg>
                                    </button>
                                    <a
                                        :href="col.url"
                                        target="_blank"
                                        title="Ouvrir la page co-brandée"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full text-emerald-700 transition-colors hover:bg-white hover:text-emerald-900"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </a>
                                    <a
                                        :href="`/admin/companies/${company.id}/edit?collection=${col.id}`"
                                        class="btn btn-ghost btn-sm font-cooper text-emerald-900 hover:bg-white"
                                        @click.prevent="navigate(`/admin/companies/${company.id}/edit?collection=${col.id}`)"
                                    >
                                        <span class="cooper-baseline">Modifier</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="upcomingCollections(company).length > 0" class="mt-4">
                        <p class="cooper-text-baseline mb-2 text-xs font-medium tracking-wider text-base-content/40 uppercase">
                            Collecte à venir
                        </p>

                        <div
                            v-for="col in upcomingCollections(company)"
                            :key="col.id"
                            class="mt-1 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex min-w-0 flex-1 items-center gap-4">
                                    <span class="cooper-baseline shrink-0 text-sm font-medium text-amber-900">
                                        {{ formatDate(col.start) }} → {{ formatDate(col.end) }}
                                    </span>
                                    <span class="cooper-baseline min-w-0 truncate text-sm text-amber-800/70">
                                        {{ col.url }}
                                    </span>
                                </div>
                                <div class="flex shrink-0 items-center gap-3">
                                    <span v-if="company.trophy || !company.is_public" class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-white px-3 py-1.5 text-xs font-medium text-[#5A002A]">
                                        <svg v-if="company.trophy" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                                        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076A10.744 10.744 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/></svg>
                                        <span class="cooper-baseline">{{ company.trophy ? 'Participation au Prix du Cœur' : 'Participation anonyme' }}</span>
                                    </span>
                                    <a
                                        :href="`/admin/companies/${company.id}/edit?collection=${col.id}`"
                                        class="btn btn-ghost btn-sm font-cooper text-amber-900 hover:bg-white"
                                        @click.prevent="navigate(`/admin/companies/${company.id}/edit?collection=${col.id}`)"
                                    >
                                        <span class="cooper-baseline">Modifier</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <details v-if="inactiveCollections(company).length > 0" class="collapse-arrow collapse mt-3 bg-base-200">
                        <summary class="collapse-title min-h-11 px-4 py-3 text-sm font-medium text-base-content/60">
                            <span class="cooper-baseline">Historique ({{ inactiveCollections(company).length }})</span>
                        </summary>
                        <div class="collapse-content px-4 pb-4">
                            <div
                                v-for="col in inactiveCollections(company)"
                                :key="col.id"
                                class="flex items-center justify-between gap-3 border-t border-base-300 py-3 first:border-t-0"
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-4">
                                    <span class="cooper-baseline shrink-0 text-sm text-base-content/55">
                                        {{ formatDate(col.start) }} → {{ formatDate(col.end) }}
                                    </span>
                                    <span class="cooper-baseline min-w-0 truncate text-xs text-base-content/35">{{ col.url }}</span>
                                    <button type="button" class="btn btn-ghost btn-xs cursor-not-allowed font-cooper opacity-50" @click="showDisabledLinkMessage">
                                        <span class="cooper-baseline">Lien désactivé</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
