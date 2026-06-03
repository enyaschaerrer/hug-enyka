<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { readableTextColor } from '../../utils/contrast';

type AppState = { csrfToken: string };
type TrophyTab = 'donneur' | 'ambassadeur' | 'jury';
type ApiTrophyType = 'donneur' | 'ambassadeur' | 'prixJury';

type TrophyCandidate = {
    id: number;
    name: string;
    logo: string | null;
    created_at: string | null;
    primaryColor: string | null;
    employee_count: number | null;
    address: string | null;
    npa: string | null;
    localite: string | null;
    collections_count: number;
    trophies_won: number;
    current_rank: number | null;
};

type TrophyWinner = {
    id: number;
    rank: number;
    name: string;
    logo: string | null;
    primaryColor: string | null;
    employee_count?: number | null;
    address?: string | null;
    npa?: string | null;
    localite?: string | null;
};

type TrophyHistoryEdition = {
    year: number;
    winners: TrophyWinner[];
};

type TrophyTabPayload = {
    type: ApiTrophyType;
    mode: 'podium' | 'single';
    max_rank: number;
    is_complete: boolean;
    candidates: TrophyCandidate[];
    current_winners: TrophyWinner[];
    history: TrophyHistoryEdition[];
};

type TrophyOverviewPayload = {
    editionYear: number;
    tabs: Record<ApiTrophyType, TrophyTabPayload>;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

const tabToApiType: Record<TrophyTab, ApiTrophyType> = {
    donneur: 'donneur',
    ambassadeur: 'ambassadeur',
    jury: 'prixJury',
};

const currentTrophyTitle: Record<TrophyTab, string> = {
    donneur: 'Meilleur donneur',
    ambassadeur: 'Meilleur ambassadeur',
    jury: 'Coup de cœur du jury',
};

const activeTab = ref<TrophyTab>('donneur');
const overview = ref<TrophyOverviewPayload | null>(null);
const loading = ref(true);
const loadError = ref<string | null>(null);
const actionError = ref<string | null>(null);
const submittingKey = ref<string | null>(null);

const currentEditionYear = computed(() => overview.value?.editionYear ?? new Date().getFullYear());
const currentTabType = computed<ApiTrophyType>(() => tabToApiType[activeTab.value]);
const currentTabData = computed<TrophyTabPayload | null>(() => {
    if (!overview.value) {
        return null;
    }

    return overview.value.tabs[currentTabType.value] ?? null;
});

const isCompetitorListDisabled = computed(() => currentTabData.value?.is_complete ?? false);

function companyBadgeLabel(name: string): string {
    const sanitized = name.replace(/[^a-zA-Z0-9]/g, '');

    if (!sanitized) {
        return '—';
    }

    const first = sanitized[0]?.toUpperCase() ?? '';
    const second = sanitized[1] ? sanitized[1].toUpperCase() : '';

    return `${first}${second}`;
}

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

function rankLabel(rank: number, type: ApiTrophyType): string {
    if (type === 'prixJury') {
        return 'Lauréat';
    }

    if (rank === 1) return '1re place';
    if (rank === 2) return '2e place';
    return '3e place';
}

function rankButtonClass(rank: number, isSelected: boolean): string {
    const styles = {
        1: isSelected
            ? 'border-[#d7ccb0] bg-[#d7ccb0] text-white'
            : 'border-[#d7ccb0] bg-[#f4efe3] text-[#8b7a52] hover:bg-[#e7dcc2]',
        2: isSelected
            ? 'border-[#c48772] bg-[#c48772] text-white'
            : 'border-[#c48772] bg-[#f4e2db] text-[#9e5f4d] hover:bg-[#ebd2c8]',
        3: isSelected
            ? 'border-[#56627e] bg-[#56627e] text-white'
            : 'border-[#56627e] bg-[#e4e8f0] text-[#44506b] hover:bg-[#d6dce8]',
    } as const;

    return styles[rank as keyof typeof styles] ?? styles[3];
}

function rankCardClass(rank: number): string {
    const styles = {
        1: 'border-[#d7ccb0] bg-[#fbf8f0]',
        2: 'border-[#c48772] bg-[#fbf1ec]',
        3: 'border-[#56627e] bg-[#f2f4f8]',
    } as const;

    return styles[rank as keyof typeof styles] ?? styles[3];
}

function rankAccentClass(rank: number): string {
    const styles = {
        1: 'text-[#8b7a52]',
        2: 'text-[#9e5f4d]',
        3: 'text-[#44506b]',
    } as const;

    return styles[rank as keyof typeof styles] ?? styles[3];
}

function winnerForRank(rank: number): TrophyWinner | null {
    return currentTabData.value?.current_winners.find((winner) => winner.rank === rank) ?? null;
}

function historySummary(edition: TrophyHistoryEdition, type: ApiTrophyType): string {
    return `Année ${edition.year}`;
}

async function fetchOverview() {
    loading.value = true;
    loadError.value = null;

    try {
        const res = await fetch('/admin/api/trophee', {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
            overview.value = await res.json();
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

async function assignPrize(companyId: number, rank: number) {
    const type = currentTabType.value;
    actionError.value = null;
    submittingKey.value = `${type}-${companyId}-${rank}`;

    try {
        const res = await fetch('/admin/api/trophee/assign', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ type, company_id: companyId, rank }),
        });

        if (res.ok) {
            overview.value = await res.json();
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            actionError.value = 'Impossible d’attribuer ce prix pour le moment.';
        }
    } catch {
        actionError.value = 'Erreur réseau.';
    } finally {
        submittingKey.value = null;
    }
}

async function removePrize(rank: number) {
    const type = currentTabType.value;
    actionError.value = null;
    submittingKey.value = `${type}-remove-${rank}`;

    try {
        const res = await fetch('/admin/api/trophee/assign', {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ type, rank }),
        });

        if (res.ok) {
            overview.value = await res.json();
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            actionError.value = 'Impossible d’annuler cette attribution pour le moment.';
        }
    } catch {
        actionError.value = 'Erreur réseau.';
    } finally {
        submittingKey.value = null;
    }
}

