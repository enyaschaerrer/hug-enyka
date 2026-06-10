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

defineProps<{
    company: CoBrandedCompany;
}>();

defineEmits<{
    goHome: [];
}>();
</script>

<template>
    <header class="sticky top-0 z-40 bg-catskillwhite-800 px-4 py-2 font-cooper shadow-sm lg:px-12">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-3">
            <button
                type="button"
                class="flex items-center gap-1.5 rounded-full bg-white px-3 py-1.5 transition hover:bg-catskillwhite-100"
                aria-label="Retour à l'accueil"
                @click="$emit('goHome')"
            >
                <img
                    v-if="company.logo"
                    class="h-4 lg:h-7 w-auto max-w-[90px] object-contain"
                    :src="company.logo"
                    :alt="company.name"
                />
                <span v-else class="text-sm font-bold text-catskillwhite-900">{{ company.name }}</span>

                <span class="text-sm font-bold text-catskillwhite-900">X</span>

                <img class="h-4 lg:h-7 w-auto max-w-[90px] object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
            </button>

            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-full bg-white px-3 py-1.5 text-body font-medium text-catskillwhite-900 transition hover:bg-catskillwhite-100"
                @click="$emit('goHome')"
            >
                <span>Quitter</span>
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">close</span>
            </button>
        </div>
    </header>
</template>
