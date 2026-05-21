<script setup lang="ts">
import AdminLayout from '../../components/layouts/AdminLayout.vue';

type AppState = {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
        } | null;
    };
    csrfToken: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
</script>

<template>
    <AdminLayout>
        <div class="flex items-center justify-between gap-4 rounded bg-base-100 p-6 shadow">
            <div>
                <p class="text-sm text-base-content/70">Admin dashboard</p>
                <h1 class="text-2xl font-semibold">{{ appState?.auth.user?.name ?? 'Admin' }}</h1>
                <p class="text-sm text-base-content/70">{{ appState?.auth.user?.email }}</p>
            </div>

            <form method="post" action="/admin/logout">
                <input type="hidden" name="_token" :value="appState?.csrfToken" />
                <button type="submit" class="btn btn-outline">Logout</button>
            </form>
        </div>
    </AdminLayout>
</template>
