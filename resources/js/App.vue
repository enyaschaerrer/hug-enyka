<script setup lang="ts">
import { computed } from 'vue';
import PublicHome from './pages/PublicHome.vue';
import AdminDashboard from './pages/admin/AdminDashboard.vue';
import AdminLogin from './pages/admin/AdminLogin.vue';

const pages = {
    '/': PublicHome,
    '/admin': AdminDashboard,
    '/admin/login': AdminLogin,
};

const currentPage = computed(() => pages[window.location.pathname as keyof typeof pages] ?? PublicHome);
</script>

<template>
    <component :is="currentPage" />
</template>
