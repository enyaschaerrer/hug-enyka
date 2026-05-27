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
};

type CompanyRow = {
    id: number;
    name: string;
    slug: string;
    email: string;
    employee_count: number | null;
    collections: CollectionRow[];
};

type AppState = { csrfToken: string };

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate } = useAdminRouter();

const companies = ref<CompanyRow[]>([]);
const loadingCompanies = ref(true);
const loadError = ref<string | null>(null);

const openCollectionForm = ref<number | null>(null);
const collectionForm = ref({ start: '', end: '', linkOneDoc: '' });
const collectionErrors = ref<Record<string, string[]>>({});
const submittingCollection = ref(false);

const editingCollectionId = ref<number | null>(null);
const editForm = ref({ start: '', end: '', linkOneDoc: '' });
const editErrors = ref<Record<string, string[]>>({});
const submittingEdit = ref(false);

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function toDatetimeLocal(iso: string): string {
    return iso.slice(0, 16);
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
    navigate('/admin/companies/create');
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

function openEditForm(col: CollectionRow) {
    editingCollectionId.value = col.id;
    editForm.value = {
        start: toDatetimeLocal(col.start),
        end: toDatetimeLocal(col.end),
        linkOneDoc: col.linkOneDoc,
    };
    editErrors.value = {};
}

async function saveEdit(company: CompanyRow, col: CollectionRow) {
    submittingEdit.value = true;
    editErrors.value = {};
    try {
        const res = await fetch(`/admin/companies/${company.id}/collections/${col.id}`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(editForm.value),
        });
        if (res.ok) {
            const data = await res.json();
            const idx = company.collections.findIndex(c => c.id === col.id);
            if (idx !== -1) company.collections[idx] = data.collection;
            editingCollectionId.value = null;
        } else if (res.status === 422) {
            const data = await res.json();
            editErrors.value = data.errors ?? {};
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            editErrors.value = { start: ['Erreur serveur. Réessaye.'] };
        }
    } catch {
        editErrors.value = { start: ['Erreur réseau. Réessaye.'] };
    } finally {
        submittingEdit.value = false;
    }
}

onMounted(fetchCompanies);
</script>

<template>
    <AdminLayout>
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Campagnes</h1>
            <a href="/admin/companies/create" class="btn btn-primary btn-sm" @click="goToCreate">
                Créer une nouvelle campagne
            </a>
        </div>

        <!-- List -->
        <div v-if="loadingCompanies" class="text-sm text-base-content/50">Chargement...</div>
        <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>
        <p v-else-if="companies.length === 0" class="text-sm text-base-content/50">Aucune campagne. Créez-en une.</p>

        <div v-else class="space-y-4">
            <div
                v-for="company in companies"
                :key="company.id"
                class="rounded-box border border-base-300 bg-base-100 p-5"
            >
                <!-- Company row -->
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="font-semibold">{{ company.name }}</p>
                        <p class="mt-0.5 text-sm text-base-content/50">
                            <span class="font-mono">{{ company.slug }}</span>
                            · {{ company.email }}
                            <span v-if="company.employee_count"> · {{ company.employee_count }} employés</span>
                        </p>
                    </div>
                    <div class="flex shrink-0 gap-2">
                        <a
                            :href="`/admin/companies/${company.id}/edit`"
                            class="btn btn-ghost btn-sm"
                            @click.prevent="navigate(`/admin/companies/${company.id}/edit`)"
                        >
                            Modifier
                        </a>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm"
                            @click="toggleCollectionForm(company.id)"
                        >
                            {{ openCollectionForm === company.id ? 'Annuler' : '+ Collecte' }}
                        </button>
                    </div>
                </div>

                <!-- Collections -->
                <div class="mt-4">
                    <p class="mb-2 text-xs font-medium tracking-wider text-base-content/40 uppercase">
                        Collectes ({{ company.collections.length }})
                    </p>
                    <p v-if="company.collections.length === 0" class="text-sm text-base-content/40">Aucune collecte.</p>

                    <div
                        v-for="col in company.collections"
                        :key="col.id"
                        class="mt-1 rounded-lg bg-base-200 px-4 py-3 text-sm"
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-base-content/60">{{ formatDate(col.start) }} → {{ formatDate(col.end) }}</span>
                            <a :href="col.url" target="_blank" class="link link-primary font-mono text-xs">{{ col.url }}</a>
                            <button
                                type="button"
                                class="btn btn-ghost btn-xs ml-auto"
                                @click="editingCollectionId === col.id ? editingCollectionId = null : openEditForm(col)"
                            >
                                {{ editingCollectionId === col.id ? 'Annuler' : 'Modifier' }}
                            </button>
                        </div>

                        <form
                            v-if="editingCollectionId === col.id"
                            class="mt-3 space-y-3 border-t border-base-300 pt-3"
                            @submit.prevent="saveEdit(company, col)"
                        >
                            <div class="grid gap-3 sm:grid-cols-3">
                                <label class="flex flex-col gap-1">
                                    <span class="text-xs">Début *</span>
                                    <input v-model="editForm.start" type="datetime-local" class="input input-bordered input-sm" required />
                                    <p v-if="editErrors.start" class="text-xs text-error">{{ editErrors.start[0] }}</p>
                                </label>
                                <label class="flex flex-col gap-1">
                                    <span class="text-xs">Fin *</span>
                                    <input v-model="editForm.end" type="datetime-local" class="input input-bordered input-sm" required />
                                    <p v-if="editErrors.end" class="text-xs text-error">{{ editErrors.end[0] }}</p>
                                </label>
                                <label class="flex flex-col gap-1">
                                    <span class="text-xs">Lien OneDoc *</span>
                                    <input v-model="editForm.linkOneDoc" type="text" class="input input-bordered input-sm" required />
                                    <p v-if="editErrors.linkOneDoc" class="text-xs text-error">{{ editErrors.linkOneDoc[0] }}</p>
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm" :disabled="submittingEdit">
                                {{ submittingEdit ? '...' : 'Enregistrer' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- New collection form -->
                <form
                    v-if="openCollectionForm === company.id"
                    class="mt-4 space-y-3 border-t border-base-300 pt-4"
                    @submit.prevent="createCollection(company)"
                >
                    <p class="text-sm font-medium">Nouvelle collecte</p>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <label class="flex flex-col gap-1">
                            <span class="text-xs">Début *</span>
                            <input v-model="collectionForm.start" type="datetime-local" class="input input-bordered input-sm" required />
                            <p v-if="collectionErrors.start" class="text-xs text-error">{{ collectionErrors.start[0] }}</p>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs">Fin *</span>
                            <input v-model="collectionForm.end" type="datetime-local" class="input input-bordered input-sm" required />
                            <p v-if="collectionErrors.end" class="text-xs text-error">{{ collectionErrors.end[0] }}</p>
                        </label>
                        <label class="flex flex-col gap-1">
                            <span class="text-xs">Lien OneDoc *</span>
                            <input v-model="collectionForm.linkOneDoc" type="text" class="input input-bordered input-sm" required placeholder="https://..." />
                            <p v-if="collectionErrors.linkOneDoc" class="text-xs text-error">{{ collectionErrors.linkOneDoc[0] }}</p>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" :disabled="submittingCollection">
                        {{ submittingCollection ? '...' : 'Créer la collecte' }}
                    </button>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
