<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { readableTextColor } from '../../utils/contrast';

type CollectionRow = {
    id: number;
    start: string;
    end: string;
    access_token: string;
    linkOneDoc: string;
    url: string;
    is_active: boolean;
    is_upcoming: boolean;
    is_public_link_enabled: boolean;
};

type CompanyRow = {
    id: number;
    name: string;
    slug: string;
    email: string;
    primaryColor: string | null;
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
const currentPage = ref(1);
const deletingCompanyId = ref<number | null>(null);
const disabledLinkMessage = ref<string | null>(null);
const copyMessage = ref<string | null>(null);
const pageSize = 10;
let disabledLinkTimer: number | undefined;
let copyMessageTimer: number | undefined;

function parseLocalDate(iso: string): Date {
    const normalized = iso.replace(' ', 'T').replace(/Z$/, '').replace(/\.\d+$/, '');
    const [datePart, timePart = '00:00'] = normalized.split('T');
    const [year, month, day] = datePart.split('-').map(Number);
    const [hours, minutes] = timePart.split(':').map(Number);
    return new Date(year, month - 1, day, hours, minutes);
}

function formatDate(iso: string): string {
    return parseLocalDate(iso).toLocaleString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function companyBadgeLabel(name: string): string {
    const sanitized = name.replace(/[^a-zA-Z0-9]/g, '');

    if (!sanitized) {
        return '—';
    }

    const first = sanitized[0]?.toUpperCase() ?? '';
    const second = sanitized[1] ? sanitized[1].toUpperCase() : '';

    return `${first}${second}`;
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

function companyEditPath(company: CompanyRow): string {
    return `/admin/companies/${company.id}/edit`;
}

function companyParticipationPath(company: CompanyRow): string {
    return `/admin/companies/${company.id}/edit#participation-settings`;
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

const totalPages = computed(() => Math.ceil(displayedCompanies.value.length / pageSize));

const paginatedCompanies = computed(() => {
    const startIndex = (currentPage.value - 1) * pageSize;
    return displayedCompanies.value.slice(startIndex, startIndex + pageSize);
});

const visiblePages = computed(() => Array.from({ length: totalPages.value }, (_, index) => index + 1));

watch([searchQuery, companyFilter], () => {
    currentPage.value = 1;
});

watch(totalPages, (pageCount) => {
    if (pageCount === 0) {
        currentPage.value = 1;
        return;
    }

    if (currentPage.value > pageCount) {
        currentPage.value = pageCount;
    }
});

watch(currentPage, async () => {
    await nextTick();

    const scrollContainer = document.querySelector('main');

    if (scrollContainer instanceof HTMLElement) {
        scrollContainer.scrollTo({ top: 0, behavior: 'smooth' });
        return;
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
});

function showDisabledLinkMessage() {
    disabledLinkMessage.value = "Le lien public s'active un mois avant le début de la collecte.";

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
            <span>{{ flashMessage }}</span>
        </div>
        <div v-if="disabledLinkMessage" class="toast toast-end toast-top z-50">
            <div class="alert alert-warning shadow-sm">
                <span>{{ disabledLinkMessage }}</span>
            </div>
        </div>
        <div v-if="copyMessage" class="toast toast-end toast-top z-50">
            <div class="alert alert-success shadow-sm">
                <span>{{ copyMessage }}</span>
            </div>
        </div>

        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-3xl font-semibold">Collectes co-brandées</h1>
            <a
                href="/admin/companies/create"
                class="btn btn-sm border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] font-cooper text-white hover:border-[var(--color-razzmatazz-800)] hover:bg-[var(--color-razzmatazz-800)]"
                @click="goToCreate"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h14" />
                    <path d="M12 5v14" />
                </svg>
                <span>Créer une nouvelle collecte</span>
            </a>
        </div>

        <section class="mb-6 rounded-box border border-base-300 bg-white p-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <label class="input input-bordered group flex w-full max-w-xl items-center gap-3 bg-white">
                    <span class="material-symbols-outlined text-base-content/45 transition-colors group-focus-within:text-black" aria-hidden="true">search</span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="w-full font-cooper"
                        placeholder="Rechercher par entreprise ou email"
                    />
                </label>

                <label class="flex items-center gap-3 self-start lg:self-auto">
                    <div class="relative">
                        <select v-model="companyFilter" class="select select-bordered bg-none bg-white pr-10 font-cooper">
                            <option value="active-first">Campagnes actives d'abord</option>
                            <option value="active-only">Campagnes actives uniquement</option>
                            <option value="incoming-only">Campagnes à venir uniquement</option>
                            <option value="created-desc">Date de création · plus récentes</option>
                            <option value="created-asc">Date de création · plus anciennes</option>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--color-pampas-950)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                </label>
            </div>
        </section>

        <!-- List -->
        <div v-if="loadingCompanies" class="text-sm text-base-content/50">Chargement...</div>
        <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>
        <p v-else-if="companies.length === 0" class="text-sm text-base-content/50">Aucune campagne. Créez-en une.</p>
        <p v-else-if="displayedCompanies.length === 0" class="text-sm text-base-content/50">Aucun résultat pour ce filtre.</p>

        <div v-else class="space-y-4">
            <div
                v-for="company in paginatedCompanies"
                :key="company.id"
                class="rounded-box border border-base-300 bg-base-100 p-5"
            >
                <!-- Company row -->
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-base-200 text-sm font-semibold"
                            :style="{
                                backgroundColor: company.primaryColor || '#E5E7EB',
                                color: readableTextColor(company.primaryColor || '#E5E7EB'),
                            }"
                        >
                            <span>{{ companyBadgeLabel(company.name) }}</span>
                        </div>
                        <div>
                            <p class="font-semibold">{{ company.name }}</p>
                            <p class="mt-0.5 text-sm text-base-content/50">
                                <span>{{ company.slug }}</span>
                                · {{ company.email }}
                                <span v-if="company.employee_count"> · {{ company.employee_count }} employés</span>
                            </p>
                        </div>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <a
                            :href="companyActionPath(company)"
                            class="btn btn-ghost btn-sm border-transparent bg-transparent font-cooper font-normal text-base-content shadow-none transition-colors hover:border-transparent hover:bg-transparent hover:text-base-content"
                            @click.prevent="navigate(companyActionPath(company))"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14" />
                                <path d="M12 5v14" />
                            </svg>
                            <span>{{ companyActionLabel(company) }}</span>
                        </a>
                        <a
                            :href="companyEditPath(company)"
                            class="btn btn-ghost btn-sm border-transparent bg-transparent font-cooper font-normal text-base-content shadow-none transition-colors hover:border-transparent hover:bg-transparent hover:text-base-content"
                            @click.prevent="navigate(companyEditPath(company))"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                            <span>Modifier l'entreprise</span>
                        </a>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm border-red-600 font-cooper font-normal text-red-700 hover:border-red-700 hover:bg-red-700 hover:text-white"
                            :disabled="deletingCompanyId === company.id"
                            @click="deleteCompany(company)"
                        >
                            <span>{{ deletingCompanyId === company.id ? '...' : 'Supprimer' }}</span>
                        </button>
                    </div>
                </div>

                <!-- Collections -->
                <div class="mt-4">
                    <p class="mb-2 text-xs font-medium tracking-wider text-base-content/40 uppercase">
                        Collecte active
                    </p>
                    <p v-if="company.collections.length === 0" class="text-sm text-base-content/40">Aucune collecte.</p>
                    <p v-else-if="activeCollections(company).length === 0" class="text-sm text-base-content/40">
                        Aucune collecte active pour le moment.
                    </p>

                    <div
                        v-for="col in activeCollections(company)"
                        :key="col.id"
                        class="mt-1 rounded-lg border border-martinique-400 bg-martinique-100 px-4 py-3 text-sm"
                    >
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex min-w-0 flex-1 items-center gap-4">
                                <span class="shrink-0 text-sm font-medium text-martinique-950">
                                    {{ formatDate(col.start) }} → {{ formatDate(col.end) }}
                                </span>
                                <a :href="col.url" target="_blank" class="link link-primary min-w-0 truncate text-sm">
                                    <span>{{ col.url }}</span>
                                </a>
                                <button
                                    type="button"
                                    title="Copier l’URL complète"
                                    class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-martinique-900 transition-colors hover:bg-white hover:text-martinique-950"
                                    @click="copyCollectionUrl(col)"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect x="9" y="9" width="13" height="13" rx="2" />
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                    </svg>
                                </button>
                            </div>
                            <div class="flex shrink-0 items-center gap-3">
                                <a
                                    v-if="company.trophy || !company.is_public"
                                    :href="companyParticipationPath(company)"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-martinique-400 bg-white px-3 py-1.5 text-xs font-medium text-[var(--color-pampas-950)] transition-colors hover:border-martinique-500 hover:bg-martinique-100/80"
                                    @click.prevent="navigate(companyParticipationPath(company))"
                                >
                                    <svg v-if="company.trophy" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076A10.744 10.744 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/></svg>
                                    <span>{{ company.trophy ? 'Participation au Prix du Cœur' : 'Participation anonyme' }}</span>
                                </a>
                                <a
                                    :href="`/admin/companies/${company.id}/edit?collection=${col.id}`"
                                    title="Modifier la collecte"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-transparent bg-transparent px-3 py-1.5 text-xs font-medium text-martinique-950 transition-colors hover:border-martinique-400 hover:bg-white hover:text-martinique-950"
                                    @click.prevent="navigate(`/admin/companies/${company.id}/edit?collection=${col.id}`)"
                                >
                                    <span class="material-symbols-outlined" style="font-size: 18px;" aria-hidden="true">edit</span>
                                    <span>Modifier la collecte</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div v-if="upcomingCollections(company).length > 0" class="mt-4">
                        <p class="mb-2 text-xs font-medium tracking-wider text-base-content/40 uppercase">
                            Collecte à venir
                        </p>

                        <div class="space-y-2.5">
                            <div
                                v-for="col in upcomingCollections(company)"
                                :key="col.id"
                                class="rounded-lg border border-pampas-300 bg-pampas-100 px-4 py-3 text-sm"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex min-w-0 flex-1 items-center gap-4">
                                        <span class="shrink-0 text-sm font-medium text-pampas-950">
                                            {{ formatDate(col.start) }} → {{ formatDate(col.end) }}
                                        </span>
                                        <a
                                            v-if="col.is_public_link_enabled"
                                            :href="col.url"
                                            target="_blank"
                                            class="link link-primary min-w-0 truncate text-sm"
                                        >
                                            <span>{{ col.url }}</span>
                                        </a>
                                        <button
                                            v-if="col.is_public_link_enabled"
                                            type="button"
                                            title="Copier l’URL complète"
                                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-pampas-800 transition-colors hover:bg-white hover:text-pampas-950"
                                            @click="copyCollectionUrl(col)"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <rect x="9" y="9" width="13" height="13" rx="2" />
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" />
                                            </svg>
                                        </button>
                                        <template v-else>
                                            <span class="min-w-0 truncate text-sm text-pampas-900/70">
                                                {{ col.url }}
                                            </span>
                                            <button type="button" class="btn btn-ghost btn-xs cursor-not-allowed border-transparent font-cooper text-pampas-900/70 hover:border-transparent hover:bg-pampas-200 hover:text-pampas-950" @click="showDisabledLinkMessage">
                                                <span>Lien désactivé</span>
                                            </button>
                                        </template>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3">
                                        <a
                                            v-if="col.is_public_link_enabled && (company.trophy || !company.is_public)"
                                            :href="companyParticipationPath(company)"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-pampas-300 bg-white px-3 py-1.5 text-xs font-medium text-[var(--color-pampas-950)] transition-colors hover:border-pampas-400 hover:bg-pampas-200/80"
                                            @click.prevent="navigate(companyParticipationPath(company))"
                                        >
                                    <svg v-if="company.trophy" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.733 5.076A10.744 10.744 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/></svg>
                                    <span>{{ company.trophy ? 'Participation au Prix du Cœur' : 'Participation anonyme' }}</span>
                                </a>
                                <a
                                    :href="`/admin/companies/${company.id}/edit?collection=${col.id}`"
                                    title="Modifier la collecte"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-transparent bg-transparent px-3 py-1.5 text-xs font-medium text-pampas-950 transition-colors hover:border-pampas-300 hover:bg-white"
                                    @click.prevent="navigate(`/admin/companies/${company.id}/edit?collection=${col.id}`)"
                                >
                                    <span class="material-symbols-outlined" style="font-size: 18px;" aria-hidden="true">edit</span>
                                    <span>Modifier la collecte</span>
                                </a>
                            </div>
                        </div>
                    </div>
                        </div>
                    </div>

                    <details v-if="inactiveCollections(company).length > 0" class="collapse-arrow collapse mt-3 bg-base-200">
                        <summary class="collapse-title min-h-11 px-4 py-3 text-sm font-medium text-base-content/60">
                            <span>Historique ({{ inactiveCollections(company).length }})</span>
                        </summary>
                        <div class="collapse-content px-4 pb-4">
                            <div
                                v-for="col in inactiveCollections(company)"
                                :key="col.id"
                                class="flex items-center justify-between gap-3 border-t border-base-300 py-3 first:border-t-0"
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-4">
                                    <span class="shrink-0 text-sm text-base-content/55">
                                        {{ formatDate(col.start) }} → {{ formatDate(col.end) }}
                                    </span>
                                    <span class="min-w-0 truncate text-xs text-base-content/35">{{ col.url }}</span>
                                    <button type="button" class="btn btn-ghost btn-xs cursor-not-allowed font-cooper opacity-50" @click="showDisabledLinkMessage">
                                        <span>Lien désactivé</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </div>

            <nav v-if="displayedCompanies.length > pageSize" class="flex justify-center pt-2" aria-label="Pagination des entreprises">
                <div class="flex flex-wrap items-center justify-center gap-2">
                    <button
                        v-for="page in visiblePages"
                        :key="page"
                        type="button"
                        class="inline-flex h-10 min-w-10 items-center justify-center rounded-lg border-2 px-3 text-sm font-medium transition-colors"
                        :class="page === currentPage
                            ? 'border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] text-white'
                            : 'border-base-300 bg-white text-base-content hover:border-[var(--color-razzmatazz-700)] hover:text-[var(--color-razzmatazz-700)]'"
                        @click="currentPage = page"
                    >
                        {{ page }}
                    </button>
                </div>
            </nav>
        </div>
    </AdminLayout>
</template>
