<script setup lang="ts">
import { onMounted, ref } from 'vue';
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
};

type CompanyRow = {
    id: number;
    name: string;
    slug: string;
    email: string;
    employee_count: number | null;
    trophy: boolean;
    collections: CollectionRow[];
};

type AppState = { csrfToken: string };

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

const companies = ref<CompanyRow[]>([]);
const loadingCompanies = ref(true);
const loadError = ref<string | null>(null);
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
    return company.collections.filter((collection) => collection.is_active);
}

function inactiveCollections(company: CompanyRow): CollectionRow[] {
    return company.collections.filter((collection) => !collection.is_active);
}

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

        <div class="mb-6 flex items-center justify-between">
            <h1 class="cooper-text-baseline text-2xl font-semibold">Campagnes</h1>
            <a href="/admin/companies/create" class="btn btn-primary btn-sm font-cooper" @click="goToCreate">
                <span class="cooper-baseline">Créer une nouvelle campagne</span>
            </a>
        </div>

        <!-- List -->
        <div v-if="loadingCompanies" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
        <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>
        <p v-else-if="companies.length === 0" class="cooper-text-baseline text-sm text-base-content/50">Aucune campagne. Créez-en une.</p>

        <div v-else class="space-y-4">
            <div
                v-for="company in companies"
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
                            :href="`/admin/companies/${company.id}/edit`"
                            class="btn btn-ghost btn-sm font-cooper"
                            @click.prevent="navigate(`/admin/companies/${company.id}/edit`)"
                        >
                            <span class="cooper-baseline">Modifier</span>
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
                                <span v-if="company.trophy" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-white px-3 py-1.5 text-xs font-medium text-[#5A002A]">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                                    <span class="cooper-baseline">Participe au prix du cœur</span>
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
