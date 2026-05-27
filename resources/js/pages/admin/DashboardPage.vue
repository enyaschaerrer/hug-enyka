<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';

type CollectionRow = {
    id: number;
    start: string;
    end: string;
    access_token: string;
    linkOneDoc: string;
    url: string;
};

type CompanyRow = {
    id: number;
    name: string;
    slug: string;
    email: string;
    employee_count: number | null;
    collections: CollectionRow[];
};

type AppState = {
    auth: { user: { id: number; name: string; email: string } | null };
    csrfToken: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

const companies = ref<CompanyRow[]>([]);
const loadingCompanies = ref(true);
const loadError = ref<string | null>(null);

const openCollectionForm = ref<number | null>(null);
const collectionForm = ref({ start: '', end: '', linkOneDoc: '' });
const collectionErrors = ref<Record<string, string[]>>({});
const submittingCollection = ref(false);

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
            loadError.value = 'Erreur lors du chargement des entreprises.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loadingCompanies.value = false;
    }
}

function goToCreate(event: Event) {
    event.preventDefault();
    flashMessage.value = null;
    navigate('/admin/companies/create');
}

async function logout() {
    try {
        const res = await fetch('/admin/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = res.ok ? await res.json() : null;
        window.location.href = data?.redirect ?? '/admin/login';
    } catch {
        window.location.href = '/admin/login';
    }
}

function toggleCollectionForm(companyId: number) {
    if (openCollectionForm.value === companyId) {
        openCollectionForm.value = null;
        return;
    }
    openCollectionForm.value = companyId;
    collectionForm.value = { start: '', end: '', linkOneDoc: '' };
    collectionErrors.value = {};
}

async function createCollection(company: CompanyRow) {
    submittingCollection.value = true;
    collectionErrors.value = {};
    try {
        const res = await fetch(`/admin/companies/${company.id}/collections`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(collectionForm.value),
        });
        if (res.ok) {
            const data = await res.json();
            company.collections.push(data.collection);
            openCollectionForm.value = null;
        } else if (res.status === 422) {
            const data = await res.json();
            collectionErrors.value = data.errors ?? {};
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            collectionErrors.value = { start: ['Erreur serveur. Réessaye.'] };
        }
    } catch {
        collectionErrors.value = { start: ['Erreur réseau. Réessaye.'] };
    } finally {
        submittingCollection.value = false;
    }
}

onMounted(fetchCompanies);
</script>

<template>
    <main class="min-h-screen space-y-4 bg-base-200 px-4 py-8 text-base-content">
        <div v-if="flashMessage" class="alert alert-success">
            <span>{{ flashMessage }}</span>
        </div>

        <!-- Header -->
        <div class="flex items-center justify-between gap-4 rounded bg-base-100 p-6 shadow">
            <div>
                <p class="text-sm text-base-content/70">Admin dashboard</p>
                <h1 class="text-2xl font-semibold">{{ appState?.auth.user?.name ?? 'Admin' }}</h1>
                <p class="text-sm text-base-content/70">{{ appState?.auth.user?.email }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="/admin/companies/create" class="btn btn-primary btn-sm" @click="goToCreate">
                    Créer une entreprise
                </a>
                <button type="button" class="btn btn-outline btn-sm" @click="logout">Logout</button>
            </div>
        </div>

        <!-- Companies list -->
        <div class="rounded bg-base-100 p-6 shadow">
            <h2 class="mb-4 text-xl font-semibold">Entreprises</h2>

            <div v-if="loadingCompanies" class="text-sm text-base-content/60">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>
            <p v-else-if="companies.length === 0" class="text-sm text-base-content/60">Aucune entreprise. Créez-en une.</p>

            <div v-else class="space-y-4">
                <div v-for="company in companies" :key="company.id" class="rounded border border-base-300 p-4">
                    <!-- Company header row -->
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold">{{ company.name }}</p>
                            <p class="text-sm text-base-content/60">
                                slug: <span class="font-mono">{{ company.slug }}</span>
                                · {{ company.email }}
                                <span v-if="company.employee_count"> · {{ company.employee_count }} employés</span>
                            </p>
                        </div>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm shrink-0"
                            @click="toggleCollectionForm(company.id)"
                        >
                            {{ openCollectionForm === company.id ? 'Annuler' : '+ Collecte' }}
                        </button>
                    </div>

                    <!-- Collections list -->
                    <div class="mt-3">
                        <p class="mb-1 text-xs font-medium uppercase text-base-content/50">
                            Collectes ({{ company.collections.length }})
                        </p>
                        <p v-if="company.collections.length === 0" class="text-sm text-base-content/40">Aucune collecte.</p>
                        <div
                            v-for="col in company.collections"
                            :key="col.id"
                            class="mt-1 flex flex-wrap items-center gap-3 rounded bg-base-200 px-3 py-2 text-sm"
                        >
                            <span class="text-base-content/70">{{ formatDate(col.start) }} → {{ formatDate(col.end) }}</span>
                            <a
                                :href="col.url"
                                target="_blank"
                                class="link link-primary font-mono text-xs"
                            >{{ col.url }}</a>
                        </div>
                    </div>

                    <!-- Inline collection creation form -->
                    <form
                        v-if="openCollectionForm === company.id"
                        class="mt-4 space-y-3 border-t border-base-300 pt-4"
                        @submit.prevent="createCollection(company)"
                    >
                        <p class="text-sm font-semibold">Nouvelle collecte</p>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <label class="flex flex-col gap-1">
                                <span class="text-xs">Début *</span>
                                <input
                                    v-model="collectionForm.start"
                                    type="datetime-local"
                                    class="input input-bordered input-sm"
                                    required
                                />
                                <p v-if="collectionErrors.start" class="text-xs text-error">{{ collectionErrors.start[0] }}</p>
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-xs">Fin *</span>
                                <input
                                    v-model="collectionForm.end"
                                    type="datetime-local"
                                    class="input input-bordered input-sm"
                                    required
                                />
                                <p v-if="collectionErrors.end" class="text-xs text-error">{{ collectionErrors.end[0] }}</p>
                            </label>
                            <label class="flex flex-col gap-1">
                                <span class="text-xs">Lien OneDoc *</span>
                                <input
                                    v-model="collectionForm.linkOneDoc"
                                    type="text"
                                    class="input input-bordered input-sm"
                                    required
                                    placeholder="https://..."
                                />
                                <p v-if="collectionErrors.linkOneDoc" class="text-xs text-error">{{ collectionErrors.linkOneDoc[0] }}</p>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm" :disabled="submittingCollection">
                            {{ submittingCollection ? '...' : 'Créer la collecte' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</template>
