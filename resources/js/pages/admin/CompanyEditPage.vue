<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminDateTimePicker from '../../components/admin/AdminDateTimePicker.vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { readableTextColor } from '../../utils/contrast';

type AppState = { csrfToken: string };
type CollectionPayload = {
    id: number;
    start: string | null;
    end: string | null;
    linkOneDoc: string | null;
};

type CompanyFormPayload = {
    name: string;
    email: string;
    slug: string;
    short_description: string;
    address: string;
    telephone: string;
    employee_count: string | number;
    allowed_email_domains: string;
    source: string;
    is_public: boolean;
    trophy: boolean;
    primaryColor: string;
    secondaryColor: string;
    thirdColor: string;
    collection_start: string;
    collection_end: string;
    collection_linkOneDoc: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

const companyId = window.location.pathname.split('/')[3];
const searchParams = new URLSearchParams(window.location.search);
const shouldCreateNewCollection = searchParams.get('newCollection') === '1';

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
    is_public: true,
    trophy: false,
    logo: '',
    primaryColor: '#c81e1e',
    secondaryColor: '#fecaca',
    thirdColor: '#1f2937',
    collection_start: '',
    collection_end: '',
    collection_linkOneDoc: '',
});
const logoFile = ref<File | null>(null);
const editLogoInputId = 'company-logo-upload-edit';

const errors = ref<Record<string, string[]>>({});
const submitting = ref(false);
const loading = ref(true);
const loadError = ref<string | null>(null);
const slugTouched = ref(false);
const selectedCollectionId = ref<number | null>(
    Number(searchParams.get('collection')) || null,
);
const anonymousParticipation = computed({
    get: () => !form.is_public,
    set: (value: boolean) => {
        form.is_public = !value;
        if (value) {
            form.trophy = false;
        }
    },
});

watch(() => form.name, (next) => {
    if (!slugTouched.value) {
        form.slug = slugify(next);
    }
});

watch(() => form.is_public, (isPublic) => {
    if (!isPublic) {
        form.trophy = false;
    }
});

function onSlugInput() {
    slugTouched.value = true;
}

function firstError(field: string): string | null {
    return errors.value[field]?.[0] ?? null;
}

function toDatetimeLocal(iso: string | null | undefined): string {
    return iso ? iso.slice(0, 16) : '';
}

function back(event: Event) {
    event.preventDefault();
    navigate('/admin/campagnes');
}

function onLogoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    logoFile.value = input.files?.[0] ?? null;
}

function buildFormData(payload: CompanyFormPayload): FormData {
    const formData = new FormData();

    formData.append('_method', 'PATCH');
    formData.append('name', payload.name);
    formData.append('email', payload.email);
    formData.append('slug', payload.slug);
    formData.append('short_description', payload.short_description);
    formData.append('address', payload.address);
    formData.append('telephone', payload.telephone);
    formData.append('employee_count', String(payload.employee_count));
    formData.append('allowed_email_domains', payload.allowed_email_domains);
    formData.append('source', payload.source);
    formData.append('is_public', payload.is_public ? '1' : '0');
    formData.append('trophy', payload.trophy ? '1' : '0');
    formData.append('primaryColor', payload.primaryColor);
    formData.append('secondaryColor', payload.secondaryColor);
    formData.append('thirdColor', payload.thirdColor);
    formData.append('collection_start', payload.collection_start);
    formData.append('collection_end', payload.collection_end);
    formData.append('collection_linkOneDoc', payload.collection_linkOneDoc);

    if (selectedCollectionId.value !== null) {
        formData.append('collection_id', String(selectedCollectionId.value));
    }

    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }

    return formData;
}

