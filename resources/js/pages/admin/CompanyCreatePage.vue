<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';

type AppState = {
    csrfToken: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

function slugify(input: string): string {
    return input
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 20);
}

const form = reactive({
    name: '',
    email: '',
    slug: '',
    short_description: '',
    address: '',
    telephone: '',
    employee_count: '' as string | number,
    allowed_email_domains: '',
    source: '',
    logo: '',
    primaryColor: '#c81e1e',
    secondaryColor: '#fecaca',
    thirdColor: '#1f2937',
});

const errors = ref<Record<string, string[]>>({});
const submitting = ref(false);
const slugTouched = ref(false);

watch(() => form.name, (next) => {
    if (!slugTouched.value) {
        form.slug = slugify(next);
    }
});

function onSlugInput() {
    slugTouched.value = true;
}

function firstError(field: string): string | null {
    return errors.value[field]?.[0] ?? null;
}

function back(event: Event) {
    event.preventDefault();
    navigate('/admin');
}

async function submit() {
    submitting.value = true;
    errors.value = {};

    const payload: Record<string, unknown> = { ...form };
    if (payload.employee_count === '') {
        payload.employee_count = null;
    }

    try {
        const res = await fetch('/admin/companies', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        if (res.ok) {
            const data = await res.json();
            flashMessage.value = data.message ?? 'Entreprise créée.';
            navigate('/admin');
            return;
        }

        if (res.status === 422) {
            const data = await res.json();
            errors.value = data.errors ?? {};
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            errors.value = { name: ['Erreur serveur. Réessaye.'] };
        }
    } catch {
        errors.value = { name: ['Erreur réseau. Réessaye.'] };
    } finally {
        submitting.value = false;
    }
}
</script>

<template>
    <main class="min-h-screen bg-base-200 px-4 py-8 text-base-content">
        <div class="mx-auto w-full max-w-3xl rounded bg-base-100 p-6 shadow">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Créer une entreprise</h1>
                <a href="/admin" @click="back" class="btn btn-ghost btn-sm">Retour</a>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <section class="grid gap-4 md:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text">Nom *</span>
                        <input
                            v-model="form.name"
                            type="text"
                            class="input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('name')" class="mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Email *</span>
                        <input
                            v-model="form.email"
                            type="email"
                            class="input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('email')" class="mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Slug URL *</span>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="input input-bordered w-full"
                            maxlength="20"
                            pattern="[A-Za-z0-9_-]+"
                            @input="onSlugInput"
                            required
                        />
                        <span class="mt-1 text-xs text-base-content/60">
                            URL co-brandée : /collecte/{{ form.slug || '...' }}/{token}
                        </span>
                        <p v-if="firstError('slug')" class="mt-1 text-sm text-error">{{ firstError('slug') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Téléphone</span>
                        <input
                            v-model="form.telephone"
                            type="tel"
                            class="input input-bordered w-full"
                        />
                        <p v-if="firstError('telephone')" class="mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="form-control w-full">
                    <span class="label-text">Description courte</span>
                    <textarea
                        v-model="form.short_description"
                        class="textarea textarea-bordered w-full"
                        rows="2"
                        maxlength="500"
                    ></textarea>
                    <p v-if="firstError('short_description')" class="mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Adresse</span>
                    <textarea
                        v-model="form.address"
                        class="textarea textarea-bordered w-full"
                        rows="2"
                        maxlength="500"
                    ></textarea>
                    <p v-if="firstError('address')" class="mt-1 text-sm text-error">{{ firstError('address') }}</p>
                </label>

                <section class="grid gap-4 md:grid-cols-3">
                    <label class="form-control w-full">
                        <span class="label-text">Nombre d'employés</span>
                        <input
                            v-model="form.employee_count"
                            type="number"
                            min="0"
                            class="input input-bordered w-full"
                        />
                        <p v-if="firstError('employee_count')" class="mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="form-control w-full md:col-span-2">
                        <span class="label-text">Domaines email autorisés</span>
                        <input
                            v-model="form.allowed_email_domains"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="rolex.com,rolex.ch"
                        />
                        <p v-if="firstError('allowed_email_domains')" class="mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="grid gap-4 md:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text">Source (référent)</span>
                        <input
                            v-model="form.source"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="Recommandation, salon, ..."
                        />
                        <p v-if="firstError('source')" class="mt-1 text-sm text-error">{{ firstError('source') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Chemin du logo</span>
                        <input
                            v-model="form.logo"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="/img/logos/exemple.png"
                        />
                        <span class="mt-1 text-xs text-base-content/60">
                            Déposer le fichier dans public/img/logos/ et renseigner ici le chemin
                        </span>
                        <p v-if="firstError('logo')" class="mt-1 text-sm text-error">{{ firstError('logo') }}</p>
                    </label>
                </section>

                <section>
                    <p class="mb-2 label-text">Couleurs co-brandées</p>
                    <div class="grid gap-4 md:grid-cols-3">
                        <label class="form-control w-full">
                            <span class="label-text-alt">Primaire</span>
                            <input
                                v-model="form.primaryColor"
                                type="color"
                                class="input input-bordered h-12 w-full p-1"
                            />
                            <p v-if="firstError('primaryColor')" class="mt-1 text-sm text-error">{{ firstError('primaryColor') }}</p>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text-alt">Secondaire</span>
                            <input
                                v-model="form.secondaryColor"
                                type="color"
                                class="input input-bordered h-12 w-full p-1"
                            />
                            <p v-if="firstError('secondaryColor')" class="mt-1 text-sm text-error">{{ firstError('secondaryColor') }}</p>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text-alt">Tertiaire</span>
                            <input
                                v-model="form.thirdColor"
                                type="color"
                                class="input input-bordered h-12 w-full p-1"
                            />
                            <p v-if="firstError('thirdColor')" class="mt-1 text-sm text-error">{{ firstError('thirdColor') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin" @click="back" class="btn btn-ghost">Annuler</a>
                    <button type="submit" class="btn btn-primary" :disabled="submitting">
                        {{ submitting ? '...' : "Créer l'entreprise" }}
                    </button>
                </div>
            </form>
        </div>
    </main>
</template>
