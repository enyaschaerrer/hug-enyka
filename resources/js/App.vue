<script setup lang="ts">
import { computed } from 'vue';
import { useAdminRouter } from './composables/useAdminRouter';
import CampagnesPage from './pages/admin/CampagnesPage.vue';
import CompanyCreatePage from './pages/admin/CompanyCreatePage.vue';
import CompanyEditPage from './pages/admin/CompanyEditPage.vue';
import DashboardPage from './pages/admin/DashboardPage.vue';
import LoginPage from './pages/admin/LoginPage.vue';
import CookieConsentModal from './components/modals/CookieConsentModal.vue';
import CoBrandedCollectePage from './pages/public/CoBrandedCollectePage.vue';
import CollectePage from './pages/public/CollectePage.vue';
import ContactPage from './pages/public/ContactPage.vue';
import HomePage from './pages/public/HomePage.vue';
import LabelPage from './pages/public/LabelPage.vue';
import TrophyPage from './pages/public/TrophyPage.vue';

const { currentPath } = useAdminRouter();

const pages = {
    '/': HomePage,
    '/collecte': CollectePage,
    '/trophee': TrophyPage,
    '/label': LabelPage,
    '/contact': ContactPage,
    '/admin': DashboardPage,
    '/admin/campagnes': CampagnesPage,
    '/admin/login': LoginPage,
    '/admin/companies/create': CompanyCreatePage,
};

const currentPage = computed(() => {
    if (/^\/collecte\/[^/]+\/[^/]+$/.test(currentPath.value)) {
        return CoBrandedCollectePage;
    }
    if (/^\/admin\/companies\/\d+\/edit$/.test(currentPath.value)) {
        return CompanyEditPage;
    }
    return pages[currentPath.value as keyof typeof pages] ?? HomePage;
});
</script>

<template>
    <component :is="currentPage" />
    <CookieConsentModal v-if="!currentPath.startsWith('/admin')" />
</template>
