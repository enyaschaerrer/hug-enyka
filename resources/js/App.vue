<script setup lang="ts">
import { computed, watchEffect } from 'vue';
import { useAdminRouter } from './composables/useAdminRouter';
import { useCoBrandedCollecte } from './composables/useCoBrandedCollecte';
import CollectionsPage from './pages/admin/CollectionsPage.vue';
import CompanyCreatePage from './pages/admin/CompanyCreatePage.vue';
import CompanyEditPage from './pages/admin/CompanyEditPage.vue';
import DashboardPage from './pages/admin/DashboardPage.vue';
import CookieConsentModal from './components/modals/CookieConsentModal.vue';
import CoBrandedCollectionPage from './pages/co-branded/CoBrandedCollectionPage.vue';
import CoBrandedEligibilityPage from './pages/co-branded/CoBrandedEligibilityPage.vue';
import RegistrationsPage from './pages/admin/RegistrationsPage.vue';
import TropheePage from './pages/admin/TropheePage.vue';
import AccountsPage from './pages/admin/AccountsPage.vue';

type PageTitles = {
    defaults?: { site?: string };
    admin?: {
        dashboard?: string;
        campaigns?: string;
        company_create?: string;
        company_edit?: string;
        registrations?: string;
        trophee?: string;
        accounts?: string;
    };
    cobranded?: {
        collection?: string;
        eligibility?: string;
    };
};

type AppWindowState = {
    __APP__?: {
        pageTitles?: PageTitles;
    };
};

const { currentPath } = useAdminRouter();
const { company } = useCoBrandedCollecte();
const pageTitles = ((window as unknown as AppWindowState).__APP__?.pageTitles ?? {}) as PageTitles;
const adminTitles = {
    '/admin': pageTitles.admin?.dashboard,
    '/admin/campagnes': pageTitles.admin?.campaigns,
    '/admin/companies/create': pageTitles.admin?.company_create,
    '/admin/registrations': pageTitles.admin?.registrations,
    '/admin/trophee': pageTitles.admin?.trophee,
    '/admin/comptes': pageTitles.admin?.accounts,
} as const;

const pages = {
    '/admin': DashboardPage,
    '/admin/campagnes': CollectionsPage,
    '/admin/companies/create': CompanyCreatePage,
    '/admin/registrations': RegistrationsPage,
    '/admin/trophee': TropheePage,
    '/admin/comptes': AccountsPage,
};

const currentPage = computed(() => {
    if (/^\/collecte\/[^/]+\/[^/]+\/questionnaire$/.test(currentPath.value)) {
        return CoBrandedEligibilityPage;
    }
    if (/^\/collecte\/[^/]+\/[^/]+$/.test(currentPath.value)) {
        return CoBrandedCollectionPage;
    }
    if (/^\/admin\/companies\/\d+\/edit$/.test(currentPath.value)) {
        return CompanyEditPage;
    }
    return pages[currentPath.value as keyof typeof pages] ?? null;
});

const cookieAccentColor = computed(() => {
    if (!currentPath.value.startsWith('/collecte/')) {
        return null;
    }

    return '#355755';
});

watchEffect(() => {
    if (/^\/collecte\/[^/]+\/[^/]+\/questionnaire$/.test(currentPath.value)) {
        document.title = (pageTitles.cobranded?.eligibility ?? ':company - Questionnaire d’éligibilité')
            .replace(':company', company.name || pageTitles.defaults?.site || 'Cœur d’Honneur');
        return;
    }

    if (/^\/collecte\/[^/]+\/[^/]+$/.test(currentPath.value)) {
        document.title = (pageTitles.cobranded?.collection ?? ':company - Collecte de sang')
            .replace(':company', company.name || pageTitles.defaults?.site || 'Cœur d’Honneur');
        return;
    }

    if (/^\/admin\/companies\/\d+\/edit$/.test(currentPath.value)) {
        document.title = pageTitles.admin?.company_edit ?? document.title;
        return;
    }

    document.title = adminTitles[currentPath.value as keyof typeof adminTitles] ?? document.title;
});
</script>

<template>
    <component :is="currentPage" />
    <CookieConsentModal
        v-if="!currentPath.startsWith('/admin')"
        :accent-color="cookieAccentColor"
    />
</template>
