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

type KpiPayload = {
    engagement: Record<string, KpiValue & { format?: string }>;
};

const loading = ref(true);
const loadError = ref<string | null>(null);
const kpis = ref<KpiPayload | null>(null);
let refreshTimer: number | undefined;

const engagementCards = computed(() => {
    if (!kpis.value) {
        return [];
    }

    return [
        { ...kpis.value.engagement.labelledCompanies, format: 'number' },
        { ...kpis.value.engagement.companySources, format: 'number' },
        { ...kpis.value.engagement.pageVisits, format: 'number' },
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
                    class="rounded-2xl border border-base-300 bg-white p-5 shadow-sm"
                    :class="card.available ? '' : 'opacity-45 grayscale'"
                >
                    <p class="min-h-11 text-lg text-base-content/65">{{ card.label }}</p>
                    <p class="mt-2 text-4xl font-bold">{{ displayValue(card.value, card.format) }}</p>
                    <div class="mt-4 h-2 rounded-full bg-base-200">
                        <div
                            class="h-full rounded-full"
                            :class="card.tone === 'warning' ? 'bg-orange-400' : 'bg-emerald-600'"
                            :style="{ width: progressWidth(card.value) }"
                        ></div>
                    </div>
                    <p class="mt-3 text-xs text-base-content/45">{{ card.note }}</p>
                </article>
            </div>
        </section>
    </AdminLayout>
</template>
