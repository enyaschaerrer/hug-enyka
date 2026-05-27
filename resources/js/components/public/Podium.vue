<script setup lang="ts">
import { computed, ref } from 'vue';

type PodiumEntry = { name: string; trophies: number };
type YearPodium = { first: PodiumEntry; second: PodiumEntry; third: PodiumEntry };

// Données en dur pour l'instant — à remplacer par une requête plus tard.
const podiumByYear: Record<number, YearPodium> = {
    2024: {
        first: { name: 'Entreprise A', trophies: 3 },
        second: { name: 'Entreprise B', trophies: 2 },
        third: { name: 'Entreprise C', trophies: 1 },
    },
    2025: {
        first: { name: 'Entreprise D', trophies: 4 },
        second: { name: 'Entreprise E', trophies: 3 },
        third: { name: 'Entreprise F', trophies: 1 },
    },
    2026: {
        first: { name: 'Entreprise G', trophies: 5 },
        second: { name: 'Entreprise H', trophies: 2 },
        third: { name: 'Entreprise I', trophies: 1 },
    },
};

const availableYears = Object.keys(podiumByYear).map(Number).sort();
const selectedYear = ref(availableYears.at(-1)!);

const currentPodium = computed(() => podiumByYear[selectedYear.value]);

function prevYear() {
    const idx = availableYears.indexOf(selectedYear.value);
    if (idx > 0) selectedYear.value = availableYears[idx - 1];
}

function nextYear() {
    const idx = availableYears.indexOf(selectedYear.value);
    if (idx < availableYears.length - 1) selectedYear.value = availableYears[idx + 1];
}
</script>

<template>
    <section class="bg-[#FAF8F2] px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-stone-900">Le podium du Prix Du Coeur</h2>

                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-200 text-stone-700 transition hover:bg-stone-300 disabled:opacity-40"
                        :disabled="selectedYear === availableYears[0]"
                        aria-label="Année précédente"
                        @click="prevYear"
                    >
                        ‹
                    </button>
                    <span class="min-w-[3rem] text-center text-lg font-semibold text-stone-900">{{ selectedYear }}</span>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-200 text-stone-700 transition hover:bg-stone-300 disabled:opacity-40"
                        :disabled="selectedYear === availableYears.at(-1)"
                        aria-label="Année suivante"
                        @click="nextYear"
                    >
                        ›
                    </button>

                    <button type="button" class="ml-4 flex h-9 w-9 items-center justify-center rounded-full bg-stone-200 text-stone-700 hover:bg-stone-300" aria-label="Filtrer">
                        ☰
                    </button>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-12 lg:grid-cols-2">
                <!-- Podium 3 marches : 3e à gauche, 1er au centre, 2e à droite -->
                <div class="flex items-end justify-center gap-3">
                    <!-- 3e -->
                    <div class="flex flex-col items-center">
                        <div class="mb-2 h-16 w-24 rounded bg-stone-300"></div>
                        <div class="mb-1 text-xs text-stone-600">{{ currentPodium.third.name }}</div>
                        <div class="flex h-32 w-28 items-center justify-center rounded-t-lg bg-[#757ABC] text-3xl font-bold text-white">3</div>
                    </div>
                    <!-- 1er -->
                    <div class="flex flex-col items-center">
                        <div class="mb-2 h-16 w-24 rounded bg-stone-300"></div>
                        <div class="mb-1 text-xs text-stone-600">{{ currentPodium.first.name }}</div>
                        <div class="flex h-48 w-28 items-center justify-center rounded-t-lg bg-[#D6C19B] text-3xl font-bold text-white">1</div>
                    </div>
                    <!-- 2e -->
                    <div class="flex flex-col items-center">
                        <div class="mb-2 h-16 w-24 rounded bg-stone-300"></div>
                        <div class="mb-1 text-xs text-stone-600">{{ currentPodium.second.name }}</div>
                        <div class="flex h-40 w-28 items-center justify-center rounded-t-lg bg-[#EC8380] text-3xl font-bold text-white">2</div>
                    </div>
                </div>

                <!-- Classement à droite -->
                <ul class="flex flex-col justify-center gap-6">
                    <li class="flex items-center gap-4 border-b border-[#D6C19B] pb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#D6C19B] text-[#D6C19B]">›</span>
                        <div>
                            <div class="font-semibold text-stone-900">{{ currentPodium.first.name }} 1er place</div>
                            <div class="text-sm text-stone-600">Nbr de trophées effectués par l'entreprise</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4 border-b border-[#EC8380] pb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#EC8380] text-[#EC8380]">›</span>
                        <div>
                            <div class="font-semibold text-stone-900">{{ currentPodium.second.name }} 2ème place</div>
                            <div class="text-sm text-stone-600">Nbr de trophées effectués par l'entreprise</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4 border-b border-[#757ABC] pb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#757ABC] text-[#757ABC]">›</span>
                        <div>
                            <div class="font-semibold text-stone-900">{{ currentPodium.third.name }} 3ème place</div>
                            <div class="text-sm text-stone-600">Nbr de trophées effectués par l'entreprise</div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>
</template>