onMounted(fetchOverview);
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
                    <span class="cooper-baseline">Meilleur donneur</span>
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
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>

            <template v-else-if="currentTabData">
                <div class="mb-5">
                    <h2 class="cooper-text-baseline text-xl font-semibold text-[#5a002a]">
                        Attribution du prix : {{ currentTrophyTitle[activeTab] }}
                    </h2>
                </div>

                <div v-if="actionError" class="alert alert-error mb-5">
                    <span class="cooper-baseline">{{ actionError }}</span>
                </div>

                <div v-if="currentTabData.current_winners.length === 0" class="mb-5">
                    <p class="cooper-text-baseline text-sm text-base-content/55">Aucun vainqueur pour le moment.</p>
                </div>

                <div
                    v-else-if="currentTabData.mode === 'single'"
                    class="mb-5 rounded-box border border-amber-200 bg-white px-5 py-5"
                >
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-full border border-amber-200 bg-amber-50 p-3">
                                <img
                                    v-if="currentTabData.current_winners[0]?.logo"
                                    :src="currentTabData.current_winners[0].logo || undefined"
                                    :alt="currentTabData.current_winners[0].name"
                                    class="max-h-full max-w-full object-contain"
                                />
                                <span
                                    v-else
                                    class="cooper-baseline text-lg font-semibold"
                                    :style="{
                                        color: readableTextColor(currentTabData.current_winners[0].primaryColor || '#FEF3C7'),
                                        backgroundColor: currentTabData.current_winners[0].primaryColor || '#FEF3C7',
                                    }"
                                >
                                    {{ companyBadgeLabel(currentTabData.current_winners[0].name) }}
                                </span>
                            </div>
                            <div>
                                <p class="cooper-text-baseline text-xs font-semibold uppercase tracking-wider text-amber-800/65">Lauréat</p>
                                <p class="cooper-text-baseline text-lg font-semibold text-base-content">{{ currentTabData.current_winners[0].name }}</p>
                                <p class="cooper-text-baseline text-sm text-base-content/55">
                                    {{ formatFullAddress(currentTabData.current_winners[0].address, currentTabData.current_winners[0].npa, currentTabData.current_winners[0].localite) }}
                                </p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="cursor-pointer rounded border border-red-200 bg-red-50 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100 font-cooper"
                            :disabled="submittingKey === `${currentTabType}-remove-1`"
                            @click="removePrize(1)"
                        >
                            <span class="cooper-baseline">{{ submittingKey === `${currentTabType}-remove-1` ? '...' : 'Annuler' }}</span>
                        </button>
                    </div>
                </div>

                <div
                    v-else
                    class="mb-5 grid gap-4 md:grid-cols-3"
                >
                    <div
                        v-for="rank in currentTabData.max_rank"
                        :key="rank"
                        class="rounded-box border px-5 py-4"
                        :class="winnerForRank(rank)
                            ? rankCardClass(rank)
                            : 'border-dashed border-base-300 bg-base-100/60'"
                    >
                        <template v-if="winnerForRank(rank)">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center overflow-hidden rounded-full border border-emerald-200 bg-emerald-50 p-2">
                                        <img
                                            v-if="winnerForRank(rank)?.logo"
                                            :src="winnerForRank(rank)?.logo || undefined"
                                            :alt="winnerForRank(rank)?.name || ''"
                                            class="max-h-full max-w-full object-contain"
                                        />
                                        <div
                                            v-else
                                            class="flex h-full w-full items-center justify-center rounded-full text-sm font-semibold"
                                            :style="{
                                                backgroundColor: winnerForRank(rank)?.primaryColor || '#E5E7EB',
                                                color: readableTextColor(winnerForRank(rank)?.primaryColor || '#E5E7EB'),
                                            }"
                                        >
                                            <span class="cooper-baseline">{{ companyBadgeLabel(winnerForRank(rank)?.name || '') }}</span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="cooper-text-baseline text-xs font-semibold uppercase tracking-wider" :class="rankAccentClass(rank)">
                                            {{ rankLabel(rank, currentTabType) }}
                                        </p>
                                        <p class="cooper-text-baseline font-semibold text-base-content">{{ winnerForRank(rank)?.name }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="cursor-pointer rounded border border-red-200 bg-red-50 px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-100 font-cooper"
                                    :disabled="submittingKey === `${currentTabType}-remove-${rank}`"
                                    @click="removePrize(rank)"
                                >
                                    <span class="cooper-baseline">{{ submittingKey === `${currentTabType}-remove-${rank}` ? '...' : 'Annuler' }}</span>
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <p class="cooper-text-baseline text-xs font-semibold uppercase tracking-wider text-base-content/45">
                                {{ rankLabel(rank, currentTabType) }}
                            </p>
                            <p class="cooper-text-baseline mt-2 text-sm text-base-content/45">Aucun gagnant attribué pour le moment.</p>
                        </template>
                    </div>
                </div>

                <details v-if="currentTabData.history.length > 0" class="collapse-arrow collapse mb-5 bg-white">
                    <summary class="collapse-title min-h-11 px-4 py-3 text-sm font-medium text-base-content/60">
                        <span class="cooper-baseline">Historique des gagnants ({{ currentTabData.history.length }})</span>
                    </summary>
                    <div class="collapse-content px-4 pb-4">
                        <div
                            v-for="edition in currentTabData.history"
                            :key="edition.year"
                            class="py-4"
                        >
                            <p class="cooper-text-baseline mb-3 text-sm font-semibold text-base-content/65">
                                {{ historySummary(edition, currentTabType) }}
                            </p>
                            <div class="grid gap-3 md:grid-cols-3">
                                <div
                                    v-for="winner in edition.winners"
                                    :key="`${edition.year}-${winner.rank}-${winner.id}`"
                                    class="rounded-lg border border-base-300 bg-white px-4 py-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-11 w-11 items-center justify-center overflow-hidden rounded-full border border-base-200 bg-base-100 p-2">
                                            <img
                                                v-if="winner.logo"
                                                :src="winner.logo"
                                                :alt="winner.name"
                                                class="max-h-full max-w-full object-contain"
                                            />
                                            <div
                                                v-else
                                                class="flex h-full w-full items-center justify-center rounded-full text-xs font-semibold"
                                                :style="{
                                                    backgroundColor: winner.primaryColor || '#E5E7EB',
                                                    color: readableTextColor(winner.primaryColor || '#E5E7EB'),
                                                }"
                                            >
                                                <span class="cooper-baseline">{{ companyBadgeLabel(winner.name) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <p class="cooper-text-baseline text-xs font-semibold uppercase tracking-wider text-base-content/45">
                                                {{ rankLabel(winner.rank, currentTabType) }}
                                            </p>
                                            <p class="cooper-text-baseline text-sm font-medium text-base-content">{{ winner.name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>

                <div v-if="currentTabData.candidates.length === 0" class="cooper-text-baseline text-sm text-base-content/50">
                    Aucune entreprise éligible pour le moment.
                </div>

                <div v-else>
                    <h3 class="cooper-text-baseline mb-3 text-xl font-semibold text-[#5a002a]">
                        Liste des concurrents
                    </h3>

                    <div
                        class="border border-base-300 bg-white transition-opacity"
                        :class="isCompetitorListDisabled ? 'pointer-events-none opacity-45' : ''"
                    >
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
                            v-for="company in currentTabData.candidates"
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

                            <div class="w-[14%] pr-4 text-sm text-base-content/70">
                                <span class="cooper-baseline">{{ formatCollections(company.collections_count) }}</span>
                            </div>

                            <div class="w-[14%] pr-4 text-sm text-base-content/70">
                                <span class="cooper-baseline">{{ formatTrophies(company.trophies_won) }}</span>
                            </div>

                            <div class="w-[10%]">
                                <div v-if="currentTabData.mode === 'podium'" class="flex items-center gap-1">
                                    <button
                                        v-for="rank in currentTabData.max_rank"
                                        :key="`${company.id}-${rank}`"
                                        type="button"
                                        class="cursor-pointer rounded border px-2.5 py-1 text-xs font-medium transition font-cooper"
                                        :class="rankButtonClass(rank, company.current_rank === rank)"
                                        :disabled="submittingKey === `${currentTabType}-${company.id}-${rank}`"
                                        @click="assignPrize(company.id, rank)"
                                    >
                                        <span class="cooper-baseline">{{ submittingKey === `${currentTabType}-${company.id}-${rank}` ? '...' : rank }}</span>
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    class="cursor-pointer rounded border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 transition hover:bg-emerald-100 font-cooper"
                                    :disabled="submittingKey === `${currentTabType}-${company.id}-1`"
                                    @click="assignPrize(company.id, 1)"
                                >
                                    <span class="cooper-baseline">{{ submittingKey === `${currentTabType}-${company.id}-1` ? '...' : 'Attribuer le prix' }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </section>
    </AdminLayout>
</template>
