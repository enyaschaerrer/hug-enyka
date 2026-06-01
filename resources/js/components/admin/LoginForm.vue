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
    <form @submit.prevent="submit">
        <div class="space-y-6 rounded-2xl bg-martinique-100 p-8">
            <div>
                <label class="mb-1.5 block text-body text-martinique-800">Nom d'utilisateur</label>
                <input
                    v-model="email"
                    type="email"
                    autocomplete="email"
                    required
                    placeholder="email@cts.ch"
                    class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                />
            </div>

            <div>
                <label class="mb-1.5 block text-body text-martinique-800">Mot de passe</label>
                <input
                    v-model="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                />
            </div>

            <p v-if="errors.email?.length" class="text-caption text-fuzzywuzzybrown-700">
                {{ errors.email[0] }}
            </p>
        </div>

        <div class="mt-8 flex justify-center">
            <button
                type="submit"
                :disabled="submitting"
                class="rounded-full bg-fuzzywuzzybrown-800 px-10 py-3 text-body text-white transition hover:bg-fuzzywuzzybrown-600 disabled:opacity-40"
            >
                {{ submitting ? 'Connexion...' : 'Se connecter' }}
            </button>
        </div>
    </form>
</template>
