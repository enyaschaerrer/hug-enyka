<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type ParticipationCompany = {
    name: string;
    primaryColor: string;
    connected: number;
    total: number;
    rate: number | null;
};

type AbandonStep = { label: string; count: number; rate: number | null };

type KpiValue = {
    label: string;
    value: number | null;
    available: boolean;
    note?: string | null;
    predefined?: { source: string; count: number }[];
    freeText?: string[];
    isVisits?: boolean;
    isHighlighted?: boolean;
    isLabelled?: boolean;
    isCobranded?: boolean;
    publicCount?: number;
    anonymousCount?: number;
    isSpacer?: boolean;
    companies?: ParticipationCompany[];
    abandonSteps?: AbandonStep[];
};

type KpiPayload = {
    engagement: Record<string, KpiValue & { format?: string }> & {
        labelledCompanies: KpiValue & { publicCount?: number; anonymousCount?: number };
    };
};

const loading = ref(true);
const loadError = ref<string | null>(null);
const kpis = ref<KpiPayload | null>(null);
let refreshTimer: number | undefined;

const listsAtBottom = ref<Record<string, boolean>>({});

function onListScroll(event: Event, key: string) {
    const el = event.target as HTMLElement;
    listsAtBottom.value[key] = el.scrollTop + el.clientHeight >= el.scrollHeight - 4;
}

const selectedCompanies = ref<Set<string>>(new Set());
const companySearch = ref('');
const searchFocused = ref(false);

const allCompanies = computed((): ParticipationCompany[] => {
    if (!kpis.value) return [];
    const seen = new Set<string>();
    const result: ParticipationCompany[] = [];
    for (const key of ['connectedUsers', 'participationRate', 'conversionRate']) {
        for (const c of (kpis.value.engagement[key]?.companies ?? []) as ParticipationCompany[]) {
            if (!seen.has(c.name)) { seen.add(c.name); result.push(c); }
        }
    }
    return result.sort((a, b) => a.name.localeCompare(b.name));
});

const searchedCompanies = computed(() => {
    const q = companySearch.value.toLowerCase().trim();
    return q ? allCompanies.value.filter(c => c.name.toLowerCase().includes(q)) : allCompanies.value;
});

function toggleCompany(name: string) {
    const next = new Set(selectedCompanies.value);
    if (next.has(name)) next.delete(name); else next.add(name);
    selectedCompanies.value = next;
}

function filterCompanies(companies: ParticipationCompany[]): ParticipationCompany[] {
    if (selectedCompanies.value.size === 0) return companies;
    return companies.filter(c => selectedCompanies.value.has(c.name));
}

function computeFilteredValue(card: KpiValue & { format?: string }): number | string | null {
    if (!card.companies) return card.value;
    const filtered = filterCompanies(card.companies);
    if (filtered.length === 0) return null;
    if (card.format === 'percent') {
        const num = filtered.reduce((s, c) => s + c.connected, 0);
        const den = filtered.reduce((s, c) => s + c.total, 0);
        return den > 0 ? Math.round((num / den) * 1000) / 10 : null;
    }
    return filtered.reduce((s, c) => s + c.connected, 0);
}

const VISIT_PERIODS = [
    { key: '30d',  label: 'Mois en cours', minMonth: 1 },
    { key: '3m',   label: '3 derniers mois', minMonth: 4 },
    { key: 'year', label: 'Année en cours', minMonth: 1 },
] as const;
type VisitPeriod = typeof VISIT_PERIODS[number]['key'];

const currentMonth = new Date().getMonth() + 1;
const availableVisitPeriods = VISIT_PERIODS.filter((p) => currentMonth >= p.minMonth);

const labelledTab = ref<'trophy' | 'anonymous'>('trophy');
const displayedLabelledCount = ref(0);
let labelledAnimation: number | undefined;

function animateLabelledCount(target: number) {
    if (labelledAnimation) cancelAnimationFrame(labelledAnimation);
    const from = displayedLabelledCount.value;
    const duration = 700;
    const startTime = performance.now();
    function step(now: number) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        displayedLabelledCount.value = Math.round(from + (target - from) * eased);
        if (progress < 1) labelledAnimation = requestAnimationFrame(step);
    }
    labelledAnimation = requestAnimationFrame(step);
}

