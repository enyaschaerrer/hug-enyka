<script setup lang="ts">
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminLayout from '../../components/layout/AdminLayout.vue';

const { flashMessage } = useAdminRouter();
</script>

<template>
    <AdminLayout>
        <div v-if="flashMessage" class="alert alert-success mb-6">
            <span>{{ flashMessage }}</span>
        </div>

        <h1 class="text-2xl font-semibold">Dashboard</h1>
        <p class="mt-2 text-base-content/50">Les KPIs seront affichés ici.</p>
    </AdminLayout>
</template>
