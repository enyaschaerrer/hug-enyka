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

</script>

<template>
    <header class="font-cooper sticky top-0 z-40 bg-white/90 shadow-sm backdrop-blur">
        <div class="flex">
            <span class="h-[10px] flex-1" :style="{ backgroundColor: company.colors.primary ?? '#e5e7eb' }"></span>
            <span class="h-[10px] flex-1" :style="{ backgroundColor: company.colors.secondary ?? '#e5e7eb' }"></span>
            <span class="h-[10px] flex-1" :style="{ backgroundColor: company.colors.third ?? '#e5e7eb' }"></span>
        </div>
        <nav class="navbar mx-auto min-h-0 w-full max-w-6xl px-4 py-3">
            <div class="navbar-start">
                <a class="inline-flex items-center gap-3" href="/" aria-label="Accueil">
                    <img class="h-9 w-auto object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
                    <span class="text-xs font-semibold leading-tight uppercase" :style="{ color: company.colors.primary ?? '#a8a29e' }">
                        <span class="cooper-baseline">X</span>
                    </span>
                    <img v-if="company.logo" class="h-11 w-auto object-contain" :src="company.logo" :alt="company.name" />
                    <span v-else class="text-sm font-bold leading-tight text-red-950">
                        <span class="cooper-baseline">{{ company.name }}</span>
                    </span>
                </a>
            </div>

        </nav>
    </header>
</template>
