<script setup lang="ts">
type CoBrandedCompany = {
    name: string;
    logo: string | null;
    colors: {
        primary: string | null;
        secondary: string | null;
        third: string | null;
    };
};

const props = defineProps<{
    company: CoBrandedCompany;
    csrfToken: string;
    logoutUrl: string;
}>();

defineEmits<{
    goHome: [];
    goTest: [];
    logout: [];
}>();
</script>

<template>
    <header class="sticky top-0 z-40 bg-catskillwhite-800 px-6 py-4 font-cooper shadow-sm lg:px-12">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4">
            <button
                type="button"
                class="flex items-center gap-3 rounded-full bg-white px-5 py-2 transition hover:bg-catskillwhite-100"
                aria-label="Retour à l'accueil"
                @click="$emit('goHome')"
            >
                <img
                    v-if="company.logo"
                    class="h-8 w-auto object-contain"
                    :src="company.logo"
                    :alt="company.name"
                />
                <span v-else class="text-sm font-bold text-catskillwhite-900">{{ company.name }}</span>

                <span class="text-sm font-bold text-catskillwhite-900">X</span>

                <img class="h-8 w-auto object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
            </button>

            <div class="flex items-center gap-3 sm:gap-6">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-white px-5 py-2 text-body font-semibold text-catskillwhite-900 transition hover:bg-catskillwhite-100"
                    @click="$emit('goHome')"
                >
                    <span>Quitter le test</span>
                    <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
                </button>

                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-white transition hover:bg-white/10"
                    title="Déconnexion"
                    aria-label="Déconnexion"
                    @click="$emit('logout')"
                >
                    <span class="material-symbols-outlined" style="font-size: 22px;" aria-hidden="true">logout</span>
                </button>
            </div>
        </div>
    </header>
</template>
