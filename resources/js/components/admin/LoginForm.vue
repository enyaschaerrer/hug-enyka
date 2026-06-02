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
    <form class="flex flex-col gap-5" @submit.prevent="submit">
        <div>
            <label class="cooper-text-baseline mb-1.5 block text-sm font-medium text-[#2F2F36]">Email</label>
            <input
                v-model="email"
                type="email"
                autocomplete="email"
                required
                class="cooper-input-baseline w-full rounded-lg border border-[#EFE8DD] bg-white px-4 py-2.5 text-sm text-[#2F2F36] outline-none transition focus:border-[#5A002A] focus:ring-2 focus:ring-[#5A002A]/15"
            />
        </div>

        <div>
            <label class="cooper-text-baseline mb-1.5 block text-sm font-medium text-[#2F2F36]">Mot de passe</label>
            <input
                v-model="password"
                type="password"
                autocomplete="current-password"
                required
                class="cooper-input-baseline w-full rounded-lg border border-[#EFE8DD] bg-white px-4 py-2.5 text-sm text-[#2F2F36] outline-none transition focus:border-[#5A002A] focus:ring-2 focus:ring-[#5A002A]/15"
            />
        </div>

        <p v-if="errors.email?.length" class="cooper-text-baseline text-sm text-[#5A002A]">
            {{ errors.email[0] }}
        </p>

        <button
            type="submit"
            :disabled="submitting"
            class="cooper-baseline mt-2 w-full rounded-lg bg-[#5A002A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#460020] disabled:opacity-50"
        >
            {{ submitting ? 'Connexion...' : 'Se connecter' }}
        </button>
    </form>
</template>
