<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { readableTextColor } from '../../utils/contrast';

type DonorCandidate = {
    id: number;
    name: string;
    email: string;
    created_at: string | null;
    primaryColor: string | null;
    employee_count: number | null;
    address: string | null;
    npa: string | null;
    localite: string | null;
    collections_count: number;
    donor_trophies_won: number;
};

type TrophyTab = 'donneur' | 'ambassadeur' | 'jury' | 'winners' | 'history';

const activeTab = ref<TrophyTab>('donneur');
const donorCandidates = ref<DonorCandidate[]>([]);
const loading = ref(true);
const loadError = ref<string | null>(null);
const currentEditionYear = new Date().getFullYear();

const donorLabel = computed(() => `Meilleur donneur (${donorCandidates.value.length})`);

function formatFullAddress(address?: string | null, npa?: string | null, localite?: string | null): string {
    return [address, [npa, localite].filter(Boolean).join(' ')].filter(Boolean).join(', ') || '—';
}

function formatTrophies(count: number): string {
    return `${count} trophée${count > 1 ? 's' : ''}`;
}

function formatCollections(count: number): string {
    return `${count} campagne${count > 1 ? 's' : ''}`;
}

function formatRegistrationDate(iso?: string | null): string {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleDateString('fr-CH', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
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

async function fetchDonorCandidates() {
    loading.value = true;
    loadError.value = null;

    try {
        const res = await fetch('/admin/api/trophee/donneur', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
            donorCandidates.value = await res.json();
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Erreur lors du chargement du trophée.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDonorCandidates);
</script>

<template>
    <AdminLayout>
        <section class="min-h-full rounded-sm bg-[#FAF8F2] p-1 pr-4 text-[#1f1f22]">
            <div class="mb-6">
                <h1 class="cooper-text-baseline text-3xl font-semibold">Trophée - Édition {{ currentEditionYear }}</h1>
                <p class="cooper-text-baseline mt-1 text-lg text-base-content/60">
                    Attribution des trophées aux entreprises candidates dans les différentes catégories.
                </p>
            </div>

            <div class="mb-4 flex flex-wrap gap-2 border-b border-base-300">
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'donneur'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'donneur'"
                >
                    <span class="cooper-baseline">{{ donorLabel }}</span>
                </button>
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'ambassadeur'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'ambassadeur'"
                >
                    <span class="cooper-baseline">Meilleur ambassadeur</span>
                </button>
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'jury'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'jury'"
                >
                    <span class="cooper-baseline">Coup de cœur du jury</span>
                </button>
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'winners'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'winners'"
                >
                    <span class="cooper-baseline">Liste des gagnants {{ currentEditionYear }}</span>
                </button>
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'history'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'history'"
                >
                    <span class="cooper-baseline">Historique des gagnants</span>
                </button>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>

            <template v-else>
                <div v-if="activeTab === 'donneur'">
                    <div v-if="donorCandidates.length === 0" class="cooper-text-baseline text-sm text-base-content/50">
                        Aucune entreprise éligible pour le moment.
                    </div>

                    <div v-else class="border border-base-300 bg-white">
                        <div class="flex border-b border-base-300 bg-[#f8e7ee] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-[#5a002a]">
                            <div class="w-[16%] pr-4"><span class="cooper-baseline">Entreprise</span></div>
                            <div class="w-[12%] pr-4"><span class="cooper-baseline">Inscription</span></div>
                            <div class="w-[24%] pr-4"><span class="cooper-baseline">Adresse</span></div>
                            <div class="w-[10%] pr-4"><span class="cooper-baseline">Employés</span></div>
                            <div class="w-[14%] pr-4"><span class="cooper-baseline">Campagnes en {{ currentEditionYear }}</span></div>
                            <div class="w-[14%] pr-4"><span class="cooper-baseline whitespace-nowrap">Trophées obtenus</span></div>
                            <div class="w-[10%]"><span class="cooper-baseline">Action</span></div>
                        </div>

                        <div
                            v-for="company in donorCandidates"
                            :key="company.id"
                            class="flex items-center border-b border-base-200 px-5 py-3 last:border-b-0"
                        >
                            <div class="flex w-[16%] items-center gap-3 pr-4">
                                <div
                                    class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-base-200 text-sm font-semibold"
                                    :style="{
                                        backgroundColor: company.primaryColor || '#E5E7EB',
                                        color: readableTextColor(company.primaryColor || '#E5E7EB'),
                                    }"
                                >
                                    <span class="cooper-baseline">{{ companyBadgeLabel(company.name) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="cooper-text-baseline truncate font-medium text-base-content">{{ company.name }}</div>
                                </div>
                            </div>

                            <div class="w-[12%] pr-4 text-sm text-base-content/70">
                                <span class="cooper-baseline">{{ formatRegistrationDate(company.created_at) }}</span>
                            </div>

                            <div class="w-[24%] pr-4 text-sm text-base-content/70">
                                <span class="cooper-baseline">{{ formatFullAddress(company.address, company.npa, company.localite) }}</span>
                            </div>

                            <div class="w-[10%] pr-4 text-base-content/70">
                                <span class="cooper-baseline">{{ company.employee_count ?? '—' }}</span>
                            </div>

                            <div class="w-[14%] pr-4">
                                <span class="inline-flex rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                                    <span class="cooper-baseline">{{ formatCollections(company.collections_count) }}</span>
                                </span>
                            </div>

                            <div class="w-[14%] pr-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                    <span class="cooper-baseline">{{ formatTrophies(company.donor_trophies_won) }}</span>
                                </span>
                            </div>

                            <div class="w-[10%]">
                                <button
                                    type="button"
                                    class="cursor-pointer rounded border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-600 transition hover:bg-stone-100 font-cooper"
                                >
                                    <span class="cooper-baseline">Attribuer le prix</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="cooper-text-baseline text-sm text-base-content/50">
                    Contenu à définir pour cet onglet.
                </div>
            </template>
        </section>
    </AdminLayout>
</template>
