<script setup lang="ts">
import { reactive, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { readableTextColor } from '../../utils/contrast';

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
    collection_start: '',
    collection_end: '',
    collection_linkOneDoc: '',
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
    navigate('/admin/campagnes');
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
            flashMessage.value = data.message ?? 'Campagne créée.';
            navigate('/admin/campagnes');
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
    <AdminLayout>
    <div class="mx-auto w-full max-w-3xl">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="cooper-text-baseline text-2xl font-semibold">Créer une campagne</h1>
                <a href="/admin/campagnes" @click="back" class="btn btn-ghost btn-sm font-cooper">
                    <span class="cooper-baseline">Retour</span>
                </a>
            </div>

            <form @submit.prevent="submit" class="space-y-6 font-cooper">
                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Nom *</span>
                        <input
                            v-model="form.name"
                            type="text"
                            class="cooper-input-baseline input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('name')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Email *</span>
                        <input
                            v-model="form.email"
                            type="email"
                            class="cooper-input-baseline input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('email')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Slug URL *</span>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="cooper-input-baseline input input-bordered w-full"
                            maxlength="20"
                            pattern="[A-Za-z0-9_-]+"
                            @input="onSlugInput"
                            required
                        />
                        <span class="cooper-text-baseline mt-1 text-xs text-base-content/60">
                            URL co-brandée : /collecte/{{ form.slug || '...' }}/{token}
                        </span>
                        <p v-if="firstError('slug')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('slug') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Téléphone</span>
                        <input
                            v-model="form.telephone"
                            type="tel"
                            class="cooper-input-baseline input input-bordered w-full"
                        />
                        <p v-if="firstError('telephone')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="flex w-full flex-col gap-2">
                    <span class="cooper-baseline label-text">Description courte</span>
                    <textarea
                        v-model="form.short_description"
                        class="cooper-textarea-baseline textarea textarea-bordered w-full font-cooper"
                        rows="2"
                        maxlength="500"
                    ></textarea>
                    <p v-if="firstError('short_description')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <label class="flex w-full flex-col gap-2">
                    <span class="cooper-baseline label-text">Adresse</span>
                    <textarea
                        v-model="form.address"
                        class="cooper-textarea-baseline textarea textarea-bordered w-full font-cooper"
                        rows="2"
                        maxlength="500"
                    ></textarea>
                    <p v-if="firstError('address')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('address') }}</p>
                </label>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Nombre d'employés</span>
                        <input
                            v-model="form.employee_count"
                            type="number"
                            min="0"
                            class="cooper-input-baseline input input-bordered w-full"
                        />
                        <p v-if="firstError('employee_count')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2 md:col-span-2">
                        <span class="cooper-baseline label-text">Domaines email autorisés</span>
                        <input
                            v-model="form.allowed_email_domains"
                            type="text"
                            class="cooper-input-baseline input input-bordered w-full"
                            placeholder="rolex.com,rolex.ch"
                        />
                        <p v-if="firstError('allowed_email_domains')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Source (référent)</span>
                        <input
                            v-model="form.source"
                            type="text"
                            class="cooper-input-baseline input input-bordered w-full"
                            placeholder="Recommandation, salon, ..."
                        />
                        <p v-if="firstError('source')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('source') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Chemin du logo</span>
                        <input
                            v-model="form.logo"
                            type="text"
                            class="cooper-input-baseline input input-bordered w-full"
                            placeholder="/img/logos/exemple.png"
                        />
                        <span class="cooper-text-baseline mt-1 text-xs text-base-content/60">
                            Déposer le fichier dans public/img/logos/ et renseigner ici le chemin
                        </span>
                        <p v-if="firstError('logo')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('logo') }}</p>
                    </label>
                </section>

                <section>
                    <p class="cooper-text-baseline mb-3 label-text">Couleurs co-brandées</p>
                    <div class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                        <label class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text-alt">Primaire</span>
                            <div class="join w-full">
                                <span
                                    class="join-item input input-bordered group relative h-12 w-14 overflow-hidden p-0 transition-colors duration-200 ease-out"
                                    :style="{ backgroundColor: form.primaryColor }"
                                >
                                    <input
                                        v-model="form.primaryColor"
                                        type="color"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                        aria-label="Sélecteur de couleur primaire"
                                    />
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="pointer-events-none absolute left-1/2 top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 opacity-0 transition-opacity duration-200 ease-out group-hover:opacity-100 group-focus-within:opacity-100"
                                        :style="{ color: readableTextColor(form.primaryColor) }"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="m12 9-8.414 8.414A2 2 0 0 0 3 18.828v1.344a2 2 0 0 1-.586 1.414A2 2 0 0 1 3.828 21h1.344a2 2 0 0 0 1.414-.586L15 12" />
                                        <path d="m18 9 .4.4a1 1 0 1 1-3 3l-3.8-3.8a1 1 0 1 1 3-3l.4.4 3.4-3.4a1 1 0 1 1 3 3z" />
                                        <path d="m2 22 .414-.414" />
                                    </svg>
                                </span>
                                <input
                                    v-model="form.primaryColor"
                                    type="text"
                                    class="cooper-input-baseline join-item input input-bordered h-12 w-full font-cooper"
                                    placeholder="#c81e1e"
                                    maxlength="7"
                                />
                            </div>
                            <p v-if="firstError('primaryColor')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('primaryColor') }}</p>
                        </label>
                        <label class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text-alt">Secondaire</span>
                            <div class="join w-full">
                                <span
                                    class="join-item input input-bordered group relative h-12 w-14 overflow-hidden p-0 transition-colors duration-200 ease-out"
                                    :style="{ backgroundColor: form.secondaryColor }"
                                >
                                    <input
                                        v-model="form.secondaryColor"
                                        type="color"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                        aria-label="Sélecteur de couleur secondaire"
                                    />
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="pointer-events-none absolute left-1/2 top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 opacity-0 transition-opacity duration-200 ease-out group-hover:opacity-100 group-focus-within:opacity-100"
                                        :style="{ color: readableTextColor(form.secondaryColor) }"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="m12 9-8.414 8.414A2 2 0 0 0 3 18.828v1.344a2 2 0 0 1-.586 1.414A2 2 0 0 1 3.828 21h1.344a2 2 0 0 0 1.414-.586L15 12" />
                                        <path d="m18 9 .4.4a1 1 0 1 1-3 3l-3.8-3.8a1 1 0 1 1 3-3l.4.4 3.4-3.4a1 1 0 1 1 3 3z" />
                                        <path d="m2 22 .414-.414" />
                                    </svg>
                                </span>
                                <input
                                    v-model="form.secondaryColor"
                                    type="text"
                                    class="cooper-input-baseline join-item input input-bordered h-12 w-full font-cooper"
                                    placeholder="#fecaca"
                                    maxlength="7"
                                />
                            </div>
                            <p v-if="firstError('secondaryColor')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('secondaryColor') }}</p>
                        </label>
                        <label class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text-alt">Tertiaire</span>
                            <div class="join w-full">
                                <span
                                    class="join-item input input-bordered group relative h-12 w-14 overflow-hidden p-0 transition-colors duration-200 ease-out"
                                    :style="{ backgroundColor: form.thirdColor }"
                                >
                                    <input
                                        v-model="form.thirdColor"
                                        type="color"
                                        class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                        aria-label="Sélecteur de couleur tertiaire"
                                    />
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="pointer-events-none absolute left-1/2 top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 opacity-0 transition-opacity duration-200 ease-out group-hover:opacity-100 group-focus-within:opacity-100"
                                        :style="{ color: readableTextColor(form.thirdColor) }"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="m12 9-8.414 8.414A2 2 0 0 0 3 18.828v1.344a2 2 0 0 1-.586 1.414A2 2 0 0 1 3.828 21h1.344a2 2 0 0 0 1.414-.586L15 12" />
                                        <path d="m18 9 .4.4a1 1 0 1 1-3 3l-3.8-3.8a1 1 0 1 1 3-3l.4.4 3.4-3.4a1 1 0 1 1 3 3z" />
                                        <path d="m2 22 .414-.414" />
                                    </svg>
                                </span>
                                <input
                                    v-model="form.thirdColor"
                                    type="text"
                                    class="cooper-input-baseline join-item input input-bordered h-12 w-full font-cooper"
                                    placeholder="#1f2937"
                                    maxlength="7"
                                />
                            </div>
                            <p v-if="firstError('thirdColor')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('thirdColor') }}</p>
                        </label>
                    </div>
                </section>

                <section class="space-y-4 border-t border-base-300 pt-6">
                    <div>
                        <h2 class="cooper-text-baseline text-lg font-semibold">Collecte</h2>
                        <p class="cooper-text-baseline mt-1 text-sm text-base-content/60">Dates de la collecte et lien de prise de rendez-vous OneDoc.</p>
                    </div>

                    <div class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                        <label class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text">Début *</span>
                            <input
                                v-model="form.collection_start"
                                type="datetime-local"
                                class="cooper-datetime-baseline input input-bordered w-full"
                                required
                            />
                            <p v-if="firstError('collection_start')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_start') }}</p>
                        </label>

                        <label class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text">Fin *</span>
                            <input
                                v-model="form.collection_end"
                                type="datetime-local"
                                class="cooper-datetime-baseline input input-bordered w-full"
                                required
                            />
                            <p v-if="firstError('collection_end')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_end') }}</p>
                        </label>

                        <label class="flex w-full flex-col gap-2 md:col-span-2">
                            <span class="cooper-baseline label-text">Lien OneDoc *</span>
                            <input
                                v-model="form.collection_linkOneDoc"
                                type="text"
                                class="cooper-input-baseline input input-bordered w-full"
                                placeholder="https://..."
                                required
                            />
                            <p v-if="firstError('collection_linkOneDoc')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_linkOneDoc') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin/campagnes" @click="back" class="btn btn-ghost font-cooper">
                        <span class="cooper-baseline">Annuler</span>
                    </a>
                    <button type="submit" class="btn btn-primary font-cooper" :disabled="submitting">
                        <span class="cooper-baseline">{{ submitting ? '...' : 'Créer la campagne' }}</span>
                    </button>
                </div>
            </form>
    </div>
    </AdminLayout>
</template>
