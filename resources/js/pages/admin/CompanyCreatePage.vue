<script setup lang="ts">
import { ref, watch } from 'vue';

type AppState = {
    csrfToken: string;
    errors: Record<string, string[]>;
    old: Record<string, string | null | undefined>;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const errors = appState?.errors ?? {};
const old = appState?.old ?? {};

function slugify(input: string): string {
    return input
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .slice(0, 20);
}

const name = ref<string>(old.name ?? '');
const slug = ref<string>(old.slug ?? '');
const slugTouched = ref<boolean>(Boolean(old.slug));

watch(name, (next) => {
    if (!slugTouched.value) {
        slug.value = slugify(next);
    }
});

function onSlugInput() {
    slugTouched.value = true;
}

function firstError(field: string): string | null {
    return errors[field]?.[0] ?? null;
}
</script>

<template>
    <main class="min-h-screen bg-base-200 px-4 py-8 text-base-content">
        <div class="mx-auto w-full max-w-3xl rounded bg-base-100 p-6 shadow">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Créer une entreprise</h1>
                <a href="/admin" class="btn btn-ghost btn-sm">Retour</a>
            </div>

            <form method="post" action="/admin/companies" class="space-y-6">
                <input type="hidden" name="_token" :value="appState?.csrfToken" />

                <section class="grid gap-4 md:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text">Nom *</span>
                        <input
                            name="name"
                            type="text"
                            class="input input-bordered w-full"
                            v-model="name"
                            required
                        />
                        <p v-if="firstError('name')" class="mt-1 text-sm text-error">{{ firstError('name') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Email *</span>
                        <input
                            name="email"
                            type="email"
                            class="input input-bordered w-full"
                            :value="old.email ?? ''"
                            required
                        />
                        <p v-if="firstError('email')" class="mt-1 text-sm text-error">{{ firstError('email') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Slug URL *</span>
                        <input
                            name="slug"
                            type="text"
                            class="input input-bordered w-full"
                            v-model="slug"
                            maxlength="20"
                            pattern="[A-Za-z0-9_-]+"
                            @input="onSlugInput"
                            required
                        />
                        <span class="mt-1 text-xs text-base-content/60">
                            URL co-brandée : /collecte/{{ slug || '...' }}/{token}
                        </span>
                        <p v-if="firstError('slug')" class="mt-1 text-sm text-error">{{ firstError('slug') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Téléphone</span>
                        <input
                            name="telephone"
                            type="tel"
                            class="input input-bordered w-full"
                            :value="old.telephone ?? ''"
                        />
                        <p v-if="firstError('telephone')" class="mt-1 text-sm text-error">{{ firstError('telephone') }}</p>
                    </label>
                </section>

                <label class="form-control w-full">
                    <span class="label-text">Description courte</span>
                    <textarea
                        name="short_description"
                        class="textarea textarea-bordered w-full"
                        rows="2"
                        maxlength="500"
                    >{{ old.short_description ?? '' }}</textarea>
                    <p v-if="firstError('short_description')" class="mt-1 text-sm text-error">{{ firstError('short_description') }}</p>
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Adresse</span>
                    <textarea
                        name="address"
                        class="textarea textarea-bordered w-full"
                        rows="2"
                        maxlength="500"
                    >{{ old.address ?? '' }}</textarea>
                    <p v-if="firstError('address')" class="mt-1 text-sm text-error">{{ firstError('address') }}</p>
                </label>

                <section class="grid gap-4 md:grid-cols-3">
                    <label class="form-control w-full">
                        <span class="label-text">Nombre d'employés</span>
                        <input
                            name="employee_count"
                            type="number"
                            min="0"
                            class="input input-bordered w-full"
                            :value="old.employee_count ?? ''"
                        />
                        <p v-if="firstError('employee_count')" class="mt-1 text-sm text-error">{{ firstError('employee_count') }}</p>
                    </label>

                    <label class="form-control w-full md:col-span-2">
                        <span class="label-text">Domaines email autorisés</span>
                        <input
                            name="allowed_email_domains"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="rolex.com,rolex.ch"
                            :value="old.allowed_email_domains ?? ''"
                        />
                        <p v-if="firstError('allowed_email_domains')" class="mt-1 text-sm text-error">{{ firstError('allowed_email_domains') }}</p>
                    </label>
                </section>

                <section class="grid gap-4 md:grid-cols-2">
                    <label class="form-control w-full">
                        <span class="label-text">Source (référent)</span>
                        <input
                            name="source"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="Recommandation, salon, ..."
                            :value="old.source ?? ''"
                        />
                        <p v-if="firstError('source')" class="mt-1 text-sm text-error">{{ firstError('source') }}</p>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Chemin du logo</span>
                        <input
                            name="logo"
                            type="text"
                            class="input input-bordered w-full"
                            placeholder="/img/logos/exemple.png"
                            :value="old.logo ?? ''"
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
                                name="primaryColor"
                                type="color"
                                class="input input-bordered h-12 w-full p-1"
                                :value="old.primaryColor ?? '#c81e1e'"
                            />
                            <p v-if="firstError('primaryColor')" class="mt-1 text-sm text-error">{{ firstError('primaryColor') }}</p>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text-alt">Secondaire</span>
                            <input
                                name="secondaryColor"
                                type="color"
                                class="input input-bordered h-12 w-full p-1"
                                :value="old.secondaryColor ?? '#fecaca'"
                            />
                            <p v-if="firstError('secondaryColor')" class="mt-1 text-sm text-error">{{ firstError('secondaryColor') }}</p>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text-alt">Tertiaire</span>
                            <input
                                name="thirdColor"
                                type="color"
                                class="input input-bordered h-12 w-full p-1"
                                :value="old.thirdColor ?? '#1f2937'"
                            />
                            <p v-if="firstError('thirdColor')" class="mt-1 text-sm text-error">{{ firstError('thirdColor') }}</p>
                        </label>
                    </div>
                </section>

                <div class="flex justify-end gap-2 pt-4">
                    <a href="/admin" class="btn btn-ghost">Annuler</a>
                    <button type="submit" class="btn btn-primary">Créer l'entreprise</button>
                </div>
            </form>
        </div>
    </main>
</template>
