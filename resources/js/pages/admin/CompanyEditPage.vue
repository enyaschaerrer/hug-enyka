<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type AppState = { csrfToken: string };
type CollectionPayload = {
    start: string | null;
    end: string | null;
    linkOneDoc: string | null;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const { navigate, flashMessage } = useAdminRouter();

const companyId = window.location.pathname.split('/')[3];

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
const loading = ref(true);
const loadError = ref<string | null>(null);
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

function toDatetimeLocal(iso: string | null | undefined): string {
    return iso ? iso.slice(0, 16) : '';
}

function back(event: Event) {
    event.preventDefault();
    navigate('/admin/campagnes');
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
            form.logo = data.logo ?? '';
            form.primaryColor = data.primaryColor ?? '#c81e1e';
            form.secondaryColor = data.secondaryColor ?? '#fecaca';
            form.thirdColor = data.thirdColor ?? '#1f2937';
            const collection = (data.collections?.[0] ?? null) as CollectionPayload | null;
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

    const payload: Record<string, unknown> = { ...form };
    if (payload.employee_count === '') {
        payload.employee_count = null;
    }

    try {
        const res = await fetch(`/admin/companies/${companyId}`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

        if (res.ok) {
            flashMessage.value = 'Campagne mise à jour.';
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
            <div class="mb-6 flex items-center justify-between">
                <h1 class="cooper-text-baseline text-2xl font-semibold">Modifier la campagne</h1>
                <a href="/admin/campagnes" class="btn btn-ghost btn-sm" @click="back">
                    <span class="cooper-baseline">Retour</span>
                </a>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/60">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>

            <form v-else @submit.prevent="submit" class="space-y-6">
                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex flex-col w-full">
                        <span class="cooper-baseline label-text">Nom *</span>
                        <input v-model="form.name" type="text" class="cooper-input-baseline input input-bordered w-full" required />
                        <p v-if="firstError('name')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="flex flex-col w-full">
                        <span class="cooper-baseline label-text">Email *</span>
                        <input v-model="form.email" type="email" class="cooper-input-baseline input input-bordered w-full" required />
                        <p v-if="firstError('email')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="flex flex-col w-full">
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

                    <label class="flex flex-col w-full">
                        <span class="cooper-baseline label-text">Téléphone</span>
                        <input v-model="form.telephone" type="tel" class="cooper-input-baseline input input-bordered w-full" />
                        <p v-if="firstError('telephone')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="flex flex-col w-full">
                    <span class="cooper-baseline label-text">Description courte</span>
                    <textarea v-model="form.short_description" class="cooper-text-baseline textarea textarea-bordered w-full" rows="2" maxlength="500"></textarea>
                    <p v-if="firstError('short_description')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <label class="flex flex-col w-full">
                    <span class="cooper-baseline label-text">Adresse</span>
                    <textarea v-model="form.address" class="cooper-text-baseline textarea textarea-bordered w-full" rows="2" maxlength="500"></textarea>
                    <p v-if="firstError('address')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('address') }}</p>
                </label>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                    <label class="flex flex-col w-full">
                        <span class="cooper-baseline label-text">Nombre d'employés</span>
                        <input v-model="form.employee_count" type="number" min="0" class="cooper-input-baseline input input-bordered w-full" />
                        <p v-if="firstError('employee_count')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="flex flex-col w-full md:col-span-2">
                        <span class="cooper-baseline label-text">Domaines email autorisés</span>
                        <input v-model="form.allowed_email_domains" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="rolex.com,rolex.ch" />
                        <p v-if="firstError('allowed_email_domains')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="grid gap-x-4 gap-y-6 md:grid-cols-2">
                    <label class="flex flex-col w-full">
                        <span class="cooper-baseline label-text">Source (référent)</span>
                        <input v-model="form.source" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="Recommandation, salon, ..." />
                        <p v-if="firstError('source')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('source') }}</p>
                    </label>

                    <label class="flex flex-col w-full">
                        <span class="cooper-baseline label-text">Chemin du logo</span>
                        <input v-model="form.logo" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="/img/logos/exemple.png" />
                        <span class="cooper-text-baseline mt-1 text-xs text-base-content/60">Déposer le fichier dans public/img/logos/</span>
                        <p v-if="firstError('logo')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('logo') }}</p>
                    </label>
                </section>

                <section>
                    <p class="cooper-text-baseline mb-2 label-text">Couleurs co-brandées</p>
                    <div class="grid gap-x-4 gap-y-6 md:grid-cols-3">
                        <label class="flex flex-col w-full">
                            <span class="cooper-baseline label-text-alt">Primaire</span>
                            <div class="join w-full">
                                <input v-model="form.primaryColor" type="color" class="join-item h-12 w-14 cursor-pointer border border-base-300 p-1" aria-label="Couleur primaire" />
                                <input v-model="form.primaryColor" type="text" class="cooper-input-baseline join-item input input-bordered h-12 w-full" placeholder="#c81e1e" maxlength="7" />
                            </div>
                            <p v-if="firstError('primaryColor')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('primaryColor') }}</p>
                        </label>
                        <label class="flex flex-col w-full">
                            <span class="cooper-baseline label-text-alt">Secondaire</span>
                            <div class="join w-full">
                                <input v-model="form.secondaryColor" type="color" class="join-item h-12 w-14 cursor-pointer border border-base-300 p-1" aria-label="Couleur secondaire" />
                                <input v-model="form.secondaryColor" type="text" class="cooper-input-baseline join-item input input-bordered h-12 w-full" placeholder="#fecaca" maxlength="7" />
                            </div>
                            <p v-if="firstError('secondaryColor')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('secondaryColor') }}</p>
                        </label>
                        <label class="flex flex-col w-full">
                            <span class="cooper-baseline label-text-alt">Tertiaire</span>
                            <div class="join w-full">
                                <input v-model="form.thirdColor" type="color" class="join-item h-12 w-14 cursor-pointer border border-base-300 p-1" aria-label="Couleur tertiaire" />
                                <input v-model="form.thirdColor" type="text" class="cooper-input-baseline join-item input input-bordered h-12 w-full" placeholder="#1f2937" maxlength="7" />
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
                        <label class="flex flex-col w-full">
                            <span class="cooper-baseline label-text">Début *</span>
                            <input v-model="form.collection_start" type="datetime-local" class="cooper-input-baseline input input-bordered w-full" required />
                            <p v-if="firstError('collection_start')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_start') }}</p>
                        </label>

                        <label class="flex flex-col w-full">
                            <span class="cooper-baseline label-text">Fin *</span>
                            <input v-model="form.collection_end" type="datetime-local" class="cooper-input-baseline input input-bordered w-full" required />
                            <p v-if="firstError('collection_end')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_end') }}</p>
                        </label>

                        <label class="flex flex-col w-full md:col-span-2">
                            <span class="cooper-baseline label-text">Lien OneDoc *</span>
                            <input v-model="form.collection_linkOneDoc" type="text" class="cooper-input-baseline input input-bordered w-full" placeholder="https://..." required />
                            <p v-if="firstError('collection_linkOneDoc')" class="cooper-text-baseline mt-1 text-sm text-error">{{ firstError('collection_linkOneDoc') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin/campagnes" class="btn btn-ghost" @click="back">
                        <span class="cooper-baseline">Annuler</span>
                    </a>
                    <button type="submit" class="btn btn-primary" :disabled="submitting">
                        <span class="cooper-baseline">{{ submitting ? '...' : 'Enregistrer' }}</span>
                    </button>
                </div>
            </form>
    </div>
    </AdminLayout>
</template>
