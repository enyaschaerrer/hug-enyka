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

type KpiValue = {
    label: string;
    value: number | null;
    available: boolean;
    note?: string | null;
    predefined?: { source: string; count: number }[];
    freeText?: string[];
    isVisits?: boolean;
    companies?: ParticipationCompany[];
};

type KpiPayload = {
    engagement: Record<string, KpiValue & { format?: string }>;
};

const loading = ref(true);
const loadError = ref<string | null>(null);
const kpis = ref<KpiPayload | null>(null);
let refreshTimer: number | undefined;

const VISIT_PERIODS = [
    { key: '30d',  label: 'Mois en cours', minMonth: 1 },
    { key: '3m',   label: '3m',            minMonth: 4 },
    { key: '6m',   label: '6m',            minMonth: 7 },
    { key: 'year', label: 'Année en cours', minMonth: 1 },
] as const;
type VisitPeriod = typeof VISIT_PERIODS[number]['key'];

const currentMonth = new Date().getMonth() + 1;
const availableVisitPeriods = VISIT_PERIODS.filter((p) => currentMonth >= p.minMonth);

const visitsPeriod = ref<VisitPeriod>('30d');
const visitsCount = ref<number | null>(null);
const visitsLoading = ref(false);

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
        { ...kpis.value.engagement.labelledCompanies, format: 'number' },
        { ...kpis.value.engagement.companySources, format: 'number' },
        { ...kpis.value.engagement.pageVisits, format: 'number', isVisits: true },
        { ...kpis.value.engagement.participationRate, format: 'percent' },
        { ...kpis.value.engagement.conversionRate, format: 'percent' },
        { ...kpis.value.engagement.questionnaireAbandonRate, format: 'percent' },
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

watch(visitsPeriod, fetchVisits);

onMounted(() => {
    fetchKpis();
    fetchVisits();
    refreshTimer = window.setInterval(fetchKpis, 30000);
});

onUnmounted(() => {
    if (refreshTimer) {
        window.clearInterval(refreshTimer);
    }
});
</script>

<template>
    <AdminLayout>
        <section class="min-h-full rounded-sm bg-[var(--color-pampas-50)] p-1 pr-4 text-[#1f1f22]">
            <h1 class="mb-6 text-3xl font-semibold">Tableau de bord</h1>

            <div v-if="loading" class="text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert border-0 bg-red-600 text-white">
                <span>{{ loadError }}</span>
            </div>

            <div v-else-if="kpis" class="grid w-full grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="card in engagementCards"
                    :key="card.label"
                    class="flex flex-col rounded-2xl border border-base-300 bg-white p-5 shadow-sm"
                    :class="card.available ? '' : 'opacity-45 grayscale'"
                >
                    <p class="text-lg text-base-content/65">{{ card.label }}</p>

                    <!-- Card visites : période type GA -->
                    <template v-if="card.isVisits">
                        <div class="mt-3 flex gap-1">
                            <button
                                v-for="p in availableVisitPeriods"
                                :key="p.key"
                                type="button"
                                class="rounded-md px-2.5 py-1 text-xs font-medium transition"
                                :class="visitsPeriod === p.key
                                    ? 'bg-[var(--color-razzmatazz-700)] text-white'
                                    : 'text-base-content/60 hover:bg-base-200'"
                                @click="visitsPeriod = p.key"
                            >
                                {{ p.label }}
                            </button>
                        </div>
                        <p class="mt-2 text-4xl font-bold">
                            {{ visitsLoading ? '…' : visitsCount !== null ? visitsCount.toLocaleString('fr-CH') : 'N/A' }}
                        </p>
                    </template>

                    <!-- Card participation : liste scrollable par entreprise -->
                    <template v-else-if="card.companies !== undefined">
                        <div v-if="card.available && card.companies.length > 0" class="mt-3 max-h-48 overflow-y-auto pr-1">
                            <div
                                v-for="company in card.companies"
                                :key="company.name"
                                class="flex items-center gap-3 py-2"
                            >
                                <div
                                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-medium text-white"
                                    :style="{ backgroundColor: company.primaryColor }"
                                >
                                    {{ company.name.slice(0, 2).toUpperCase() }}
                                </div>
                                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-base-content/80">{{ company.name }}</span>
                                <span class="shrink-0 text-xs text-base-content/50">{{ company.connected }}/{{ company.total }}</span>
                                <span class="w-11 shrink-0 text-right text-sm font-semibold">
                                    {{ company.rate !== null ? company.rate + '%' : '–' }}
                                </span>
                            </div>
                        </div>
                        <p v-else class="mt-3 text-xs text-base-content/45">Aucune donnée de participation pour le moment.</p>
                    </template>

                    <!-- Card sources : liste scrollable -->
                    <template v-else-if="card.predefined !== undefined">
                        <div v-if="card.available" class="mt-3 max-h-40 overflow-y-auto pr-1">
                            <div
                                v-for="item in card.predefined"
                                :key="item.source"
                                class="flex items-center justify-between gap-2 py-1 text-sm"
                            >
                                <span class="text-base-content/80">{{ item.source }}</span>
                                <span class="shrink-0 font-semibold">{{ item.count }}</span>
                            </div>
                            <template v-if="card.freeText && card.freeText.length > 0">
                                <hr v-if="card.predefined.length > 0" class="my-2 border-base-200" />
                                <div
                                    v-for="text in card.freeText"
                                    :key="text"
                                    class="flex items-center justify-between gap-2 py-1 text-sm"
                                >
                                    <span class="text-base-content/80">{{ text }}</span>
                                    <span class="shrink-0 text-xs text-base-content/40">Autre</span>
                                </div>
                            </template>
                        </div>
                        <p v-else class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                    </template>

                    <!-- Cards génériques -->
                    <template v-else>
                        <p class="mt-2 text-4xl font-bold">{{ displayValue(card.value, card.format) }}</p>
                        <p class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                    </template>
                </article>
            </div>
        </section>
    </AdminLayout>
</template>
