<script setup lang="ts">
import { reactive, ref } from 'vue';
import { contrastRatio, readableTextColor } from '../../utils/contrast';

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

const primaryColor = props.company.colors.primary ?? '#575656';
const secondaryColor = props.company.colors.secondary ?? '#575656';
const accessiblePrimaryTextColor = (contrastRatio(primaryColor, '#ffffff') ?? 0) >= 4.5 ? primaryColor : '#111827';
const codeInputTextColor = (contrastRatio(secondaryColor, '#ffffff') ?? 0) >= 4.5 ? secondaryColor : '#111827';

function darkenHexColor(hex: string, amount = 0.16): string {
    const normalized = /^#[0-9a-fA-F]{6}$/.test(hex) ? hex : '#575656';
    const channels = [1, 3, 5].map((start) => {
        const value = Number.parseInt(normalized.slice(start, start + 2), 16);
        return Math.max(0, Math.round(value * (1 - amount))).toString(16).padStart(2, '0');
    });

    return `#${channels.join('')}`;
}

const primaryHoverColor = darkenHexColor(primaryColor);

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
    <main data-theme="light" class="flex min-h-screen items-center justify-center bg-white px-4 py-10 font-cooper text-slate-900">
        <section class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-8 flex items-center justify-between gap-4">
                <img :src="'/img/logo_HUG.png'" alt="HUG" class="max-h-10 max-w-28 object-contain" />
                <img
                    v-if="company.logo"
                    :src="company.logo"
                    :alt="company.name"
                    class="max-h-10 max-w-32 object-contain"
                />
                <span v-else class="cooper-baseline text-sm font-semibold">{{ company.name }}</span>
            </div>

            <h1 class="cooper-text-baseline mb-5 text-heading-t1">Accès collecte {{ company.name }}</h1>

            <div v-if="successMessage" class="alert mb-4 border-emerald-200 bg-emerald-50 text-emerald-900">
                <span class="cooper-baseline">{{ successMessage }}</span>
            </div>

            <div
                role="tablist"
                class="tabs tabs-lift"
            >
                <label
                    class="tab h-14 gap-2 rounded-t-xl bg-white px-4 text-slate-700 transition-colors duration-200 ease-in-out"
                    :style="activeTab === 'code'
                        ? { color: primaryColor }
                        : { '--tab-border-colors': '#0000 #0000 var(--tab-border-color) #0000' }"
                    @mouseenter="($event.currentTarget as HTMLElement).style.color = primaryColor"
                    @mouseleave="($event.currentTarget as HTMLElement).style.color = activeTab === 'code' ? primaryColor : ''"
                >
                    <input
                        v-model="activeTab"
                        type="radio"
                        name="cobranded_auth_tabs"
                        value="code"
                    />
                    <span class="material-symbols-outlined shrink-0" style="font-size: 18px;" aria-hidden="true">mail</span>
                    <span class="cooper-baseline whitespace-nowrap text-sm font-semibold">Recevoir un code</span>
                </label>
                <div class="tab-content rounded-b-xl border-slate-200 bg-white p-5">
                    <form class="space-y-4" @submit.prevent="requestCode">
                        <label class="flex flex-col gap-2">
                            <span class="cooper-baseline label-text text-slate-800">Renseigne ton adresse email professionnelle</span>
                            <input
                                v-model="codeForm.email"
                                type="email"
                                class="cooper-input-baseline input input-bordered w-full bg-white font-semibold"
                                :style="{ color: codeInputTextColor }"
                                :placeholder="emailPlaceholder"
                                autocomplete="email"
                                required
                            />
                            <span v-if="firstError('email')" class="cooper-text-baseline text-sm text-error">{{ firstError('email') }}</span>
                        </label>

                        <button
                            type="submit"
                            class="btn !mt-5 h-12 w-full rounded-xl border-none transition-colors duration-200 ease-in-out"
                            :disabled="loading"
                            :style="{
                                backgroundColor: company.colors.primary ?? '#575656',
                                color: readableTextColor(company.colors.primary ?? '#575656'),
                            }"
                            @mouseenter="($event.currentTarget as HTMLElement).style.backgroundColor = primaryHoverColor"
                            @mouseleave="($event.currentTarget as HTMLElement).style.backgroundColor = primaryColor"
                        >
                            <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                            <span class="cooper-baseline">Recevoir un code</span>
                        </button>
                    </form>
                </div>

                <label
                    class="tab h-14 gap-2 rounded-t-xl bg-white px-4 text-slate-700 transition-colors duration-200 ease-in-out"
                    :style="activeTab === 'login' ? { color: primaryColor } : undefined"
                    @mouseenter="($event.currentTarget as HTMLElement).style.color = primaryColor"
                    @mouseleave="($event.currentTarget as HTMLElement).style.color = activeTab === 'login' ? primaryColor : ''"
                >
                    <input
                        v-model="activeTab"
                        type="radio"
                        name="cobranded_auth_tabs"
                        value="login"
                    />
                    <span class="material-symbols-outlined shrink-0" style="font-size: 18px;" aria-hidden="true">login</span>
                    <span class="cooper-baseline whitespace-nowrap text-sm font-semibold">Se connecter</span>
                </label>
                <div class="tab-content rounded-b-xl border-slate-200 bg-white p-5">
                    <form class="space-y-4" @submit.prevent="login">
                        <label class="flex flex-col gap-2">
                            <span class="cooper-baseline label-text text-slate-800">Email professionnel</span>
                            <input
                                v-model="loginForm.email"
                                type="email"
                                class="cooper-input-baseline input input-bordered w-full bg-white text-slate-900"
                                :placeholder="emailPlaceholder"
                                autocomplete="email"
                                required
                            />
                            <span v-if="firstError('email')" class="cooper-text-baseline text-sm text-error">{{ firstError('email') }}</span>
                        </label>

                        <label class="flex flex-col gap-2">
                            <span class="cooper-baseline label-text text-slate-800">Mot de passe</span>
                            <input
                                v-model="loginForm.password"
                                type="password"
                                class="cooper-input-baseline input input-bordered w-full bg-white text-slate-900"
                                autocomplete="current-password"
                                required
                            />
                            <span v-if="firstError('password')" class="cooper-text-baseline text-sm text-error">{{ firstError('password') }}</span>
                        </label>

                        <button
                            type="submit"
                            class="btn !mt-5 h-12 w-full rounded-xl border-none transition-colors duration-200 ease-in-out"
                            :disabled="loading"
                            :style="{
                                backgroundColor: company.colors.primary ?? '#575656',
                                color: readableTextColor(company.colors.primary ?? '#575656'),
                            }"
                            @mouseenter="($event.currentTarget as HTMLElement).style.backgroundColor = primaryHoverColor"
                            @mouseleave="($event.currentTarget as HTMLElement).style.backgroundColor = primaryColor"
                        >
                            <span v-if="loading" class="loading loading-spinner loading-sm"></span>
                            <span class="cooper-baseline">Se connecter</span>
                        </button>

                        <button
                            type="button"
                            class="cooper-baseline !mt-[-0.25rem] w-full p-0 text-center text-xs font-semibold leading-none text-slate-700 transition-colors duration-200 ease-in-out disabled:cursor-not-allowed disabled:opacity-50"
                            :disabled="loading || !loginForm.email"
                            @mouseenter="($event.currentTarget as HTMLElement).style.color = accessiblePrimaryTextColor"
                            @mouseleave="($event.currentTarget as HTMLElement).style.color = ''"
                            @click="forgotPassword"
                        >
                            Mot de passe oublié
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>
</template>
