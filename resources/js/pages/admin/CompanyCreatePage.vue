<script setup lang="ts">
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminDateTimePicker from '../../components/admin/AdminDateTimePicker.vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';
import { buildBrandPalette } from '../../utils/brandPalette';
import { readableTextColor } from '../../utils/contrast';

type PendingForm = {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    address: string | null;
    npa: string | null;
    localite: string | null;
    message: string | null;
    trophy: boolean;
};

type AppState = {
    csrfToken: string;
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
const createLogoInputId = 'company-logo-upload-create';

const pendingForms = ref<PendingForm[]>([]);
const pendingSearch = ref('');
const pendingOpen = ref(false);
const selectedFormId = ref<number | null>(null);

const filteredPending = ref<PendingForm[]>([]);
const anonymousParticipation = computed({
    get: () => !form.is_public,
    set: (value: boolean) => {
        form.is_public = !value;
        if (value) {
            form.trophy = false;
        }
    },
});

watch(pendingSearch, (query) => {
    const q = query.toLowerCase().trim();
    filteredPending.value = q
        ? pendingForms.value.filter((f) =>
            f.name.toLowerCase().includes(q) || f.email.toLowerCase().includes(q),
        )
        : pendingForms.value;
});

function openPendingDropdown() {
    filteredPending.value = pendingForms.value;
    pendingOpen.value = true;
}

function selectPendingForm(pending: PendingForm) {
    selectedFormId.value = pending.id;
    pendingSearch.value = `${pending.name} — ${pending.email}`;
    pendingOpen.value = false;
    form.name = pending.name;
    form.email = pending.email;
    form.telephone = pending.phone ?? '';
    form.address = pending.address ?? '';
    form.npa = pending.npa ?? '';
    form.localite = pending.localite ?? '';
    form.trophy = pending.trophy;
    slugTouched.value = false;
}

function clearPendingSelection() {
    selectedFormId.value = null;
    pendingSearch.value = '';
    pendingOpen.value = false;
    form.name = '';
    form.email = '';
    form.telephone = '';
    form.address = '';
    form.npa = '';
    form.localite = '';
    form.trophy = false;
    slugTouched.value = false;
}

async function fetchPendingForms() {
    try {
        const res = await fetch('/admin/api/registrations/pending', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (res.ok) {
            pendingForms.value = await res.json();
            filteredPending.value = pendingForms.value;
        }
    } catch {
        // silently ignore
    }
}

const errors = ref<Record<string, string[]>>({});
const submitting = ref(false);
const slugTouched = ref(false);

onMounted(fetchPendingForms);

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

function back(event: Event) {
    event.preventDefault();
    navigate('/admin/campagnes');
}

function onLogoChange(event: Event) {
    const input = event.target as HTMLInputElement;
    logoFile.value = input.files?.[0] ?? null;
}

function onNpaInput(event: Event) {
    const input = event.target as HTMLInputElement;
    const sanitized = input.value.replace(/\D/g, '').slice(0, 4);
    form.npa = sanitized;
    input.value = sanitized;
}

function buildFormData(payload: CompanyFormPayload): FormData {
    const formData = new FormData();

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
    formData.append('collection_start', payload.collection_start);
    formData.append('collection_end', payload.collection_end);
    formData.append('collection_linkOneDoc', payload.collection_linkOneDoc);

    if (logoFile.value) {
        formData.append('logo', logoFile.value);
    }

    return formData;
}

async function submit() {
    submitting.value = true;
    errors.value = {};

    form.source = selectedSourceOption.value === 'Autre'
        ? sourceOther.value.trim()
        : selectedSourceOption.value;
    const payload = { ...form } as CompanyFormPayload;

    try {
        const res = await fetch('/admin/companies', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: buildFormData(payload),
        });

        if (res.ok) {
            const data = await res.json();
            if (selectedFormId.value !== null) {
                await fetch(`/admin/forms/${selectedFormId.value}/treated`, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
            }
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
            <div class="mb-6">
                <a href="/admin/campagnes" @click="back" class="mb-3 inline-flex items-center gap-2 font-cooper text-sm font-medium text-base-content/45 transition-colors duration-200 ease-out hover:text-black">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M6 8L2 12L6 16" />
                        <path d="M2 12H22" />
                    </svg>
                    <span>Retour</span>
                </a>
                <h1 class="text-2xl font-semibold">Créer une campagne</h1>
            </div>

            <div v-if="pendingForms.length > 0" class="mb-6 rounded-box border border-amber-200 bg-amber-50 p-5">
                <p class="mb-3 text-sm font-semibold text-amber-900">
                    Pré-remplir depuis une inscription en attente
                </p>
                <div class="relative">
                    <div class="relative">
                        <input
                            v-model="pendingSearch"
                            type="text"
                            class="input input-bordered w-full font-cooper text-sm"
                            :class="selectedFormId !== null ? 'pr-9' : ''"
                            placeholder="Rechercher par nom ou email..."
                            autocomplete="off"
                            @focus="openPendingDropdown"
                            @input="pendingOpen = true"
                        />
                        <button
                            v-if="selectedFormId !== null"
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40 transition-colors hover:text-base-content"
                            @click="clearPendingSelection"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                    <div v-if="pendingOpen && filteredPending.length > 0" class="fixed inset-0 z-20" @mousedown="pendingOpen = false"></div>
                    <ul v-if="pendingOpen && filteredPending.length > 0" class="absolute left-0 top-[calc(100%+0.25rem)] z-30 w-full rounded-box border border-base-300 bg-white py-1 shadow-xl">
                        <li
                            v-for="pending in filteredPending"
                            :key="pending.id"
                            class="cursor-pointer px-4 py-2.5 transition-colors duration-100 hover:bg-base-200/60"
                            @mousedown.prevent="selectPendingForm(pending)"
                        >
                            <span class="text-sm font-semibold text-base-content">{{ pending.name }}</span>
                            <span class="ml-3 text-sm text-base-content/55">{{ pending.email }}</span>
                        </li>
                    </ul>
                </div>
                <p v-if="selectedFormId !== null" class="mt-2 text-xs text-amber-700">
                    Champs pré-remplis. L&#39;inscription sera archivée à la création de la campagne.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-6 font-cooper">
                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Nom <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.name"
                            type="text"
                            class="input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('name')" class="mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Email <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.email"
                            type="email"
                            class="input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('email')" class="mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Slug URL <span style="color: var(--color-razzmatazz-700);">*</span></span>
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
                        <input
                            v-model="form.telephone"
                            type="tel"
                            class="input input-bordered w-full"
                        />
                        <p v-if="firstError('telephone')" class="mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="flex w-full flex-col gap-2">
                    <span class="label-text">Description courte</span>
                    <textarea
                        v-model="form.short_description"
                        class="textarea textarea-bordered w-full font-cooper"
                        rows="2"
                        maxlength="500"
                    ></textarea>
                    <p v-if="firstError('short_description')" class="mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                    <label class="flex w-full flex-col gap-2 md:col-span-2">
                        <span class="label-text">Adresse <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.address"
                            type="text"
                            class="input input-bordered w-full"
                            maxlength="500"
                            required
                        />
                        <p v-if="firstError('address')" class="mt-1 text-sm text-error">{{ firstError('address') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">NPA <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.npa"
                            type="text"
                            class="input input-bordered w-full"
                            inputmode="numeric"
                            pattern="[0-9]{4}"
                            maxlength="4"
                            @input="onNpaInput"
                            required
                        />
                        <p v-if="firstError('npa')" class="mt-1 text-sm text-error">{{ firstError('npa') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2 md:col-span-3">
                        <span class="label-text">Localité <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.localite"
                            type="text"
                            class="input input-bordered w-full"
                            maxlength="100"
                            required
                        />
                        <p v-if="firstError('localite')" class="mt-1 text-sm text-error">{{ firstError('localite') }}</p>
                    </label>
                </section>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Nombre d'employés <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.employee_count"
                            type="number"
                            min="0"
                            class="input input-bordered w-full"
                            required
                        />
                        <p v-if="firstError('employee_count')" class="mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="flex w-full flex-col gap-2">
                        <span class="label-text">Domaines email autorisés (séparés par ",") <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            v-model="form.allowed_email_domains"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="rolex.com,rolex.ch"
                            required
                        />
                        <p v-if="firstError('allowed_email_domains')" class="mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="space-y-6">
                    <div class="flex w-full flex-col gap-2">
                        <span class="label-text">Logo de l'entreprise <span style="color: var(--color-razzmatazz-700);">*</span></span>
                        <input
                            :id="createLogoInputId"
                            type="file"
                            class="hidden"
                            accept=".png,.jpg,.jpeg,.webp,.svg,image/png,image/jpeg,image/webp,image/svg+xml"
                            @change="onLogoChange"
                        />
                        <label
                            :for="createLogoInputId"
                            class="group input input-bordered flex w-full cursor-pointer items-center gap-3 px-3 text-base-content/60"
                        >
                            <span class="material-symbols-outlined shrink-0 text-base-content/70 transition-colors duration-200 ease-in-out group-hover:text-[var(--color-razzmatazz-700)]" aria-hidden="true">upload</span>
                            <span class="min-w-0 truncate text-sm">
                                {{ logoFile?.name || 'Aucun fichier sélectionné' }}
                            </span>
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
                                <input
                                    v-model="sourceColor"
                                    type="color"
                                    class="absolute inset-0 h-full w-full cursor-pointer opacity-0"
                                    aria-label="Sélecteur de couleur de l'entreprise"
                                />
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
                                <input
                                    v-model="sourceColor"
                                    type="text"
                                    class="input input-bordered rounded-l-none h-12 w-full pr-20 font-cooper"
                                    placeholder="#c81e1e"
                                    maxlength="7"
                                    required
                                />
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

                <section class="space-y-4">
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

                <section class="space-y-4 border-t border-base-300 pt-6">
                    <div>
                        <h2 class="text-lg font-semibold">Collecte</h2>
                        <p class="mt-1 text-sm text-base-content/60">Dates de la collecte et lien de prise de rendez-vous OneDoc.</p>
                    </div>

                    <div class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                        <div class="flex w-full flex-col gap-2">
                            <span class="label-text">Début <span style="color: var(--color-razzmatazz-700);">*</span></span>
                            <AdminDateTimePicker
                                v-model="form.collection_start"
                                label="Choisir une date de début"
                                mode="start"
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
                                :reference-date-time="form.collection_start || null"
                                default-time="17:00"
                            />
                            <p v-if="firstError('collection_end')" class="mt-1 text-sm text-error">{{ firstError('collection_end') }}</p>
                        </div>

                        <label class="flex w-full flex-col gap-2 md:col-span-2">
                            <span class="label-text">Lien OneDoc <span style="color: var(--color-razzmatazz-700);">*</span></span>
                            <input
                                v-model="form.collection_linkOneDoc"
                                type="text"
                                class="input input-bordered w-full"
                                placeholder="https://www.onedoc.ch/..."
                                pattern="https://(www\.)?onedoc\.ch/.*"
                                required
                            />
                            <p v-if="firstError('collection_linkOneDoc')" class="mt-1 text-sm text-error">{{ firstError('collection_linkOneDoc') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin/campagnes" @click="back" class="btn btn-ghost font-cooper">
                        <span>Annuler</span>
                    </a>
                    <button
                        type="submit"
                        class="btn border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] font-cooper text-white hover:border-[var(--color-razzmatazz-800)] hover:bg-[var(--color-razzmatazz-800)]"
                        :disabled="submitting"
                    >
                        <span>{{ submitting ? '...' : 'Créer la campagne' }}</span>
                    </button>
                </div>
            </form>
    </div>
    </AdminLayout>
</template>
