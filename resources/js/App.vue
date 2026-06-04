<script setup lang="ts">
import { computed } from 'vue';
import { useAdminRouter } from './composables/useAdminRouter';
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

const pages = {
    '/admin': DashboardPage,
    '/admin/campagnes': CollectionsPage,
    '/admin/companies/create': CompanyCreatePage,
    '/admin/registrations': RegistrationsPage,
    '/admin/trophee': TropheePage,
    '/admin/comptes': AccountsPage,
};

const currentPage = computed(() => {
    if (/^\/collecte\/[^/]+\/[^/]+\/eligibility$/.test(currentPath.value)) {
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
</script>

<template>
    <component :is="currentPage" />
    <CookieConsentModal v-if="!currentPath.startsWith('/admin')" />
</template>
