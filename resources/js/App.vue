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

const { currentPath } = useAdminRouter();
const { company } = useCoBrandedCollecte();
const adminTitles = {
    '/admin': 'Tableau de bord - Administration CTS',
    '/admin/campagnes': 'Collectes - Administration CTS',
    '/admin/companies/create': 'Créer une collecte - Administration CTS',
    '/admin/registrations': 'Demandes de collecte - Administration CTS',
    '/admin/trophee': 'Gestion des gagnants - Administration CTS',
    '/admin/comptes': 'Gestion de comptes - Administration CTS',
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
        document.title = `${company.name || 'Cœur d’Honneur'} - Questionnaire d’éligibilité`;
        return;
    }

    if (/^\/collecte\/[^/]+\/[^/]+$/.test(currentPath.value)) {
        document.title = `${company.name || 'Cœur d’Honneur'} - Collecte de sang`;
        return;
    }

    if (/^\/admin\/companies\/\d+\/edit$/.test(currentPath.value)) {
        document.title = 'Modifier une collecte - Administration CTS';
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
