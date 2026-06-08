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
const competitorSearch = ref('');

const currentEditionYear = computed(() => overview.value?.editionYear ?? new Date().getFullYear());
const currentTabType = computed<ApiTrophyType>(() => tabToApiType[activeTab.value]);
const currentTabData = computed<TrophyTabPayload | null>(() => {
    if (!overview.value) {
        return null;
    }

    return overview.value.tabs[currentTabType.value] ?? null;
});

const isCompetitorListDisabled = computed(() => currentTabData.value?.is_complete ?? false);
const filteredCandidates = computed(() => {
    const candidates = currentTabData.value?.candidates ?? [];
    const query = competitorSearch.value.trim().toLowerCase();

    if (!query) {
        return candidates;
    }

    return candidates.filter((company) => {
        return [
            company.name,
            company.address,
            company.npa,
            company.localite,
        ]
            .filter(Boolean)
            .some((value) => value?.toLowerCase().includes(query));
    });
});

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

    if (rank === 1) return '1ère place';
    if (rank === 2) return '2ème place';
    return '3ème place';
}

function rankButtonClass(rank: number, isSelected: boolean): string {
    const styles = {
        1: isSelected
            ? 'border-[var(--color-podium-gold)] bg-[var(--color-podium-gold)] text-white'
            : 'border-[var(--color-podium-gold)] bg-[var(--color-podium-gold-soft)] text-[var(--color-podium-gold-text)] hover:bg-[var(--color-podium-gold-soft-hover)]',
        2: isSelected
            ? 'border-[var(--color-podium-silver)] bg-[var(--color-podium-silver)] text-white'
            : 'border-[var(--color-podium-silver)] bg-[var(--color-podium-silver-soft)] text-[var(--color-podium-silver-text)] hover:bg-[var(--color-podium-silver-soft-hover)]',
        3: isSelected
            ? 'border-[var(--color-podium-bronze)] bg-[var(--color-podium-bronze)] text-white'
            : 'border-[var(--color-podium-bronze)] bg-[var(--color-podium-bronze-soft)] text-[var(--color-podium-bronze-text)] hover:bg-[var(--color-podium-bronze-soft-hover)]',
    } as const;

    return styles[rank as keyof typeof styles] ?? styles[3];
}

function singleAwardButtonClass(): string {
    return 'border-[var(--color-podium-gold)] bg-[var(--color-podium-gold-soft)] text-[var(--color-podium-gold-text)] hover:bg-[var(--color-podium-gold-soft-hover)]';
}

function rankCardClass(rank: number): string {
    const styles = {
        1: 'border-[var(--color-podium-gold)] bg-[var(--color-podium-gold-surface)]',
        2: 'border-[var(--color-podium-silver)] bg-[var(--color-podium-silver-surface)]',
        3: 'border-[var(--color-podium-bronze)] bg-[var(--color-podium-bronze-surface)]',
    } as const;

    return styles[rank as keyof typeof styles] ?? styles[3];
}

function rankDividerColor(rank: number): string {
    const colors = {
        1: 'var(--color-podium-gold)',
        2: 'var(--color-podium-silver)',
        3: 'var(--color-podium-bronze)',
    } as const;
    return colors[rank as keyof typeof colors] ?? colors[3];
}

