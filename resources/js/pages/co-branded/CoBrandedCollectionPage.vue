<script setup lang="ts">
import { computed, ref } from 'vue';
import CoBrandedHeader from '../../components/public/CoBrandedHeader.vue';
import CoBrandedAuthGate from '../../components/public/CoBrandedAuthGate.vue';
import CoBrandedTabs from '../../components/co-branded/CoBrandedTabs.vue';
import CoBrandedFooter from '../../components/co-branded/CoBrandedFooter.vue';
import CoBrandedInformationTab from '../../components/co-branded/tabs/CoBrandedInformationTab.vue';
import CoBrandedStatisticsTab from '../../components/co-branded/tabs/CoBrandedStatisticsTab.vue';
import CoBrandedStepsTab from '../../components/co-branded/tabs/CoBrandedStepsTab.vue';
import CoBrandedMapTab from '../../components/co-branded/tabs/CoBrandedMapTab.vue';
import CoBrandedTestTab from '../../components/co-branded/tabs/CoBrandedTestTab.vue';

type TabKey = 'informations' | 'stats' | 'etapes' | 'map' | 'test';

type CoBrandedCollecte = {
    company: {
        name: string;
        logo: string | null;
        shortDescription: string | null;
        slug: string | null;
        colors: {
            primary: string | null;
            secondary: string | null;
            third: string | null;
        };
    };
    collection: {
        start: string | null;
        end: string | null;
        appointmentUrl: string | null;
    };
    auth: {
        canAccess: boolean;
        emailPlaceholder: string;
        accessCodeUrl: string;
        loginUrl: string;
        logoutUrl: string;
    };
};

type AppState = {
    csrfToken: string;
    coBrandedCollecte?: CoBrandedCollecte | null;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const coBrandedCollecte = appState?.coBrandedCollecte;

const company = coBrandedCollecte?.company ?? {
    name: 'Entreprise',
    logo: null,
    shortDescription: null,
    slug: null,
    colors: { primary: null, secondary: null, third: null },
};
const collection = coBrandedCollecte?.collection ?? {
    start: null,
    end: null,
    appointmentUrl: null,
};
const auth = coBrandedCollecte?.auth ?? {
    canAccess: false,
    emailPlaceholder: 'exemple@entreprise.ch',
    accessCodeUrl: '',
    loginUrl: '',
    logoutUrl: '',
};

const tabs: { key: TabKey; label: string }[] = [
    { key: 'informations', label: 'Informations' },
    { key: 'stats',        label: 'Statistiques' },
    { key: 'etapes',       label: 'Étapes du don' },
    { key: 'map',          label: 'Map des voyages' },
    { key: 'test',         label: 'Passer le test' },
];

const activeTab = ref<TabKey>('informations');

function goToTab(key: TabKey) {
    activeTab.value = key;
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function next() {
    const idx = tabs.findIndex(t => t.key === activeTab.value);
    if (idx < tabs.length - 1) goToTab(tabs[idx + 1].key);
}

function prev() {
    const idx = tabs.findIndex(t => t.key === activeTab.value);
    if (idx > 0) goToTab(tabs[idx - 1].key);
}

const canPrev = computed(() => activeTab.value !== tabs[0].key);
const canNext = computed(() => activeTab.value !== tabs[tabs.length - 1].key);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-white">
        <CoBrandedAuthGate
            v-if="!auth.canAccess"
            :company="company"
            :csrf-token="csrfToken"
            :email-placeholder="auth.emailPlaceholder"
            :access-code-url="auth.accessCodeUrl"
            :login-url="auth.loginUrl"
        />

        <template v-else>
            <CoBrandedHeader
                :company="company"
                :csrf-token="csrfToken"
                :logout-url="auth.logoutUrl"
            />

            <CoBrandedTabs
                :tabs="tabs"
                :active="activeTab"
                :primary-color="company.colors.primary"
                :secondary-color="company.colors.secondary"
                @change="goToTab"
            />

            <main class="flex-1">
                <CoBrandedInformationTab
                    v-if="activeTab === 'informations'"
                    :company-name="company.name"
                    :collection="collection"
                    :colors="company.colors"
                    @go-to-test="goToTab('test')"
                />
                <CoBrandedStatisticsTab v-else-if="activeTab === 'stats'" />
                <CoBrandedStepsTab      v-else-if="activeTab === 'etapes'" />
                <CoBrandedMapTab        v-else-if="activeTab === 'map'" />
                <CoBrandedTestTab       v-else-if="activeTab === 'test'" />
            </main>

            <!-- Précédent / Suivant -->
            <div class="mx-auto flex w-full max-w-5xl items-center justify-between gap-4 px-6 pb-8">
                <button
                    v-if="canPrev"
                    type="button"
                    class="rounded-2xl bg-catskillwhite-800 px-6 py-3 text-body font-semibold text-white transition hover:bg-catskillwhite-900"
                    @click="prev"
                >
                    ← Précédent
                </button>
                <span v-else></span>

                <button
                    v-if="canNext"
                    type="button"
                    class="rounded-2xl bg-catskillwhite-800 px-6 py-3 text-body font-semibold text-white transition hover:bg-catskillwhite-900"
                    @click="next"
                >
                    Suivant →
                </button>
                <span v-else></span>
            </div>

            <CoBrandedFooter />
        </template>
    </div>
</template>
