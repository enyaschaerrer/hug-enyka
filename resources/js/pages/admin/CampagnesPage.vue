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
const { navigate, flashMessage } = useAdminRouter();

const companies = ref<CompanyRow[]>([]);
const loadingCompanies = ref(true);
const loadError = ref<string | null>(null);
const deletingCompanyId = ref<number | null>(null);

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

        <div class="mb-6 flex items-center justify-between">
            <h1 class="cooper-text-baseline text-2xl font-semibold">Campagnes</h1>
            <a href="/admin/companies/create" class="btn btn-primary btn-sm" @click="goToCreate">
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
                            <span class="cooper-baseline">Modifier</span>
                        </a>
                        <button
                            type="button"
                            class="btn btn-error btn-outline btn-sm"
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
                        Collectes ({{ company.collections.length }})
                    </p>
                    <p v-if="company.collections.length === 0" class="cooper-text-baseline text-sm text-base-content/40">Aucune collecte.</p>

                    <div
                        v-for="col in company.collections"
                        :key="col.id"
                        class="mt-1 rounded-lg bg-base-200 px-4 py-3 text-sm"
                    >
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="cooper-baseline text-base-content/60">{{ formatDate(col.start) }} → {{ formatDate(col.end) }}</span>
                            <a :href="col.url" target="_blank" class="link link-primary font-mono text-xs">
                                <span class="cooper-baseline">{{ col.url }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