watch(labelledTab, () => {
    displayedLabelledCount.value = 0;
    setTimeout(() => {
        if (!kpis.value) return;
        const target = labelledTab.value === 'trophy'
            ? (kpis.value.engagement.labelledCompanies.publicCount ?? 0)
            : (kpis.value.engagement.labelledCompanies.anonymousCount ?? 0);
        animateLabelledCount(target);
    }, 200);
});

const visitsPeriod = ref<VisitPeriod>('30d');
const visitsCount = ref<number | null>(null);
const visitsLoading = ref(false);
const displayedCount = ref(0);
let countAnimation: number | undefined;

function animateCount(target: number) {
    if (countAnimation) cancelAnimationFrame(countAnimation);
    const from = displayedCount.value;
    const duration = 700;
    const startTime = performance.now();
    function step(now: number) {
        const progress = Math.min((now - startTime) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        displayedCount.value = Math.round(from + (target - from) * eased);
        if (progress < 1) countAnimation = requestAnimationFrame(step);
    }
    countAnimation = requestAnimationFrame(step);
}

async function fetchVisits() {
    visitsLoading.value = true;
    try {
        const res = await fetch(
            `/admin/api/kpis/page-visits?period=${visitsPeriod.value}`,
            { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } },
        );
        if (res.ok) {
            const data = await res.json();
            visitsCount.value = data.count;
        }
    } finally {
        visitsLoading.value = false;
    }
}

const engagementCards = computed(() => {
    if (!kpis.value) {
        return [];
    }

    return [
        { ...kpis.value.engagement.pageVisits, format: 'number', isVisits: true },
        { ...kpis.value.engagement.labelledCompanies, format: 'number', isHighlighted: true, isLabelled: true },
        { ...kpis.value.engagement.companySources, format: 'number', isHighlighted: true },
        { isSpacer: true, label: '__spacer__', value: null, available: true, isCobranded: true },
        { ...kpis.value.engagement.connectedUsers, format: 'number', isCobranded: true },
        { ...kpis.value.engagement.participationRate, format: 'percent', isCobranded: true },
        { ...kpis.value.engagement.conversionRate, format: 'percent', isCobranded: true },
        { ...kpis.value.engagement.questionnaireAbandonRate, format: 'percent', isCobranded: true },
    ];
});

function displayValue(value: number | null, format: string): string {
    if (value === null) {
        return 'N/A';
    }

    return format === 'percent' ? `${value}%` : value.toLocaleString('fr-CH');
}


