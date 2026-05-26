<script setup lang="ts">
import { computed } from 'vue';
import DashboardPage from './pages/admin/DashboardPage.vue';
import LoginPage from './pages/admin/LoginPage.vue';
import CoBrandedCollectePage from './pages/public/CoBrandedCollectePage.vue';
import CollectePage from './pages/public/CollectePage.vue';
import ContactPage from './pages/public/ContactPage.vue';
import HomePage from './pages/public/HomePage.vue';
import LabelPage from './pages/public/LabelPage.vue';
import TrophyPage from './pages/public/TrophyPage.vue';

const pages = {
    '/': HomePage,
    '/collecte': CollectePage,
    '/trophee': TrophyPage,
    '/label': LabelPage,
    '/contact': ContactPage,
    '/admin': DashboardPage,
    '/admin/login': LoginPage,
};

const isCoBrandedCollecte = /^\/collecte\/[^/]+\/[^/]+$/.test(window.location.pathname);
const currentPage = computed(() => {
    if (isCoBrandedCollecte) {
        return CoBrandedCollectePage;
    }

    return pages[window.location.pathname as keyof typeof pages] ?? HomePage;
});
</script>

<template>
    <component :is="currentPage" />
</template>
