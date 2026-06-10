<script setup lang="ts">
import { computed, nextTick, onMounted, reactive, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminDateTimePicker from '../../components/admin/AdminDateTimePicker.vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { buildBrandPalette } from '../../utils/brandPalette';
import { readableTextColor } from '../../utils/contrast';

type AppState = { csrfToken: string };
type CollectionPayload = {
    id: number;
    start: string | null;
    end: string | null;
    linkOneDoc: string | null;
    url: string;
    is_active: boolean;
    is_upcoming: boolean;
};

type CompanyFormPayload = {
    name: string;
    email: string;
    slug: string;
    short_description: string;
    address: string;
    npa: string;
    localite: string;
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

const ONEDOC_PREFIX = 'https://www.onedoc.ch/';
const SOURCE_OPTIONS = [
    'Réseaux sociaux',
    'Recherche web / site HUG',
    "Recommandation d'une entreprise",
    "Recommandation d'un collaborateur",
    'Contact direct des HUG / CTS',
    'Événement / présentation',
    'Bouche à oreille',
    'Autre',
] as const;
type SourceOption = typeof SOURCE_OPTIONS[number];

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

const companyId = window.location.pathname.split('/')[3];
const searchParams = new URLSearchParams(window.location.search);
const shouldCreateNewCollection = searchParams.get('newCollection') === '1';
const requestedCollectionId = Number(searchParams.get('collection')) || null;
const isCollectionMode = shouldCreateNewCollection || requestedCollectionId !== null;

function slugify(input: string): string {
    return input
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]/g, '')
        .slice(0, 20);
}

function isHexColor(value: string): boolean {
    return /^#[0-9a-fA-F]{6}$/.test(value.trim());
}

function hydrateSourceFields(
    value: string | null | undefined,
    selectedSourceOption: { value: SourceOption | '' },
    sourceOther: { value: string },
    target: { source: string },
): void {
    const normalized = value?.trim() ?? '';

    if (!normalized) {
        selectedSourceOption.value = '';
        sourceOther.value = '';
        target.source = '';
        return;
    }

    if (SOURCE_OPTIONS.includes(normalized as SourceOption) && normalized !== 'Autre') {
        selectedSourceOption.value = normalized as SourceOption;
        sourceOther.value = '';
        target.source = normalized;
        return;
    }

    selectedSourceOption.value = 'Autre';
    sourceOther.value = normalized === 'Autre' ? '' : normalized;
    target.source = sourceOther.value;
}

const form = reactive({
    name: '',
    email: '',
    slug: '',
    short_description: '',
    address: '',
    npa: '',
    localite: '',
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
    collection_linkOneDoc: ONEDOC_PREFIX,
});
const sourceColor = ref('#c81e1e');
const selectedSourceOption = ref<SourceOption | ''>('');
const sourceOther = ref('');
const logoFile = ref<File | null>(null);
const editLogoInputId = 'company-logo-upload-edit';

