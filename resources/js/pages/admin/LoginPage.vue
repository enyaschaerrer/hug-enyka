<script setup lang="ts">
import { ref } from 'vue';

type AppState = {
    csrfToken: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

const email = ref('');
const password = ref('');
const errors = ref<Record<string, string[]>>({});
const submitting = ref(false);

async function submit() {
    submitting.value = true;
    errors.value = {};

    try {
        const res = await fetch('/admin/login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ email: email.value, password: password.value }),
        });

        if (res.ok) {
            const data = await res.json();
            // Full reload to refresh server-injected auth.user state
            window.location.href = data.redirect ?? '/admin';
            return;
        }

        if (res.status === 422) {
            const data = await res.json();
            errors.value = data.errors ?? {};
        } else {
            errors.value = { email: ['Erreur serveur. Réessaye.'] };
        }
    } catch {
        errors.value = { email: ['Erreur réseau. Réessaye.'] };
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <main class="min-h-screen bg-base-200 px-4 py-8 text-base-content">
        <div class="mx-auto w-full max-w-sm rounded bg-base-100 p-6 shadow">
            <h1 class="mb-6 text-2xl font-semibold">Admin</h1>

            <form @submit.prevent="submit" class="space-y-4">
                <label class="form-control w-full">
                    <span class="label-text">Email</span>
                    <input
                        v-model="email"
                        type="email"
                        autocomplete="email"
                        class="input input-bordered w-full"
                        required
                    />
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Password</span>
                    <input
                        v-model="password"
                        type="password"
                        autocomplete="current-password"
                        class="input input-bordered w-full"
                        required
                    />
                </label>

                <p v-if="errors.email?.length" class="text-sm text-error">
                    {{ errors.email[0] }}
                </p>

                <button type="submit" class="btn btn-primary w-full" :disabled="submitting">
                    {{ submitting ? '...' : 'Login' }}
                </button>
            </form>
        </div>
    </main>
</template>
