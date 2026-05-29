<script setup lang="ts">
import { ref } from 'vue';

type AppState = { csrfToken: string };

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
    <div data-theme="light" class="font-cooper flex min-h-screen items-center justify-center bg-base-200">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <h1 class="cooper-text-baseline text-2xl font-semibold">Administration CTS</h1>
            </div>

            <div class="card bg-base-100 shadow-sm">
                <div class="card-body">
                    <form class="flex flex-col gap-5 font-cooper" @submit.prevent="submit">
                        <label class="form-control w-full">
                            <span class="cooper-baseline label-text mb-2 font-medium">Email</span>
                            <input
                                v-model="email"
                                type="email"
                                autocomplete="email"
                                class="cooper-input-baseline input input-bordered w-full font-cooper"
                                required
                            />
                        </label>

                        <label class="form-control w-full">
                            <span class="cooper-baseline label-text mb-2 font-medium">Mot de passe</span>
                            <input
                                v-model="password"
                                type="password"
                                autocomplete="current-password"
                                class="cooper-input-baseline input input-bordered w-full font-cooper"
                                required
                            />
                        </label>

                        <p v-if="errors.email?.length" class="cooper-text-baseline text-sm text-error">
                            {{ errors.email[0] }}
                        </p>

                        <button type="submit" class="btn btn-primary mt-1 w-full font-cooper" :disabled="submitting">
                            <span class="cooper-baseline">{{ submitting ? '...' : 'Connexion' }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
