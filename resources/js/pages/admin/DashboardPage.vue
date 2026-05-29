<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type KpiValue = {
    label: string;
    value: number | null;
    available: boolean;
    note?: string;
    tone?: 'success' | 'warning';
};

type FunnelStep = {
    label: string;
    value: number | null;
    rate: number | null;
    available: boolean;
    note?: string;
};

type KpiPayload = {
    live: {
        activeVisitors: KpiValue;
    };
    summary: {
        registeredUsers: KpiValue;
        participationRate: KpiValue;
        donationConversionRate: KpiValue;
        labelledCompanies: KpiValue;
    };
    funnel: FunnelStep[];
    engagement: Record<string, KpiValue>;
};

const loading = ref(true);
const loadError = ref<string | null>(null);
const kpis = ref<KpiPayload | null>(null);
let refreshTimer: number | undefined;

const summaryCards = computed(() => {
    if (!kpis.value) {
        return [];
    }

    return [
        { ...kpis.value.summary.registeredUsers, format: 'number' },
        { ...kpis.value.summary.participationRate, format: 'percent' },
        { ...kpis.value.summary.donationConversionRate, format: 'percent' },
        { ...kpis.value.summary.labelledCompanies, format: 'number' },
    ];
});

const engagementCards = computed(() => {
    if (!kpis.value) {
        return [];
    }

    return [
        { ...kpis.value.engagement.questionnaireAbandonRate, format: 'percent' },
        { ...kpis.value.engagement.recommendedCompanies, format: 'number' },
        { ...kpis.value.engagement.qrScans, format: 'percent' },
        { ...kpis.value.engagement.mailClicks, format: 'percent' },
        { ...kpis.value.engagement.bannerUsage, format: 'percent' },
    ];
});

function displayValue(value: number | null, format: string): string {
    if (value === null) {
        return 'N/A';
    }

    return format === 'percent' ? `${value}%` : value.toLocaleString('fr-CH');
}

function barWidth(rate: number | null): string {
    if (rate === null) {
        return '100%';
    }

    return `${Math.max(6, Math.min(rate, 100))}%`;
}

function progressWidth(value: number | null): string {
    if (value === null) {
        return '0%';
    }

    return `${Math.max(4, Math.min(value, 100))}%`;
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

onMounted(() => {
    fetchKpis();
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
        <section class="min-h-full rounded-sm bg-[#FAF8F2] p-1 pr-4 text-[#1f1f22]">
            <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h1 class="cooper-text-baseline text-3xl font-semibold">Bienvenue dans votre dashboard</h1>
                    <p class="cooper-text-baseline mt-1 text-lg text-base-content/60">Campagne 2026 · vue globale CTS</p>
                </div>

                <article
                    v-if="kpis?.live.activeVisitors"
                    class="flex w-full items-center justify-between gap-4 border border-[#5a002a]/10 bg-white px-4 py-2.5 text-[#5a002a] shadow-sm sm:w-80"
                >
                    <p class="cooper-text-baseline text-sm font-medium leading-tight text-[#5a002a]/65">
                        Nombre d’utilisateurs connectés
                    </p>
                    <p class="cooper-text-baseline text-2xl font-bold leading-none">
                        {{ displayValue(kpis.live.activeVisitors.value, 'number') }}
                    </p>
                </article>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement des KPIs...</div>
            <div v-else-if="loadError" class="alert alert-error">
                <span class="cooper-baseline">{{ loadError }}</span>
            </div>

            <template v-else-if="kpis">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <article
                        v-for="card in summaryCards"
                        :key="card.label"
                        class="border-2 bg-[#f8e7ee] p-5"
                        :class="card.available ? 'border-[#5a002a] text-[#5a002a]' : 'border-[#8b7f86] text-[#8b7f86] opacity-60 grayscale'"
                    >
                        <p class="cooper-text-baseline text-xl font-medium">{{ card.label }}</p>
                        <p class="cooper-text-baseline mt-3 text-4xl font-bold leading-none">
                            {{ displayValue(card.value, card.format) }}
                        </p>
                        <p class="cooper-text-baseline mt-3 min-h-9 text-xs opacity-70">{{ card.note }}</p>
                    </article>
                </div>

                <section class="mt-6 border border-base-300 bg-white p-6">
                    <div class="mb-5 flex flex-wrap items-center gap-3">
                        <h2 class="cooper-text-baseline text-2xl font-semibold">Entonnoir de conversion</h2>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">
                            <span class="cooper-baseline">objectif : réduire le différentiel de 30%</span>
                        </span>
                    </div>

                    <div class="space-y-5">
                        <div v-for="(step, index) in kpis.funnel" :key="step.label">
                            <div
                                class="flex min-h-12 items-center justify-between rounded-lg px-5"
                                :class="[
                                    step.available ? ['bg-[#efb7cd]', index === 2 ? 'bg-[#d24c82] text-white' : 'text-[#5a002a]'] : 'bg-base-200 text-base-content/40',
                                ]"
                                :style="{ width: barWidth(step.rate) }"
                            >
                                <span class="cooper-baseline text-lg font-medium">{{ step.label }}</span>
                                <span class="flex items-baseline gap-3">
                                    <strong class="cooper-baseline text-2xl">{{ displayValue(step.value, 'number') }}</strong>
                                    <span v-if="step.rate !== null" class="cooper-baseline text-lg">{{ step.rate }}%</span>
                                </span>
                            </div>
                            <p
                                v-if="index < kpis.funnel.length - 1"
                                class="cooper-text-baseline mt-2 text-sm"
                                :class="step.available ? 'text-red-500' : 'text-base-content/40'"
                            >
                                ▼ différentiel non disponible
                            </p>
                            <p v-if="step.note" class="cooper-text-baseline mt-2 text-xs text-base-content/45">{{ step.note }}</p>
                        </div>
                    </div>
                </section>

                <section class="mt-6 pb-2">
                    <h2 class="cooper-text-baseline text-sm font-semibold uppercase tracking-wide text-base-content/60">Engagement digital</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                        <article
                            v-for="card in engagementCards"
                            :key="card.label"
                            class="rounded-2xl border border-base-300 bg-white p-5 shadow-sm"
                            :class="card.available ? '' : 'opacity-45 grayscale'"
                        >
                            <p class="cooper-text-baseline min-h-11 text-lg text-base-content/65">{{ card.label }}</p>
                            <p class="cooper-text-baseline mt-2 text-4xl font-bold">{{ displayValue(card.value, card.format) }}</p>
                            <div class="mt-4 h-2 rounded-full bg-base-200">
                                <div
                                    class="h-full rounded-full"
                                    :class="card.tone === 'warning' ? 'bg-orange-400' : 'bg-emerald-600'"
                                    :style="{ width: progressWidth(card.value) }"
                                ></div>
                            </div>
                            <p class="cooper-text-baseline mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                        </article>
                    </div>
                </section>
            </template>
        </section>
    </AdminLayout>
</template>
