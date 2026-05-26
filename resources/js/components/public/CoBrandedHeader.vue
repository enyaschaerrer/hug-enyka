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
}>();

const colorItems = [
    { label: 'primaryColor', value: props.company.colors.primary },
    { label: 'secondaryColor', value: props.company.colors.secondary },
    { label: 'thirdColor', value: props.company.colors.third },
];
</script>

<template>
    <header class="font-cooper sticky top-0 z-40 border-b border-red-100 bg-white/90 px-4 py-3 shadow-sm backdrop-blur">
        <nav class="navbar mx-auto min-h-0 w-full max-w-6xl p-0">
            <div class="navbar-start">
                <a class="inline-flex items-center gap-3" href="/" aria-label="Accueil">
                    <img class="h-9 w-auto object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
                    <span class="text-xs font-semibold leading-tight uppercase text-stone-400">
                        <span class="cooper-baseline">X</span>
                    </span>
                    <img v-if="company.logo" class="h-11 w-auto object-contain" :src="company.logo" :alt="company.name" />
                    <span v-else class="text-sm font-bold leading-tight text-red-950">
                        <span class="cooper-baseline">{{ company.name }}</span>
                    </span>
                </a>
            </div>

            <div class="navbar-end">
                <div class="flex items-center gap-1" aria-label="Couleurs co-brandées">
                    <span
                        v-for="color in colorItems"
                        :key="color.label"
                        class="h-5 w-5 rounded border border-stone-200 shadow-sm"
                        :style="{ backgroundColor: color.value ?? '#ffffff' }"
                        :title="`${color.label}: ${color.value ?? 'non definie'}`"
                    ></span>
                </div>
            </div>
        </nav>
    </header>
</template>
