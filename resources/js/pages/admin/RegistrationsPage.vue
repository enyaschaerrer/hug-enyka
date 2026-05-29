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
                    Nouvelles inscriptions
                </div>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>

            <template v-else>
                <!-- Onglets -->
                <div class="mb-4 flex gap-2 border-b border-base-300">
                    <button
                        class="px-5 py-2.5 text-sm font-medium transition"
                        :class="activeTab === 'pending'
                            ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                            : 'text-base-content/50 hover:text-base-content'"
                        @click="activeTab = 'pending'"
                    >
                        En attente
                        <span class="ml-1.5 rounded-full bg-rose-100 px-2 py-0.5 text-xs text-[#5a002a]">
                            {{ registrations.filter(r => !r.treated).length }}
                        </span>
                    </button>
                    <button
                        class="px-5 py-2.5 text-sm font-medium transition"
                        :class="activeTab === 'treated'
                            ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                            : 'text-base-content/50 hover:text-base-content'"
                        @click="activeTab = 'treated'"
                    >
                        Traitées
                        <span class="ml-1.5 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-500">
                            {{ registrations.filter(r => r.treated).length }}
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
                        <div class="w-1/5">Entreprise</div>
                        <div class="w-1/5">Email</div>
                        <div class="w-1/5">Date</div>
                        <div class="w-1/5 text-center">Trophée</div>
                        <div class="w-1/5 text-center">Actions</div>
                    </div>

                    <!-- Rows -->
                    <div
                        v-for="reg in filteredRegistrations"
                        :key="reg.id"
                        class="flex items-center border-b border-base-200 px-5 py-3 hover:bg-rose-50/40"
                        :class="reg.treated ? 'opacity-50' : ''"
                    >
                        <div class="w-1/5 truncate font-medium">{{ reg.name }}</div>
                        <div class="w-1/5 truncate text-base-content/70">{{ reg.email }}</div>
                        <div class="w-1/5 truncate text-sm text-base-content/50">{{ formatDate(reg.created_at) }}</div>
                        <div class="w-1/5 text-center">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-medium"
                                :class="reg.trophy ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400'"
                            >
                                {{ reg.trophy ? 'Oui' : 'Non' }}
                            </span>
                        </div>
                        <div class="w-1/5 text-center">
                            <div class="inline-flex gap-2">
                                <button
                                    class="rounded border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-600 transition hover:bg-stone-100"
                                    @click="openDetail(reg.id)"
                                >
                                    Voir
                                </button>
                                <button
                                    class="rounded border px-3 py-1 text-xs font-medium transition"
                                    :class="reg.treated
                                        ? 'border-stone-200 bg-stone-50 text-stone-400 hover:bg-stone-100'
                                        : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                                    @click="toggleTreated(reg)"
                                >
                                    {{ reg.treated ? 'Réouvrir' : 'Traité' }}
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
                    <h2 class="cooper-text-baseline mb-6 text-lg font-semibold text-[#5a002a]">
                        Détail de la demande
                    </h2>

                    <dl class="space-y-3 text-sm">
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Entreprise</dt>
                            <dd class="text-stone-900">{{ selectedForm.name }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Email</dt>
                            <dd class="text-stone-900">{{ selectedForm.email }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Téléphone</dt>
                            <dd class="text-stone-900">{{ selectedForm.phone ?? '—' }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Adresse</dt>
                            <dd class="text-stone-900">{{ selectedForm.address ?? '—' }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Trophée</dt>
                            <dd>
                                <span
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                    :class="selectedForm.trophy ? 'bg-emerald-50 text-emerald-700' : 'bg-stone-100 text-stone-400'"
                                >
                                    {{ selectedForm.trophy ? 'Oui' : 'Non' }}
                                </span>
                            </dd>
                        </div>
                        <div v-if="selectedForm.message" class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Message</dt>
                            <dd class="whitespace-pre-wrap text-stone-900">{{ selectedForm.message }}</dd>
                        </div>
                        <div class="flex gap-4">
                            <dt class="w-32 shrink-0 font-medium text-stone-500">Date</dt>
                            <dd class="text-stone-900">{{ formatDate(selectedForm.created_at) }}</dd>
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
                            class="rounded-xl bg-[#5a002a] px-5 py-2 text-sm font-medium text-white hover:bg-[#7a0038]"
                            @click="selectedForm = null"
                        >
                            Fermer
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </AdminLayout>
</template>