const errors = ref<Record<string, string[]>>({});
const submitting = ref(false);
const loading = ref(true);
const loadError = ref<string | null>(null);
const slugTouched = ref(false);
const selectedCollectionId = ref<number | null>(
    requestedCollectionId,
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
const collectionRanges = ref<CollectionPayload[]>([]);

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

watch(sourceColor, (baseColor) => {
    if (!isHexColor(baseColor)) {
        return;
    }

    const palette = buildBrandPalette(baseColor);
    form.primaryColor = palette.primaryColor;
    form.secondaryColor = palette.secondaryColor;
    form.thirdColor = palette.thirdColor;
}, { immediate: true });

watch(selectedSourceOption, (option) => {
    if (option !== 'Autre') {
        sourceOther.value = '';
    }
});

function onSlugInput(event: Event) {
    slugTouched.value = true;
    const input = event.target as HTMLInputElement;
    const sanitized = slugify(input.value);
    form.slug = sanitized;
    input.value = sanitized;
}

function firstError(field: string): string | null {
    return errors.value[field]?.[0] ?? null;
}

function toDatetimeLocal(iso: string | null | undefined): string {
    return iso ? iso.slice(0, 16) : '';
}

function formatDate(iso: string): string {
    return new Date(iso).toLocaleString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

function back(event: Event) {
    event.preventDefault();
    navigate('/admin/campagnes');
}

function onLogoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    logoFile.value = input.files?.[0] ?? null;
}

function logoFilename(path: string | null | undefined): string {
    if (!path) {
        return '';
    }

    return path.split('/').filter(Boolean).pop() ?? path;
}

function onNpaInput(event: Event) {
    const input = event.target as HTMLInputElement;
    const sanitized = input.value.replace(/\D/g, '').slice(0, 4);
    form.npa = sanitized;
    input.value = sanitized;
}

function buildFormData(payload: CompanyFormPayload): FormData {
    const formData = new FormData();

    formData.append('_method', 'PATCH');
    formData.append('name', payload.name);
    formData.append('email', payload.email);
    formData.append('slug', payload.slug);
    formData.append('short_description', payload.short_description);
    formData.append('address', payload.address);
    formData.append('npa', payload.npa);
    formData.append('localite', payload.localite);
    formData.append('telephone', payload.telephone);
    formData.append('employee_count', String(payload.employee_count));
    formData.append('allowed_email_domains', payload.allowed_email_domains);
    formData.append('source', payload.source);
    formData.append('is_public', payload.is_public ? '1' : '0');
    formData.append('trophy', payload.trophy ? '1' : '0');
    formData.append('primaryColor', payload.primaryColor);
    formData.append('secondaryColor', payload.secondaryColor);
    formData.append('thirdColor', payload.thirdColor);

    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }

    return formData;
}

