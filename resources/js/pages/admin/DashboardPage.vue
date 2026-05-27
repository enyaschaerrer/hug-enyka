<script setup lang="ts">
import { useAdminRouter } from '../../composables/useAdminRouter';

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
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

function goToCreate(event: Event) {
    event.preventDefault();
    flashMessage.value = null;
    navigate('/admin/companies/create');
}

async function logout() {
    try {
        const res = await fetch('/admin/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = res.ok ? await res.json() : null;
        window.location.href = data?.redirect ?? '/admin/login';
    } catch {
        window.location.href = '/admin/login';
    }
}
</script>

<template>
    <main class="min-h-screen space-y-4 bg-base-200 px-4 py-8 text-base-content">
        <div v-if="flashMessage" class="alert alert-success">
            <span>{{ flashMessage }}</span>
        </div>

        <div class="flex items-center justify-between gap-4 rounded bg-base-100 p-6 shadow">
            <div>
                <p class="text-sm text-base-content/70">Admin dashboard</p>
                <h1 class="text-2xl font-semibold">{{ appState?.auth.user?.name ?? 'Admin' }}</h1>
                <p class="text-sm text-base-content/70">{{ appState?.auth.user?.email }}</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="/admin/companies/create" @click="goToCreate" class="btn btn-primary">
                    Créer une entreprise
                </a>
                <button type="button" class="btn btn-outline" @click="logout">Logout</button>
            </div>
        </div>
    </main>
</template>
