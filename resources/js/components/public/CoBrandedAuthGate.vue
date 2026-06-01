<script setup lang="ts">
import { reactive, ref } from 'vue';
import { readableTextColor } from '../../utils/contrast';

type Company = {
    name: string;
    logo: string | null;
    colors: {
        primary: string | null;
        secondary: string | null;
        third: string | null;
    };
};

const props = defineProps<{
    company: Company;
    csrfToken: string;
    emailPlaceholder: string;
    accessCodeUrl: string;
    loginUrl: string;
}>();

const activeTab = ref<'code' | 'login'>('code');
const loading = ref(false);
const successMessage = ref<string | null>(null);
const errors = ref<Record<string, string[]>>({});

const codeForm = reactive({
    email: '',
});

const loginForm = reactive({
    email: '',
    password: '',
});

function firstError(field: string): string | null {
    return errors.value[field]?.[0] ?? null;
}

async function postJson(url: string, payload: Record<string, string>) {
    loading.value = true;
    errors.value = {};
    successMessage.value = null;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            if (data.reload) {
                window.location.reload();
                return;
            }

            successMessage.value = data.message ?? 'Demande envoyée.';
            return;
        }

        if (res.status === 422) {
            errors.value = data.errors ?? { email: [data.message ?? 'Les informations sont invalides.'] };
            return;
        }

        errors.value = { email: ['Erreur serveur. Réessaie.'] };
    } catch {
        errors.value = { email: ['Erreur réseau. Réessaie.'] };
    } finally {
        loading.value = false;
    }
}

function requestCode() {
    postJson(props.accessCodeUrl, { email: codeForm.email });
}

function login() {
    postJson(props.loginUrl, {
        email: loginForm.email,
        password: loginForm.password,
    });
}

function forgotPassword() {
    codeForm.email = loginForm.email;
    activeTab.value = 'code';
    requestCode();
}
</script>

<template>
    <main class="flex min-h-screen items-center justify-center bg-base-100 px-4 py-10 font-cooper">
        <section class="w-full max-w-md rounded-box border border-base-300 bg-white p-6 shadow-sm">
            <div class="mb-6 flex items-center justify-between gap-4">
                <img :src="'/img/logo_HUG.png'" alt="HUG" class="max-h-10 max-w-28 object-contain" />
                <img
                    v-if="company.logo"
                    :src="company.logo"
                    :alt="company.name"
                    class="max-h-10 max-w-32 object-contain"
                />
                <span v-else class="cooper-baseline text-sm font-semibold">{{ company.name }}</span>
            </div>

            <h1 class="cooper-text-baseline mb-2 text-heading-t1">Accès collecte {{ company.name }}</h1>

            <div role="tablist" class="tabs tabs-box mb-5 grid grid-cols-2">
                <button
                    role="tab"
                    type="button"
                    class="tab cooper-baseline text-xs sm:text-sm"
                    :class="{ 'tab-active': activeTab === 'code' }"
                    @click="activeTab = 'code'"
                >
                    Recevoir mon code d’accès
                </button>
                <button
                    role="tab"
                    type="button"
                    class="tab cooper-baseline text-xs sm:text-sm"
                    :class="{ 'tab-active': activeTab === 'login' }"
                    @click="activeTab = 'login'"
                >
                    Se connecter
                </button>
            </div>

            <div v-if="successMessage" class="alert alert-success mb-4">
                <span class="cooper-baseline">{{ successMessage }}</span>
            </div>

            <form v-if="activeTab === 'code'" class="space-y-4" @submit.prevent="requestCode">
                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline label-text">Renseigne ton adresse email professionnelle</span>
                    <input
                        v-model="codeForm.email"
                        type="email"
                        class="cooper-input-baseline input input-bordered w-full"
                        :placeholder="emailPlaceholder"
                        autocomplete="email"
                        required
                    />
                    <span v-if="firstError('email')" class="cooper-text-baseline text-sm text-error">{{ firstError('email') }}</span>
                </label>

                <button
                    type="submit"
                    class="btn w-full border-none"
                    :disabled="loading"
                    :style="{
                        backgroundColor: company.colors.primary ?? '#575656',
                        color: readableTextColor(company.colors.primary ?? '#575656'),
                    }"
                >
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span class="cooper-baseline">Recevoir mon code d’accès</span>
                </button>
            </form>

            <form v-else class="space-y-4" @submit.prevent="login">
                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline label-text">Email professionnel</span>
                    <input
                        v-model="loginForm.email"
                        type="email"
                        class="cooper-input-baseline input input-bordered w-full"
                        :placeholder="emailPlaceholder"
                        autocomplete="email"
                        required
                    />
                    <span v-if="firstError('email')" class="cooper-text-baseline text-sm text-error">{{ firstError('email') }}</span>
                </label>

                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline label-text">Mot de passe</span>
                    <input
                        v-model="loginForm.password"
                        type="password"
                        class="cooper-input-baseline input input-bordered w-full"
                        autocomplete="current-password"
                        required
                    />
                    <span v-if="firstError('password')" class="cooper-text-baseline text-sm text-error">{{ firstError('password') }}</span>
                </label>

                <button
                    type="submit"
                    class="btn w-full border-none"
                    :disabled="loading"
                    :style="{
                        backgroundColor: company.colors.primary ?? '#575656',
                        color: readableTextColor(company.colors.primary ?? '#575656'),
                    }"
                >
                    <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                    <span class="cooper-baseline">Se connecter</span>
                </button>

                <button
                    type="button"
                    class="btn btn-ghost w-full"
                    :disabled="loading || !loginForm.email"
                    @click="forgotPassword"
                >
                    <span class="cooper-baseline">Mot de passe oublié</span>
                </button>
            </form>
        </section>
    </main>
</template>