async function fetchCompany() {
    try {
        const res = await fetch(`/admin/api/companies/${companyId}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
            const data = await res.json();
            form.name = data.name ?? '';
            form.email = data.email ?? '';
            form.slug = data.slug ?? '';
            form.short_description = data.short_description ?? '';
            form.address = data.address ?? '';
            form.telephone = data.telephone ?? '';
            form.employee_count = data.employee_count ?? '';
            form.allowed_email_domains = data.allowed_email_domains ?? '';
            form.source = data.source ?? '';
            form.is_public = data.is_public !== false;
            form.trophy = Boolean(data.trophy);
            form.logo = data.logo ?? '';
            form.primaryColor = data.primaryColor ?? '#c81e1e';
            form.secondaryColor = data.secondaryColor ?? '#fecaca';
            form.thirdColor = data.thirdColor ?? '#1f2937';
            const collections = (data.collections ?? []) as CollectionPayload[];
            const collection = shouldCreateNewCollection
                ? null
                : collections.find((item) => item.id === selectedCollectionId.value) ?? collections[0] ?? null;
            selectedCollectionId.value = shouldCreateNewCollection ? null : collection?.id ?? null;
            form.collection_start = toDatetimeLocal(collection?.start);
            form.collection_end = toDatetimeLocal(collection?.end);
            form.collection_linkOneDoc = collection?.linkOneDoc ?? '';
            slugTouched.value = true;
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Campagne introuvable.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loading.value = false;
    }
}

async function submit() {
    submitting.value = true;
    errors.value = {};

    const payload = { ...form } as CompanyFormPayload;

    try {
        const res = await fetch(`/admin/companies/${companyId}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: buildFormData(payload),
        });

        if (res.ok) {
            flashMessage.value = shouldCreateNewCollection ? 'Nouvelle campagne créée.' : 'Campagne mise à jour.';
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

onMounted(fetchCompany);
</script>

<template>
    <AdminLayout>
    <div class="mx-auto w-full max-w-3xl">
            <div class="mb-6">
                <a href="/admin/campagnes" class="mb-3 inline-flex items-center gap-2 font-cooper text-sm font-medium text-base-content/45 transition-colors duration-200 ease-out hover:text-black" @click="back">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 8L2 12L6 16" />
                        <path d="M2 12H22" />
                    </svg>
                    <span class="cooper-baseline">Retour</span>
                </a>
                <h1 class="cooper-text-baseline text-2xl font-semibold">
                    {{ shouldCreateNewCollection ? 'Nouvelle campagne' : 'Modifier la campagne' }}
                </h1>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/60">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>

            <form v-else @submit.prevent="submit" class="space-y-6 font-cooper">
                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Nom <span style="color: #9B2F5C;">*</span></span>
                        <input v-model="form.name" type="text" class="cooper-input-baseline input input-bordered w-full" required />
                        <p v-if="firstError('name')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Email <span style="color: #9B2F5C;">*</span></span>
                        <input v-model="form.email" type="email" class="cooper-input-baseline input input-bordered w-full" required />
                        <p v-if="firstError('email')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Slug URL <span style="color: #9B2F5C;">*</span></span>
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
                        <input v-model="form.telephone" type="tel" class="cooper-input-baseline input input-bordered w-full" />
                        <p v-if="firstError('telephone')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="flex w-full flex-col gap-2">
                    <span class="cooper-baseline label-text">Description courte</span>
                    <textarea v-model="form.short_description" class="cooper-textarea-baseline textarea textarea-bordered w-full font-cooper" rows="2" maxlength="500"></textarea>
                    <p v-if="firstError('short_description')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <label class="flex w-full flex-col gap-2">
                    <span class="cooper-baseline label-text">Adresse <span style="color: #9B2F5C;">*</span></span>
                    <textarea v-model="form.address" class="cooper-textarea-baseline textarea textarea-bordered w-full font-cooper" rows="2" maxlength="500" required></textarea>
                    <p v-if="firstError('address')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('address') }}</p>
                </label>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Nombre d'employés <span style="color: #9B2F5C;">*</span></span>
                        <input v-model="form.employee_count" type="number" min="0" class="cooper-input-baseline input input-bordered w-full" required />
                        <p v-if="firstError('employee_count')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2 md:col-span-2">
                        <span class="cooper-baseline label-text">Domaines email autorisés (séparés par ",") <span style="color: #9B2F5C;">*</span></span>
                        <input v-model="form.allowed_email_domains" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="rolex.com,rolex.ch" required />
                        <p v-if="firstError('allowed_email_domains')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Où avez-vous entendu parler de nous ?</span>
                        <input v-model="form.source" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="Recommandation, salon, ..." />
                        <p v-if="firstError('source')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('source') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="cooper-baseline label-text">Logo de l'entreprise</span>
                        <input
                            :id="editLogoInputId"
                            type="file"
                            class="hidden"
                            accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml"
                            @change="onLogoChange"
                        />
                        <label
                            :for="editLogoInputId"
                            class="group cooper-input-baseline input input-bordered flex w-full cursor-pointer items-center gap-3 px-3 text-base-content/60"
                        >
                            <span class="material-symbols-outlined shrink-0 text-base-content/70 transition-colors duration-200 ease-in-out group-hover:text-primary" aria-hidden="true">upload</span>
                            <span class="cooper-baseline min-w-0 truncate text-sm">
                                {{ logoFile?.name || 'Aucun fichier sélectionné' }}
                            </span>
                        </label>
                        <p v-if="firstError('logo')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('logo') }}</p>
                    </label>
                </section>

                <section>
                    <p class="cooper-text-baseline mb-3 label-text">Couleurs co-brandées <span style="color: #9B2F5C;">*</span></p>
                    <div class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                        <label class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text-alt">Primaire</span>
                            <div class="join w-full">
                                <span
                                    class="join-item input input-bordered group relative h-12 w-14 overflow-hidden p-0 transition-colors duration-200 ease-out"
                                    :style="{ backgroundColor: form.primaryColor }"
                                >
                                    <input v-model="form.primaryColor" type="color" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="Couleur primaire" />
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
                                <input v-model="form.primaryColor" type="text" class="cooper-input-baseline join-item input input-bordered h-12 w-full font-cooper" placeholder="#c81e1e" maxlength="7" required />
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
                                    <input v-model="form.secondaryColor" type="color" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="Couleur secondaire" />
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
                                <input v-model="form.secondaryColor" type="text" class="cooper-input-baseline join-item input input-bordered h-12 w-full font-cooper" placeholder="#fecaca" maxlength="7" required />
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
                                    <input v-model="form.thirdColor" type="color" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="Couleur tertiaire" />
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
                                <input v-model="form.thirdColor" type="text" class="cooper-input-baseline join-item input input-bordered h-12 w-full font-cooper" placeholder="#1f2937" maxlength="7" required />
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
                        <div class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text">Début <span style="color: #9B2F5C;">*</span></span>
                            <AdminDateTimePicker
                                v-model="form.collection_start"
                                label="Choisir une date de début"
                                mode="start"
                                default-time="09:00"
                            />
                            <p v-if="firstError('collection_start')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_start') }}</p>
                        </div>

                        <div class="flex w-full flex-col gap-2">
                            <span class="cooper-baseline label-text">Fin <span style="color: #9B2F5C;">*</span></span>
                            <AdminDateTimePicker
                                v-model="form.collection_end"
                                label="Choisir une date de fin"
                                mode="end"
                                :min-date-time="form.collection_start || null"
                                :reference-date-time="form.collection_start || null"
                                default-time="17:00"
                            />
                            <p v-if="firstError('collection_end')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_end') }}</p>
                        </div>

                        <label class="flex items-center gap-3 md:col-span-2">
                            <input
                                v-model="anonymousParticipation"
                                type="checkbox"
                                class="checkbox checked:[--input-color:var(--color-primary)] checked:[color:var(--color-primary-content)]"
                            />
                            <span class="cooper-baseline text-sm font-medium text-base-content/75">Participation anonyme</span>
                        </label>

                        <label
                            class="flex items-center gap-3 md:col-span-2"
                            :class="{ 'cursor-not-allowed opacity-45': !form.is_public }"
                        >
                            <input
                                v-model="form.trophy"
                                type="checkbox"
                                class="checkbox checked:[--input-color:var(--color-primary)] checked:[color:var(--color-primary-content)]"
                                :disabled="!form.is_public"
                            />
                            <span class="cooper-baseline text-sm font-medium text-base-content/75">Participation au prix du cœur</span>
                        </label>

                        <label class="flex w-full flex-col gap-2 md:col-span-2">
                            <span class="cooper-baseline label-text">Lien OneDoc <span style="color: #9B2F5C;">*</span></span>
                            <input v-model="form.collection_linkOneDoc" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="https://..." required />
                            <p v-if="firstError('collection_linkOneDoc')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_linkOneDoc') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin/campagnes" class="btn btn-ghost font-cooper" @click="back">
                        <span class="cooper-baseline">Annuler</span>
                    </a>
                    <button type="submit" class="btn btn-primary font-cooper" :disabled="submitting">
                        <span class="cooper-baseline">{{ submitting ? '...' : 'Enregistrer' }}</span>
                    </button>
                </div>
            </form>
    </div>
    </AdminLayout>
</template>