async function fetchKpis() {
    const showInitialLoader = !kpis.value;
    loading.value = showInitialLoader;
    loadError.value = null;

    try {
        const res = await fetch('/admin/api/kpis', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
            kpis.value = await res.json();
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Erreur lors du chargement des KPIs.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        if (showInitialLoader) {
            loading.value = false;
        }
    }
}

watch(kpis, (val) => {
    if (!val) return;
    const target = labelledTab.value === 'trophy'
        ? (val.engagement.labelledCompanies.publicCount ?? 0)
        : (val.engagement.labelledCompanies.anonymousCount ?? 0);
    animateLabelledCount(target);
});

watch(visitsPeriod, () => { displayedCount.value = 0; fetchVisits(); });
watch(visitsCount, (val) => { if (val !== null) animateCount(val); });

onMounted(() => {
    fetchKpis();
    fetchVisits();
    refreshTimer = window.setInterval(fetchKpis, 30000);
});

onUnmounted(() => {
    if (refreshTimer) window.clearInterval(refreshTimer);
    if (countAnimation) cancelAnimationFrame(countAnimation);
    if (labelledAnimation) cancelAnimationFrame(labelledAnimation);
});
</script>

<template>
    <AdminLayout>
        <section class="min-h-full rounded-sm bg-[var(--color-pampas-50)] p-1 pr-4 text-[#1f1f22]">
<div v-if="loading" class="text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert border-0 bg-red-600 text-white">
                <span>{{ loadError }}</span>
            </div>

            <div v-else-if="kpis" class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="card in engagementCards"
                    :key="card.label"
                    class="flex flex-col rounded-2xl border p-5 shadow-sm"
                    :class="[
                        card.available ? '' : 'opacity-45 grayscale',
                        (card.isVisits || card.isHighlighted) ? 'border-2 border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-50)]'
                        : card.isCobranded ? 'border-2 border-[var(--color-martinique-700)] bg-[var(--color-martinique-50)]'
                        : 'border-base-300 bg-white',
                    ]"
                >
                    <!-- Card filtre KPIs co-brandés -->
                    <template v-if="card.isSpacer">
                        <p class="text-lg font-semibold text-[#000]">Site co-brandé</p>
                        <div class="relative mt-1.5">
                            <svg
                                xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="absolute left-3 top-1/2 -translate-y-1/2 transition ease-in-out"
                                :class="searchFocused ? 'text-[var(--color-martinique-700)]' : 'text-[var(--color-martinique-300)]'"
                            ><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
                            <input
                                v-model="companySearch"
                                type="search"
                                placeholder="Rechercher une entreprise…"
                                class="search-no-clear w-full rounded-lg border border-[var(--color-martinique-100)] bg-base-100 py-2 pl-9 pr-8 text-sm font-medium text-[var(--color-martinique-700)] outline-none transition ease-in-out focus:border-[var(--color-martinique-700)]"
                                @focus="searchFocused = true"
                                @blur="searchFocused = false"
                            />
                            <button
                                v-if="companySearch"
                                type="button"
                                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[var(--color-martinique-700)] transition ease-in-out hover:opacity-70"
                                @click="companySearch = ''"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                            </button>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-xs font-medium text-[#000]">Filtrer</span>
                            <button
                                v-if="selectedCompanies.size > 0"
                                type="button"
                                class="text-xs font-medium text-[var(--color-martinique-700)] transition hover:opacity-70"
                                @click="selectedCompanies = new Set()"
                            >Réinitialiser</button>
                        </div>
                        <div class="relative -mb-5">
                            <div class="max-h-48 overflow-y-auto pb-4" @scroll="onListScroll($event, '__filter__')">
                                <div
                                    v-for="company in searchedCompanies"
                                    :key="company.name"
                                    class="flex cursor-pointer items-center gap-3 py-2"
                                    @click="toggleCompany(company.name)"
                                >
                                    <div
                                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-medium text-white"
                                        :style="{ backgroundColor: company.primaryColor }"
                                    >{{ company.name.slice(0, 2).toUpperCase() }}</div>
                                    <span class="min-w-0 flex-1 truncate text-sm font-semibold text-base-content/80">{{ company.name }}</span>
                                    <div
                                        class="h-4 w-4 shrink-0 rounded-sm border transition"
                                        :class="selectedCompanies.has(company.name)
                                            ? 'border-[var(--color-martinique-700)] bg-[var(--color-martinique-700)]'
                                            : 'border-[var(--color-martinique-100)] bg-white'"
                                    >
                                        <svg v-if="selectedCompanies.has(company.name)" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    </div>
                                </div>
                                <p v-if="searchedCompanies.length === 0" class="py-4 text-center text-xs text-base-content/40">Aucun résultat</p>
                            </div>
                            <div
                                class="pointer-events-none absolute bottom-0 left-0 right-0 h-14 transition-opacity duration-300"
                                :style="{ background: `linear-gradient(to top, ${card.isCobranded ? 'var(--color-martinique-50)' : (card.isHighlighted || card.isVisits) ? 'var(--color-razzmatazz-50)' : 'white'}, transparent)` }"
                                :class="listsAtBottom['__filter__'] ? 'opacity-0' : 'opacity-100'"
                            ></div>
                        </div>
                    </template>

                    <template v-else>
                    <p class="text-lg font-semibold text-[#000]">{{ card.isLabelled ? `${card.label} (${card.value})` : card.label }}</p>

                    <!-- Card visites : période type GA -->
                    <template v-if="card.isVisits">
                        <div class="mt-4 flex gap-1.5">
                            <button
                                v-for="p in availableVisitPeriods"
                                :key="p.key"
                                type="button"
                                class="rounded-md border px-4 py-1.5 text-sm font-medium transition"
                                :class="visitsPeriod === p.key
                                    ? 'border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] text-white'
                                    : 'border-[var(--color-razzmatazz-100)] bg-white text-[#000] hover:text-[var(--color-razzmatazz-700)] ease-in-out'"
                                @click="visitsPeriod = p.key"
                            >
                                {{ p.label }}
                            </button>
                        </div>
                        <div class="mt-9 flex items-center gap-2 text-[var(--color-razzmatazz-700)]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user-round shrink-0"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 0 0-16 0"/></svg>
                            <Transition name="kpi-fade" mode="out-in">
                                <p :key="visitsPeriod" class="text-[3.25rem] font-bold leading-none">
                                    {{ visitsLoading ? '…' : visitsCount !== null ? displayedCount.toLocaleString('fr-CH') : 'N/A' }}
                                </p>
                            </Transition>
                        </div>
                    </template>

                    <!-- Card connectés / participation / conversion : liste scrollable par entreprise -->
                    <template v-else-if="card.companies !== undefined">
                        <p class="mt-2 text-4xl font-bold" :class="card.isCobranded ? 'text-[var(--color-martinique-700)]' : ''">
                            {{ computeFilteredValue(card) !== null ? (card.format === 'percent' ? computeFilteredValue(card) + '%' : displayValue(computeFilteredValue(card) as number, 'number')) : 'N/A' }}
                        </p>
                        <div v-if="card.available && card.companies.length > 0" class="relative mt-3 -mb-5">
                            <div class="min-h-48 max-h-48 overflow-y-auto pr-1 pb-4 pt-2 border-t" :class="card.isCobranded ? 'border-[var(--color-martinique-100)]' : 'border-base-200'" @scroll="onListScroll($event, card.label)">
                            <div
                                v-for="company in filterCompanies(card.companies)"
                                :key="company.name"
                                class="flex cursor-pointer items-center gap-3 py-2"
                                @click="toggleCompany(company.name)"
                            >
                                <div
                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-medium text-white"
                                    :style="{ backgroundColor: company.primaryColor }"
                                >
                                    {{ company.name.slice(0, 2).toUpperCase() }}
                                </div>
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-base-content/80">{{ company.name }}</span>
                                <template v-if="card.format === 'percent'">
                                    <span class="shrink-0 text-xs text-base-content/50">{{ company.connected }}/{{ company.total }}</span>
                                    <span class="w-11 shrink-0 text-right text-sm font-semibold" :class="card.isCobranded ? 'text-[var(--color-martinique-700)]' : ''">
                                        {{ company.rate !== null ? company.rate + '%' : '–' }}
                                    </span>
                                </template>
                                <span v-else class="shrink-0 text-sm font-semibold" :class="card.isCobranded ? 'text-[var(--color-martinique-700)]' : ''">{{ company.connected }}</span>
                            </div>
                            </div>
                            <div
                                class="pointer-events-none absolute bottom-0 left-0 right-0 h-14 transition-opacity duration-300"
                                :style="{ background: `linear-gradient(to top, ${card.isCobranded ? 'var(--color-martinique-50)' : (card.isHighlighted || card.isVisits) ? 'var(--color-razzmatazz-50)' : 'white'}, transparent)` }"
                                :class="listsAtBottom[card.label] ? 'opacity-0' : 'opacity-100'"
                            ></div>
                        </div>
                        <p v-if="card.note && (!card.available || card.companies.length === 0 || card.format !== 'percent')" class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                    </template>

                    <!-- Card sources : liste scrollable -->
                    <template v-else-if="card.predefined !== undefined">
                        <div v-if="card.available" class="relative mt-3">
                            <div class="h-40 overflow-y-auto border-t border-[var(--color-razzmatazz-100)] pr-1 pt-2" @scroll="onListScroll($event, card.label)">
                                <div
                                    v-for="item in card.predefined"
                                    :key="item.source"
                                    class="flex items-center justify-between gap-2 py-1.5 text-sm"
                                >
                                    <span class="font-medium text-[#000]">{{ item.source }}</span>
                                    <span class="shrink-0 font-semibold text-[var(--color-razzmatazz-700)]">{{ item.count }}</span>
                                </div>
                            </div>
                            <div
                                class="pointer-events-none absolute bottom-0 left-0 right-0 h-14 transition-opacity duration-300"
                                :style="{ background: `linear-gradient(to top, ${card.isHighlighted ? 'var(--color-razzmatazz-50)' : 'white'}, transparent)` }"
                                :class="listsAtBottom[card.label] ? 'opacity-0' : 'opacity-100'"
                            ></div>
                        </div>
                        <p v-else class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                    </template>

                    <!-- Card abandon questionnaire -->
                    <template v-else-if="card.abandonSteps !== undefined">
                        <p class="mt-2 text-4xl font-bold text-[var(--color-martinique-700)]">
                            {{ card.available ? (card.value !== null ? card.value + '%' : 'N/A') : 'N/A' }}
                        </p>
                        <div v-if="card.available" class="mt-3 space-y-2">
                            <div
                                v-for="step in card.abandonSteps"
                                :key="step.label"
                                class="flex items-center justify-between gap-2"
                            >
                                <span class="text-sm text-base-content/70">{{ step.label }} : <span class="font-semibold text-[#000]">{{ step.rate !== null ? step.rate + '%' : '–' }}</span></span>
                                <span class="text-sm font-semibold text-[var(--color-martinique-700)]">{{ step.count }}</span>
                            </div>
                        </div>
                        <p class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                    </template>

                    <!-- Card entreprises labellisées -->
                    <template v-else-if="card.isLabelled">
                        <div class="mt-4 flex gap-1.5">
                            <button
                                type="button"
                                class="rounded-md border px-4 py-1.5 text-sm font-medium transition ease-in-out"
                                :class="labelledTab === 'trophy'
                                    ? 'border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] text-white'
                                    : 'border-[var(--color-razzmatazz-100)] bg-white text-[#000] hover:text-[var(--color-razzmatazz-700)]'"
                                @click="labelledTab = 'trophy'"
                            >Participation au Prix du Cœur</button>
                            <button
                                type="button"
                                class="rounded-md border px-4 py-1.5 text-sm font-medium transition ease-in-out"
                                :class="labelledTab === 'anonymous'
                                    ? 'border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] text-white'
                                    : 'border-[var(--color-razzmatazz-100)] bg-white text-[#000] hover:text-[var(--color-razzmatazz-700)]'"
                                @click="labelledTab = 'anonymous'"
                            >Participation anonyme</button>
                        </div>
                        <div class="mt-9 flex items-center gap-2 text-[var(--color-razzmatazz-700)]">
                            <Transition name="kpi-fade" mode="out-in">
                                <div :key="labelledTab" class="flex items-center gap-2">
                                    <svg v-if="labelledTab === 'trophy'" xmlns="http://www.w3.org/2000/svg" width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"/></svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="54" height="54" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M10.733 5.076A10.744 10.744 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><path d="M2 2l20 20"/><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/></svg>
                                    <p class="text-[3.25rem] font-bold leading-none">{{ displayedLabelledCount }}</p>
                                </div>
                            </Transition>
                        </div>
                    </template>

                    <!-- Cards génériques -->
                    <template v-else>
                        <p class="mt-2 text-4xl font-bold" :class="card.isHighlighted ? 'text-[var(--color-razzmatazz-700)]' : ''">{{ displayValue(card.value, card.format) }}</p>
                        <p class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                    </template>
                    </template>
                </article>
            </div>
        </section>
    </AdminLayout>
</template>

<style scoped>
.search-no-clear::-webkit-search-cancel-button {
    display: none;
}

.kpi-fade-enter-active,
.kpi-fade-leave-active {
    transition: opacity 0.2s ease;
}
.kpi-fade-enter-from,
.kpi-fade-leave-to {
    opacity: 0;
}
</style>
