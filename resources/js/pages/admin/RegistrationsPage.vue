<script setup lang="ts">
import { onMounted, onUnmounted, ref, computed } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type Registration = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
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

type FormDetail = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
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
        <section class="min-h-full rounded-sm bg-[#FAF8F2] p-1 pr-4 text-[#1f1f22]">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="cooper-text-baseline text-3xl font-semibold">Inscriptions</h1>
                    <p class="cooper-text-baseline mt-1 text-lg text-base-content/60">
                        Nouvelles demandes reçues via le formulaire
                    </p>
                </div>
                <div
                    v-if="hasNew"
                    class="flex cursor-pointer items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
                    @click="hasNew = false"
                >
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <span class="cooper-baseline">Nouvelles inscriptions</span>
                </div>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>

            <template v-else>
                <!-- Onglets -->
                <div class="mb-4 flex gap-2 border-b border-base-300">
                    <button
                        class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                        :class="activeTab === 'pending'
                            ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                            : 'text-base-content/50 hover:text-base-content'"
                        @click="activeTab = 'pending'"
                    >
                        <span class="cooper-baseline">En attente</span>
                        <span class="ml-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-rose-100 text-xs text-[#5a002a]">
                            <span class="cooper-baseline">{{ registrations.filter(r => !r.treated).length }}</span>
                        </span>
                    </button>
                    <button
                        class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                        :class="activeTab === 'treated'
                            ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                            : 'text-base-content/50 hover:text-base-content'"
                        @click="activeTab = 'treated'"
                    >
                        <span class="cooper-baseline">Traitées</span>
                        <span class="ml-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-stone-100 text-xs text-stone-500">
                            <span class="cooper-baseline">{{ registrations.filter(r => r.treated).length }}</span>
                        </span>
                    </button>
                </div>

                <!-- Message vide -->
                <div v-if="filteredRegistrations.length === 0" class="cooper-text-baseline text-sm text-base-content/50">
                    Aucune inscription {{ activeTab === 'pending' ? 'en attente' : 'traitée' }} pour le moment.
                </div>

                <div v-else class="border border-base-300 bg-white">
                    <!-- Header -->
                    <div class="flex border-b border-base-300 bg-[#f8e7ee] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-[#5a002a]">
                        <div class="w-1/5"><span class="cooper-baseline">Entreprise</span></div>
                        <div class="w-1/5"><span class="cooper-baseline">Email</span></div>
                        <div class="w-1/5"><span class="cooper-baseline">Date</span></div>
                        <div class="w-1/5 text-center"><span class="cooper-baseline">Trophée</span></div>
                        <div class="w-1/5 text-center"><span class="cooper-baseline">Actions</span></div>
                    </div>

                    <!-- Rows -->
                    <div
                        v-for="reg in filteredRegistrations"
                        :key="reg.id"
                        class="flex items-center border-b border-base-200 px-5 py-3 hover:bg-rose-50/40"
                        :class="[reg.treated ? 'opacity-50' : 'cursor-pointer']"
                        @click="!reg.treated && openDetail(reg.id)"
                    >
                        <div class="w-1/5 truncate font-medium"><span class="cooper-baseline">{{ reg.name }}</span></div>
                        <div class="w-1/5 truncate text-base-content/70"><span class="cooper-baseline">{{ reg.email }}</span></div>
                        <div class="w-1/5 truncate text-sm text-base-content/50"><span class="cooper-baseline">{{ formatDate(reg.created_at) }}</span></div>
                        <div class="w-1/5 text-center">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="reg.trophy ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400'"
                            >
                                <span class="cooper-baseline">{{ reg.trophy ? 'Oui' : 'Non' }}</span>
                            </span>
                        </div>
                        <div class="w-1/5 text-center" @click.stop>
                            <div class="inline-flex gap-2">
                                <button
                                    v-if="!reg.treated"
                                    class="cursor-pointer rounded border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-600 transition hover:bg-stone-100 font-cooper"
                                    @click="openDetail(reg.id)"
                                >
                                    <span class="cooper-baseline">Voir</span>
                                </button>
                                <button
                                    class="cursor-pointer rounded border px-3 py-1 text-xs font-medium transition font-cooper"
                                    :class="reg.treated
                                        ? 'border-amber-300 bg-amber-50 text-amber-700 hover:bg-amber-100'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    @click="toggleTreated(reg)"
                                >
                                    <span class="cooper-baseline">{{ reg.treated ? 'Réouvrir' : 'Traité' }}</span>
                                </button>
                                <button
                                    v-if="reg.treated"
                                    class="cursor-pointer rounded border border-red-200 bg-red-50 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100 font-cooper"
                                    @click="deleteRegistration(reg)"
                                >
                                    <span class="cooper-baseline">Supprimer</span>
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
                <div v-if="loadingDetail" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>

                <template v-else>
                    <h2 class="cooper-text-baseline mb-6 text-lg font-semibold text-[#5a002a]">
                        Détail de la demande
                    </h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Entreprise</span></dt>
                            <dd class="text-stone-900"><span class="cooper-baseline">{{ selectedForm.name }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Email</span></dt>
                            <dd class="text-stone-900"><span class="cooper-baseline">{{ selectedForm.email }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Téléphone</span></dt>
                            <dd class="text-stone-900"><span class="cooper-baseline">{{ selectedForm.phone ?? '—' }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Adresse</span></dt>
                            <dd class="text-stone-900"><span class="cooper-baseline">{{ selectedForm.address ?? '—' }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Trophée</span></dt>
                            <dd>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="selectedForm.trophy ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400'"
                                >
                                    <span class="cooper-baseline">{{ selectedForm.trophy ? 'Oui' : 'Non' }}</span>
                                </span>
                            </dd>
                        </div>
                        <div v-if="selectedForm.message" class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Message</span></dt>
                            <dd class="whitespace-pre-wrap text-stone-900"><span class="cooper-baseline">{{ selectedForm.message }}</span></dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500"><span class="cooper-baseline">Date</span></dt>
                            <dd class="text-stone-900"><span class="cooper-baseline">{{ formatDate(selectedForm.created_at) }}</span></dd>
                        </div>
                    </dl>

                    <div class="mt-6">
                        <p class="cooper-text-baseline mb-2 text-xs font-semibold uppercase tracking-wide text-stone-500">
                            Entreprises similaires dans la base
                        </p>
                        <div v-if="matchingCompanies.length === 0" class="cooper-text-baseline text-sm text-stone-400">
                            Aucune entreprise similaire trouvée.
                        </div>
                        <ul v-else class="space-y-2">
                            <li
                                v-for="company in matchingCompanies"
                                :key="company.id"
                                class="flex items-center justify-between rounded-lg bg-amber-50 px-4 py-2 text-sm"
                            >
                                <span class="cooper-baseline font-medium text-amber-800">{{ company.name }}</span>
                                <span class="cooper-baseline text-amber-600">{{ company.email }}</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button
                            class="rounded-xl bg-[#5a002a] px-5 py-2 text-sm font-medium text-white hover:bg-[#7a0038] font-cooper"
                            @click="selectedForm = null"
                        >
                            <span class="cooper-baseline">Fermer</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