function rankAccentClass(rank: number): string {
    const styles = {
        1: 'text-[var(--color-podium-gold-text)]',
        2: 'text-[var(--color-podium-silver-text)]',
        3: 'text-[var(--color-podium-bronze-text)]',
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
                <h1 class="text-3xl font-semibold">Trophée - Édition {{ currentEditionYear }}</h1>
                <p class="mt-1 text-lg text-base-content/60">
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
                    <span>Meilleur donneur</span>
                </button>
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'ambassadeur'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'ambassadeur'"
                >
                    <span>Meilleur ambassadeur</span>
                </button>
                <button
                    class="cursor-pointer px-5 py-2.5 text-sm font-medium transition font-cooper"
                    :class="activeTab === 'jury'
                        ? 'border-b-2 border-[#5a002a] text-[#5a002a]'
                        : 'text-base-content/50 hover:text-base-content'"
                    @click="activeTab = 'jury'"
                >
                    <span>Coup de cœur du jury</span>
                </button>
            </div>

            <div v-if="loading" class="text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>

            <template v-else-if="currentTabData">
                <div class="mb-5">
                    <h2 class="text-xl font-semibold text-[#5a002a]">
                        Attribution du prix : {{ currentTrophyTitle[activeTab] }}
                    </h2>
                </div>

                <div v-if="actionError" class="alert alert-error mb-5">
                    <span>{{ actionError }}</span>
                </div>

                <div v-if="currentTabData.current_winners.length === 0" class="mb-5">
                    <p class="text-sm text-base-content/55">Aucun vainqueur pour le moment.</p>
                </div>

                <div
                    v-else-if="currentTabData.mode === 'single'"
                    class="mb-5 rounded-box border px-5 py-4"
                    :class="rankCardClass(1)"
                >
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <img
                                v-if="currentTabData.current_winners[0]?.logo"
                                :src="currentTabData.current_winners[0].logo || undefined"
                                :alt="currentTabData.current_winners[0].name"
                                class="h-10 w-auto max-w-[5rem] object-contain"
                            />
                            <div
                                v-else
                                class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold"
                                :style="{
                                    backgroundColor: currentTabData.current_winners[0].primaryColor || '#FEF3C7',
                                    color: readableTextColor(currentTabData.current_winners[0].primaryColor || '#FEF3C7'),
                                }"
                            >
                                <span>{{ companyBadgeLabel(currentTabData.current_winners[0].name) }}</span>
                            </div>
                            <div class="w-px -my-4 mx-3 self-stretch" :style="{ backgroundColor: rankDividerColor(1) }"></div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider" :class="rankAccentClass(1)">Coup de cœur du jury</p>
                                <p class="font-semibold text-base-content -mb-[5px]">{{ currentTabData.current_winners[0].name }}</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            class="cursor-pointer rounded p-1 transition-colors duration-200 ease-in-out hover:text-red-500"
                            :class="rankAccentClass(1)"
                            :disabled="submittingKey === `${currentTabType}-remove-1`"
                            @click="removePrize(1)"
                        >
                            <span v-if="submittingKey === `${currentTabType}-remove-1`" class="text-xs leading-none">…</span>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
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
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img
                                        v-if="winnerForRank(rank)?.logo"
                                        :src="winnerForRank(rank)?.logo || undefined"
                                        :alt="winnerForRank(rank)?.name || ''"
                                        class="h-10 w-auto max-w-[5rem] object-contain"
                                    />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold"
                                        :style="{
                                            backgroundColor: winnerForRank(rank)?.primaryColor || '#E5E7EB',
                                            color: readableTextColor(winnerForRank(rank)?.primaryColor || '#E5E7EB'),
                                        }"
                                    >
                                        <span>{{ companyBadgeLabel(winnerForRank(rank)?.name || '') }}</span>
                                    </div>
                                    <div class="w-px -my-4 mx-3 self-stretch" :style="{ backgroundColor: rankDividerColor(rank) }"></div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider" :class="rankAccentClass(rank)">
                                            {{ rankLabel(rank, currentTabType) }}
                                        </p>
                                        <p class="font-semibold text-base-content -mb-[5px]">{{ winnerForRank(rank)?.name }}</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="cursor-pointer rounded p-1 transition-colors duration-200 ease-in-out hover:text-red-500"
                                    :class="rankAccentClass(rank)"
                                    :disabled="submittingKey === `${currentTabType}-remove-${rank}`"
                                    @click="removePrize(rank)"
                                >
                                    <span v-if="submittingKey === `${currentTabType}-remove-${rank}`" class="text-xs leading-none">…</span>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex min-h-10 items-center gap-3">
                                <p class="text-xs font-semibold uppercase tracking-wider text-base-content/45">
                                    {{ rankLabel(rank, currentTabType) }}
                                </p>
                                <p class="text-sm text-base-content/45">Aucun gagnant attribué pour le moment.</p>
                            </div>
                        </template>
                    </div>
                </div>

                <details v-if="currentTabData.history.length > 0" class="collapse mb-3">
                    <summary class="collapse-title flex cursor-pointer list-none items-center gap-1.5 px-0 pt-1 pb-3 text-sm font-medium text-base-content/60 [&::-webkit-details-marker]:hidden">
                        <span>Historique des gagnants ({{ currentTabData.history.length }})</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 transition-transform duration-200 [[open]_&]:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </summary>
                    <div class="collapse-content px-0 pb-0">
                        <div
                            v-for="edition in currentTabData.history"
                            :key="edition.year"
                            class="mb-3 flex flex-wrap items-stretch gap-3 last:mb-0"
                        >
                            <div class="inline-flex min-w-[88px] items-center justify-center rounded-box border border-[#5a002a]/15 bg-[#f8e7ee] px-4 py-2 text-sm font-semibold text-[#5a002a]">
                                {{ edition.year }}
                            </div>
                            <div class="flex min-w-0 flex-1 flex-wrap gap-3">
                                <div
                                    v-for="winner in edition.winners"
                                    :key="`${edition.year}-${winner.rank}-${winner.id}`"
                                    class="flex min-w-[220px] flex-1 items-center gap-3 rounded-box border border-base-content/20 bg-base-200/60 px-4 py-3"
                                >
                                    <img
                                        v-if="winner.logo"
                                        :src="winner.logo"
                                        :alt="winner.name"
                                        class="h-10 w-auto max-w-[5rem] object-contain"
                                    />
                                    <div
                                        v-else
                                        class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold"
                                        :style="{
                                            backgroundColor: winner.primaryColor || '#E5E7EB',
                                            color: readableTextColor(winner.primaryColor || '#E5E7EB'),
                                        }"
                                    >
                                        <span>{{ companyBadgeLabel(winner.name) }}</span>
                                    </div>
                                    <div class="w-px -my-3 mx-1 self-stretch bg-base-300"></div>
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-wider text-base-content/45">
                                            {{ rankLabel(winner.rank, currentTabType) }}
                                        </p>
                                        <p class="font-semibold text-base-content -mb-[5px]">{{ winner.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </details>

                <div v-if="currentTabData.candidates.length === 0" class="text-sm text-base-content/50">
                    Aucune entreprise éligible pour le moment.
                </div>

                <div v-else>
                    <div class="mb-3 flex items-center justify-between gap-4">
                        <h3 class="text-xl font-semibold text-[#5a002a]">
                            Liste des concurrents
                        </h3>
                        <label class="input input-bordered flex w-full max-w-sm items-center gap-3 bg-white">
                            <span
                                class="material-symbols-outlined"
                                :class="isCompetitorListDisabled ? 'text-base-content/25' : 'text-base-content/45'"
                                aria-hidden="true"
                            >
                                search
                            </span>
                            <input
                                v-model="competitorSearch"
                                type="text"
                                class="w-full font-cooper"
                                :class="isCompetitorListDisabled ? 'cursor-not-allowed text-base-content/35' : ''"
                                placeholder="Rechercher une entreprise"
                                :disabled="isCompetitorListDisabled"
                            />
                        </label>
                    </div>

                    <div
                        class="border border-base-300 bg-white transition-opacity"
                        :class="isCompetitorListDisabled ? 'pointer-events-none opacity-45' : ''"
                    >
                        <div class="flex border-b border-base-300 bg-[#f8e7ee] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-[#5a002a]">
                            <div class="w-[16%] pr-4"><span>Entreprise</span></div>
                            <div class="w-[12%] pr-4"><span>Inscription</span></div>
                            <div class="w-[24%] pr-4"><span>Adresse</span></div>
                            <div class="w-[10%] pr-4"><span>Employés</span></div>
                            <div class="w-[14%] pr-4"><span>Campagnes en {{ currentEditionYear }}</span></div>
                            <div class="w-[14%] pr-4"><span class="whitespace-nowrap">Trophées obtenus</span></div>
                            <div class="w-[10%]"><span>Action</span></div>
                        </div>

                        <div
                            v-for="company in filteredCandidates"
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
                                    <span>{{ companyBadgeLabel(company.name) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-medium text-base-content">{{ company.name }}</div>
                                </div>
                            </div>

                            <div class="w-[12%] pr-4 text-sm text-base-content/70">
                                <span>{{ formatRegistrationDate(company.created_at) }}</span>
                            </div>

                            <div class="w-[24%] pr-4 text-sm text-base-content/70">
                                <span>{{ formatFullAddress(company.address, company.npa, company.localite) }}</span>
                            </div>

                            <div class="w-[10%] pr-4 text-base-content/70">
                                <span>{{ company.employee_count ?? '—' }}</span>
                            </div>

                            <div class="w-[14%] pr-4 text-sm text-base-content/70">
                                <span>{{ formatCollections(company.collections_count) }}</span>
                            </div>

                            <div class="w-[14%] pr-4 text-sm text-base-content/70">
                                <span>{{ formatTrophies(company.trophies_won) }}</span>
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
                                        <span>{{ submittingKey === `${currentTabType}-${company.id}-${rank}` ? '...' : rank }}</span>
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    class="cursor-pointer rounded border px-3 py-1 text-xs font-medium transition font-cooper"
                                    :class="singleAwardButtonClass()"
                                    :disabled="submittingKey === `${currentTabType}-${company.id}-1`"
                                    @click="assignPrize(company.id, 1)"
                                >
                                    <span>{{ submittingKey === `${currentTabType}-${company.id}-1` ? '...' : 'Attribuer le prix' }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-if="filteredCandidates.length === 0" class="px-5 py-4 text-sm text-base-content/50">
                            Aucune entreprise ne correspond à cette recherche.
                        </div>
                    </div>
                </div>
            </template>
        </section>
    </AdminLayout>
</template>
