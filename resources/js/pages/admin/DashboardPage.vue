<script setup lang="ts">
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
    <main class="min-h-screen bg-base-200 px-4 py-8 text-base-content">
        <div class="flex items-center justify-between gap-4 rounded bg-base-100 p-6 shadow">
            <div>
                <p class="text-sm text-base-content/70">Admin dashboard</p>
                <h1 class="text-2xl font-semibold">{{ appState?.auth.user?.name ?? 'Admin' }}</h1>
                <p class="text-sm text-base-content/70">{{ appState?.auth.user?.email }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="/admin/companies/create" class="btn btn-primary">Créer une entreprise</a>
                <form method="post" action="/admin/logout">
                    <input type="hidden" name="_token" :value="appState?.csrfToken" />
                    <button type="submit" class="btn btn-outline">Logout</button>
                </form>
            </div>
        </div>
    </main>
</template>
