<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type Registration = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    npa: string | null;
    localite: string | null;
    message: string | null;
    trophy: boolean;
    treated: boolean;
    created_at: string;
};

const registrations = ref<Registration[]>([]);
const loading = ref(true);
const loadError = ref<string | null>(null);
const lastCount = ref(0);
const hasNew = ref(false);
const activeTab = ref<'pending' | 'treated'>('pending');

const filteredRegistrations = computed(() =>
    registrations.value.filter(reg =>
        activeTab.value === 'pending' ? !reg.treated : reg.treated
    )
);

let pollTimer: number | undefined;

const appState = (window as unknown as { __APP__?: { csrfToken: string } }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

async function fetchRegistrations() {
    try {
        const res = await fetch('/admin/api/registrations', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
            const data = await res.json();
            if (lastCount.value > 0 && data.length > lastCount.value) hasNew.value = true;
            lastCount.value = data.length;
            registrations.value = data;
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Erreur lors du chargement.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loading.value = false;
    }
}

async function deleteRegistration(reg: Registration) {
    if (!window.confirm(`Supprimer l'inscription de "${reg.name}" ?`)) return;

    const res = await fetch(`/admin/forms/${reg.id}`, {
        method: 'DELETE',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (res.ok) {
        registrations.value = registrations.value.filter((r) => r.id !== reg.id);
    }
}

async function toggleTreated(reg: Registration) {
    const res = await fetch(`/admin/forms/${reg.id}/treated`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (res.ok) {
        reg.treated = !reg.treated;
    }
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function formatFullAddress(address?: string | null, npa?: string | null, localite?: string | null): string {
    return [address, [npa, localite].filter(Boolean).join(' ')].filter(Boolean).join(', ') || '—';
}

type FormDetail = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    npa: string | null;
    localite: string | null;
    message: string | null;
    trophy: boolean;
    treated: boolean;
    created_at: string;
};

type MatchingCompany = {
    id: number;
    name: string;
    email: string;
};

const selectedForm = ref<FormDetail | null>(null);
const matchingCompanies = ref<MatchingCompany[]>([]);
const loadingDetail = ref(false);

async function openDetail(id: number) {
    loadingDetail.value = true;
    selectedForm.value = null;
    matchingCompanies.value = [];

    const res = await fetch(`/admin/forms/${id}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (res.ok) {
        const data = await res.json();
        selectedForm.value = data.form;
        matchingCompanies.value = data.matchingCompanies;
    }

    loadingDetail.value = false;
}

onMounted(() => {
    fetchRegistrations();
    pollTimer = window.setInterval(fetchRegistrations, 5000);
});

onUnmounted(() => {
    if (pollTimer) window.clearInterval(pollTimer);
});

</script>

<template>
    <AdminLayout>
        <section class="min-h-full rounded-sm bg-[var(--color-pampas-50)] p-1 pr-4 text-[#1f1f22]">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-semibold">Inscriptions</h1>
                    <p class="mt-1 text-lg text-base-content/60">
                        Nouvelles demandes reçues via le formulaire
                    </p>
                </div>
                <div
                    v-if="hasNew"
                    class="flex cursor-pointer items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
                    @click="hasNew = false"
                >
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <span>Nouvelles inscriptions</span>
                </div>
            </div>

            <div v-if="loading" class="text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>

            <template v-else>
                <!-- Onglets -->
                <div class="mb-4 flex gap-2 border-b border-base-300">
                    <button
                        class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                        :class="activeTab === 'pending'
                            ? 'border-b-2 border-[var(--color-pampas-950)] text-[var(--color-pampas-950)]'
                            : 'text-base-content/50 hover:text-base-content'"
                        @click="activeTab = 'pending'"
                    >
                        <span>En attente</span>
                        <span class="ml-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-xs text-[var(--color-pampas-950)]">
                            <span>{{ registrations.filter(r => !r.treated).length }}</span>
                        </span>
                    </button>
                    <button
                        class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                        :class="activeTab === 'treated'
                            ? 'border-b-2 border-[var(--color-pampas-950)] text-[var(--color-pampas-950)]'
                            : 'text-base-content/50 hover:text-base-content'"
                        @click="activeTab = 'treated'"
                    >
                        <span>Historique</span>
                        <span class="ml-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-stone-100 text-xs text-stone-500">
                            <span>{{ registrations.filter(r => r.treated).length }}</span>
                        </span>
                    </button>
                </div>

                <!-- Message vide -->
                <div v-if="filteredRegistrations.length === 0" class="text-sm text-base-content/50">
                    Aucune inscription {{ activeTab === 'pending' ? 'en attente' : 'dans l\'historique' }} pour le moment.
                </div>

                <div v-else class="border border-base-300 bg-white">
                    <!-- Header -->
                    <div class="flex border-b border-base-300 bg-[var(--color-razzmatazz-100)] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-[var(--color-pampas-950)]">
                        <div class="w-1/5"><span>Entreprise</span></div>
                        <div class="w-1/5"><span>Email</span></div>
                        <div class="w-1/5"><span>Date</span></div>
                        <div class="w-1/5 text-center"><span>Trophée</span></div>
                        <div class="w-1/5 text-center"><span>Actions</span></div>
                    </div>

                    <!-- Rows -->
                    <div
                        v-for="reg in filteredRegistrations"
                        :key="reg.id"
                        class="flex items-center border-b border-base-200 px-5 py-3 hover:bg-rose-50/40"
                        :class="[!reg.treated ? 'cursor-pointer' : '']"
                        @click="!reg.treated && openDetail(reg.id)"
                    >
                        <div class="flex w-4/5 items-center" :class="reg.treated ? 'opacity-50' : ''">
                            <div class="w-1/4 truncate font-medium"><span>{{ reg.name }}</span></div>
                            <div class="w-1/4 truncate text-base-content/70"><span>{{ reg.email }}</span></div>
                            <div class="w-1/4 truncate text-sm text-base-content/50"><span>{{ formatDate(reg.created_at) }}</span></div>
                            <div class="w-1/4 text-center">
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="reg.trophy ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400'"
                                >
                                    <span>{{ reg.trophy ? 'Oui' : 'Non' }}</span>
                                </span>
                            </div>
                        </div>
                        <div class="w-1/5 text-center" @click.stop>
                            <div class="inline-flex gap-2">
                                <button
                                    v-if="!reg.treated"
                                    class="cursor-pointer rounded border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-600 transition hover:bg-stone-100 font-cooper"
                                    @click="openDetail(reg.id)"
                                >
                                    <span>Voir</span>
                                </button>
                                <button
                                    class="cursor-pointer rounded border px-3 py-1 text-xs font-medium transition font-cooper"
                                    :class="reg.treated
                                        ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    @click="toggleTreated(reg)"
                                >
                                    <span>{{ reg.treated ? 'Réouvrir' : 'Archiver' }}</span>
                                </button>
                                <button
                                    v-if="reg.treated"
                                    class="cursor-pointer rounded border border-red-200 bg-red-50 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100 font-cooper"
                                    @click="deleteRegistration(reg)"
                                >
                                    <span>Supprimer</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </section>

        <!-- Modale détail -->
        <div
            v-if="selectedForm !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @click.self="selectedForm = null"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white p-8 shadow-xl">
                <div v-if="loadingDetail" class="text-sm text-base-content/50">Chargement...</div>

                <template v-else>
                    <h2 class="mb-6 text-lg font-semibold text-[var(--color-pampas-950)]">
                        Détail de la demande
                    </h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Entreprise</span></dt>
                            <dd class="text-stone-900"><span>{{ selectedForm.name }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Email</span></dt>
                            <dd class="text-stone-900"><span>{{ selectedForm.email }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Téléphone</span></dt>
                            <dd class="text-stone-900"><span>{{ selectedForm.phone ?? '—' }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Adresse</span></dt>
                            <dd class="text-stone-900"><span>{{ formatFullAddress(selectedForm.address, selectedForm.npa, selectedForm.localite) }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Trophée</span></dt>
                            <dd>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="selectedForm.trophy ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400'"
                                >
                                    <span>{{ selectedForm.trophy ? 'Oui' : 'Non' }}</span>
                                </span>
                            </dd>
                        </div>
                        <div v-if="selectedForm.message" class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Message</span></dt>
                            <dd class="whitespace-pre-wrap text-stone-900"><span>{{ selectedForm.message }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span>Date</span></dt>
                            <dd class="text-stone-900"><span>{{ formatDate(selectedForm.created_at) }}</span></dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                            Entreprises similaires dans la base
                        </p>
                        <div v-if="matchingCompanies.length === 0" class="text-sm text-stone-400">
                            Aucune entreprise similaire trouvée.
                        </div>
                        <ul v-else class="space-y-2">
                            <li
                                v-for="company in matchingCompanies"
                                :key="company.id"
                                class="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-2 text-sm"
                            >
                                <span class="font-medium text-amber-800">{{ company.name }}</span>
                                <span class="text-amber-600">{{ company.email }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            class="rounded-xl bg-[var(--color-pampas-950)] px-5 py-2 text-sm font-medium text-white hover:opacity-90 font-cooper"
                            @click="selectedForm = null"
                        >
                            <span>Fermer</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