function buildCollectionFormData(payload: CompanyFormPayload): FormData {
    const formData = new FormData();

    formData.append('_method', 'PATCH');
    formData.append('collection_start', payload.collection_start);
    formData.append('collection_end', payload.collection_end);
    formData.append('collection_linkOneDoc', payload.collection_linkOneDoc);

    if (selectedCollectionId.value !== null) {
        formData.append('collection_id', String(selectedCollectionId.value));
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
            form.npa = data.npa ?? '';
            form.localite = data.localite ?? '';
            form.telephone = data.telephone ?? '';
            form.employee_count = data.employee_count ?? '';
            form.allowed_email_domains = data.allowed_email_domains ?? '';
            hydrateSourceFields(data.source, selectedSourceOption, sourceOther, form);
            form.is_public = Boolean(data.is_public);
            form.trophy = Boolean(data.trophy);
            form.logo = data.logo ?? '';
            form.primaryColor = data.primaryColor ?? '#c81e1e';
            sourceColor.value = form.primaryColor;
            form.secondaryColor = data.secondaryColor ?? '#fecaca';
            form.thirdColor = data.thirdColor ?? '#1f2937';
            const collections = (data.collections ?? []) as CollectionPayload[];
            collectionRanges.value = collections;
            const collection = shouldCreateNewCollection
                ? null
                : collections.find((item) => item.id === selectedCollectionId.value) ?? null;
            selectedCollectionId.value = shouldCreateNewCollection ? null : collection?.id ?? requestedCollectionId;
            form.collection_start = toDatetimeLocal(collection?.start);
            form.collection_end = toDatetimeLocal(collection?.end);
            form.collection_linkOneDoc = collection?.linkOneDoc ?? ONEDOC_PREFIX;
            slugTouched.value = false;
            await scrollToParticipationSettings();
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

const blockedRanges = computed(() => collectionRanges.value
    .filter((collection) => collection.id !== selectedCollectionId.value)
    .filter((collection) => collection.start && collection.end)
    .map((collection) => ({
        start: collection.start as string,
        end: collection.end as string,
    })));

const editedCollection = computed(() => {
    if (!isCollectionMode || shouldCreateNewCollection || selectedCollectionId.value === null) {
        return null;
    }

    return collectionRanges.value.find((collection) => collection.id === selectedCollectionId.value) ?? null;
});

async function scrollToParticipationSettings(): Promise<void> {
    if (window.location.hash !== '#participation-settings') {
        return;
    }

    await nextTick();
    await new Promise((resolve) => window.requestAnimationFrame(() => resolve(null)));

    const scrollContainer = document.querySelector('main');

    if (scrollContainer instanceof HTMLElement) {
        scrollContainer.scrollTo({
            top: scrollContainer.scrollHeight,
            behavior: 'smooth',
        });
        return;
    }

    window.scrollTo({
        top: document.documentElement.scrollHeight,
        behavior: 'smooth',
    });
}

async function submit() {
    submitting.value = true;
    errors.value = {};

    form.source = selectedSourceOption.value === 'Autre'
        ? sourceOther.value.trim()
        : selectedSourceOption.value;
    const payload = { ...form } as CompanyFormPayload;

    try {
        const res = await fetch(`/admin/companies/${companyId}${window.location.search}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: isCollectionMode ? buildCollectionFormData(payload) : buildFormData(payload),
        });

        if (res.ok) {
            if (isCollectionMode) {
                flashMessage.value = shouldCreateNewCollection ? 'Nouvelle campagne créée.' : 'Collecte mise à jour.';
            } else {
                flashMessage.value = 'Entreprise mise à jour.';
            }
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

watch(loading, async (isLoading) => {
    if (!isLoading) {
        await scrollToParticipationSettings();
    }
});
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
                    <span>Retour</span>
                </a>
                <h1 class="text-3xl font-semibold">
                    {{ isCollectionMode ? (shouldCreateNewCollection ? `Nouvelle collecte : ${form.name}` : `Modifier la collecte : ${form.name}`) : `Modifier l’entreprise : ${form.name}` }}
                </h1>
                <div
                    v-if="editedCollection"
                    class="mt-4 rounded-lg border px-4 py-3 text-sm"
                    :class="editedCollection.is_active
                        ? 'border-martinique-200 bg-martinique-100'
                        : 'border-pampas-200 bg-pampas-100'"
                >
                    <p
                        class="mb-2 text-xs font-medium tracking-wider uppercase"
                        :class="editedCollection.is_active ? 'text-martinique-800/70' : 'text-pampas-800/65'"
                    >
                        {{ editedCollection.is_active ? 'Collecte active' : 'Collecte à venir' }}
                    </p>
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 flex-1 items-center gap-4">
                            <span
                                v-if="editedCollection.start && editedCollection.end"
                                class="shrink-0 text-sm font-medium"
                                :class="editedCollection.is_active ? 'text-martinique-950' : 'text-pampas-950'"
                            >
                                {{ formatDate(editedCollection.start) }} → {{ formatDate(editedCollection.end) }}
                            </span>
                            <a
                                v-if="editedCollection.is_active"
                                :href="editedCollection.url"
                                target="_blank"
                                class="link link-primary min-w-0 truncate text-sm font-medium"
                            >
                                <span>{{ editedCollection.url }}</span>
                            </a>
                            <a
                                v-else
                                :href="editedCollection.url"
                                target="_blank"
                                class="link link-primary min-w-0 truncate text-sm font-medium"
                            >
                                {{ editedCollection.url }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="loading" class="text-sm text-base-content/60">Chargement...</div>
            <div v-else-if="loadError" class="alert border-0 bg-red-600 text-white"><span>{{ loadError }}</span></div>

            <form v-else @submit.prevent="submit" class="space-y-6 font-cooper">
                <template v-if="!isCollectionMode">
                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Nom <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.name" type="text" class="input input-bordered w-full" required />
                        <p v-if="firstError('name')" class="mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Email <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.email" type="email" class="input input-bordered w-full" required />
                        <p v-if="firstError('email')" class="mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">URL de collecte <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.slug"
                            type="text"
                            class="input input-bordered w-full"
                            maxlength="20"
                            pattern="[A-Za-z0-9]+"
                            @input="onSlugInput"
                            required
                        />
                        <span class="mt-1 text-xs text-base-content/60">
                            URL co-brandée : /collecte/{{ form.slug || '...' }}/{token}
                        </span>
                        <p v-if="firstError('slug')" class="mt-1 text-sm text-error">{{ firstError('slug') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Téléphone</span>
                        <input v-model="form.telephone" type="tel" class="input input-bordered w-full" />
                        <p v-if="firstError('telephone')" class="mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="flex w-full flex-col gap-2">
                    <span class="label-text">Description courte</span>
                    <textarea v-model="form.short_description" class="textarea textarea-bordered w-full font-cooper" rows="2" maxlength="500"></textarea>
                    <p v-if="firstError('short_description')" class="mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                    <label class="flex w-full flex-col gap-2 md:col-span-2">
                        <span class="label-text">Adresse <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.address" type="text" class="input input-bordered w-full" maxlength="500" required />
                        <p v-if="firstError('address')" class="mt-1 text-sm text-error">{{ firstError('address') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">NPA <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.npa" type="text" class="input input-bordered w-full" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" @input="onNpaInput" required />
                        <p v-if="firstError('npa')" class="mt-1 text-sm text-error">{{ firstError('npa') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2 md:col-span-3">
                        <span class="label-text">Localité <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.localite" type="text" class="input input-bordered w-full" maxlength="100" required />
                        <p v-if="firstError('localite')" class="mt-1 text-sm text-error">{{ firstError('localite') }}</p>
                    </label>
                </section>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Nombre d'employés <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.employee_count" type="number" min="0" class="input input-bordered w-full" required />
                        <p v-if="firstError('employee_count')" class="mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Domaines email autorisés (séparés par ",") <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input v-model="form.allowed_email_domains" type="text" class="input input-bordered w-full" placeholder="domaine.com, domaine.ch" required />
                        <p v-if="firstError('allowed_email_domains')" class="mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="space-y-6">
                    <div class="flex w-full flex-col gap-2">
                        <span class="label-text">Logo de l'entreprise <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            :id="editLogoInputId"
                            type="file"
                            class="hidden"
                            accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml"
                            @change="onLogoChange"
                        />
                        <label
                            :for="editLogoInputId"
                            class="group input input-bordered flex w-full cursor-pointer items-center gap-3 px-3 text-base-content/60"
                        >
                            <span class="material-symbols-outlined shrink-0 text-base-content/70 transition-colors duration-200 ease-in-out group-hover:text-[var(--color-razzmatazz-700)]" aria-hidden="true">upload</span>
                            <span class="min-w-0 flex-1 truncate text-sm">
                                {{ logoFile?.name || logoFilename(form.logo) || 'Aucun fichier sélectionné' }}
                            </span>
                            <img
                                v-if="!logoFile && form.logo"
                                :src="form.logo"
                                alt="Aperçu du logo"
                                class="my-0.5 h-[calc(100%-0.25rem)] max-h-none w-auto max-w-24 shrink-0 self-stretch rounded object-contain"
                            />
                        </label>
                        <p class="mt-1 text-xs text-base-content/60">Formats autorisés : PNG, JPG, JPEG, WEBP, SVG. Taille maximale : 5 Mo.</p>
                        <p v-if="firstError('logo')" class="mt-1 text-sm text-error">{{ firstError('logo') }}</p>
                    </div>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Où avez-vous entendu parler de nous ?</span>
                        <div class="relative">
                            <select v-model="selectedSourceOption" class="select select-bordered w-full bg-none pr-10">
                                <option value="">Sélectionner une option</option>
                                <option v-for="option in SOURCE_OPTIONS" :key="option" :value="option">
                                    {{ option }}
                                </option>
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--color-pampas-950)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </div>
                        <input
                            v-if="selectedSourceOption === 'Autre'"
                            v-model="sourceOther"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="Précisez la source"
                        />
                        <p v-if="firstError('source')" class="mt-1 text-sm text-error">{{ firstError('source') }}</p>
                    </label>
                </section>

                <section>
                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Couleur de l'entreprise <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <div class="flex w-full">
                            <span
                                class="input input-bordered rounded-r-none border-r-0 group relative h-12 w-14 shrink-0 overflow-hidden p-0 transition-colors duration-200 ease-out"
                                :style="{ backgroundColor: sourceColor }"
                            >
                                <input v-model="sourceColor" type="color" class="absolute inset-0 h-full w-full cursor-pointer opacity-0" aria-label="Couleur de l'entreprise" />
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="pointer-events-none absolute left-1/2 top-1/2 h-5 w-5 -translate-x-1/2 -translate-y-1/2 opacity-0 transition-opacity duration-200 ease-out group-hover:opacity-100 group-focus-within:opacity-100"
                                    :style="{ color: readableTextColor(sourceColor) }"
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
                            <div class="relative w-full">
                                <input v-model="sourceColor" type="text" class="input input-bordered rounded-l-none h-12 w-full pr-20 font-cooper" placeholder="#c81e1e" maxlength="7" required />
                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center gap-1">
                                    <span class="h-4.5 w-4.5 rounded-sm border border-base-300" :style="{ backgroundColor: form.primaryColor }"></span>
                                    <span class="h-4.5 w-4.5 rounded-sm border border-base-300" :style="{ backgroundColor: form.secondaryColor }"></span>
                                    <span class="h-4.5 w-4.5 rounded-sm border border-base-300" :style="{ backgroundColor: form.thirdColor }"></span>
                                </div>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-base-content/60">
                            Les deux autres variations de couleur sont calculées automatiquement à partir de cette teinte.
                        </p>
                        <p v-if="firstError('primaryColor')" class="mt-1 text-sm text-error">{{ firstError('primaryColor') }}</p>
                        <p v-if="firstError('secondaryColor')" class="mt-1 text-sm text-error">{{ firstError('secondaryColor') }}</p>
                        <p v-if="firstError('thirdColor')" class="mt-1 text-sm text-error">{{ firstError('thirdColor') }}</p>
                    </label>
                </section>

                <section id="participation-settings" class="space-y-4 pt-3">
                    <label class="flex items-center gap-3">
                        <input
                            v-model="anonymousParticipation"
                            type="checkbox"
                            class="checkbox checked:[--input-color:var(--color-razzmatazz-700)] checked:[color:white]"
                        />
                        <span class="text-sm font-medium text-base-content/75">Participation anonyme</span>
                    </label>

                    <label
                        class="flex items-center gap-3"
                        :class="{ 'cursor-not-allowed opacity-45': !form.is_public }"
                    >
                        <input
                            v-model="form.trophy"
                            type="checkbox"
                            class="checkbox checked:[--input-color:var(--color-razzmatazz-700)] checked:[color:white]"
                            :disabled="!form.is_public"
                        />
                        <span class="text-sm font-medium text-base-content/75">Participation au Prix du Cœur</span>
                    </label>
                </section>
                </template>

                <section v-if="isCollectionMode" class="space-y-4">
                    <div class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                        <div class="flex w-full flex-col gap-2">
                            <span class="label-text">Début <span style="color: var(--color-razzmatazz-700);">*</span></span>
                            <AdminDateTimePicker
                                v-model="form.collection_start"
                                label="Choisir une date de début"
                                mode="start"
                                :paired-date-time="form.collection_end || null"
                                :blocked-ranges="blockedRanges"
                                default-time="09:00"
                            />
                            <p v-if="firstError('collection_start')" class="mt-1 text-sm text-error">{{ firstError('collection_start') }}</p>
                        </div>

                        <div class="flex w-full flex-col gap-2">
                            <span class="label-text">Fin <span style="color: var(--color-razzmatazz-700);">*</span></span>
                            <AdminDateTimePicker
                                v-model="form.collection_end"
                                label="Choisir une date de fin"
                                mode="end"
                                :disabled="!form.collection_start"
                                :min-date-time="form.collection_start || null"
                                :paired-date-time="form.collection_start || null"
                                :reference-date-time="form.collection_start || null"
                                :blocked-ranges="blockedRanges"
                                default-time="17:00"
                            />
                            <p v-if="firstError('collection_end')" class="mt-1 text-sm text-error">{{ firstError('collection_end') }}</p>
                        </div>

                        <label class="flex w-full flex-col gap-2 md:col-span-2">
                            <span class="label-text">Lien OneDoc <span style="color: var(--color-razzmatazz-700);">*</span></span>
                            <input v-model="form.collection_linkOneDoc" type="text" class="input input-bordered w-full" placeholder="https://www.onedoc.ch/..." pattern="https://(www\.)?onedoc\.ch/.*" required />
                            <p v-if="firstError('collection_linkOneDoc')" class="mt-1 text-sm text-error">{{ firstError('collection_linkOneDoc') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin/campagnes" class="btn btn-ghost font-cooper" @click="back">
                        <span>Annuler</span>
                    </a>
                    <button
                        type="submit"
                        class="btn border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] font-cooper text-white hover:border-[var(--color-razzmatazz-800)] hover:bg-[var(--color-razzmatazz-800)]"
                        :disabled="submitting"
                    >
                        <span>{{ submitting ? '...' : 'Enregistrer' }}</span>
                    </button>
                </div>
            </form>
    </div>
    </AdminLayout>
</template>
