<script setup lang="ts">
import { computed, ref } from 'vue';

type PodiumEntry = { name: string | null; logo: string | null; trophies: number };
type YearPodium = { year: number; first: PodiumEntry; second: PodiumEntry; third: PodiumEntry };

const props = defineProps<{
    initialPodiums: YearPodium[];
}>();

const podiumByYear = computed<Record<number, YearPodium>>(() =>
    Object.fromEntries(props.initialPodiums.map((p) => [p.year, p])),
);

const availableYears = computed(() => props.initialPodiums.map((p) => p.year).sort());

const selectedYear = ref(availableYears.value.at(-1)!);

const currentPodium = computed(() => podiumByYear.value[selectedYear.value]);

function prevYear() {
    const idx = availableYears.value.indexOf(selectedYear.value);
    if (idx > 0) selectedYear.value = availableYears.value[idx - 1];
}

function nextYear() {
    const idx = availableYears.value.indexOf(selectedYear.value);
    if (idx < availableYears.value.length - 1) selectedYear.value = availableYears.value[idx + 1];
}
</script>

<template>
    <section class="bg-[#FAF8F2] px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div v-if="!currentPodium" class="text-center text-sm text-stone-500">
                Aucun podium disponible pour l'instant.
            </div>
            <template v-else>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-stone-900">Le podium du Prix du Coeur</h2>

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
                        <div class="mb-2 flex h-16 w-24 items-center justify-center rounded bg-white p-2">
                            <img v-if="currentPodium.third.logo" :src="currentPodium.third.logo" :alt="currentPodium.third.name ?? ''" class="max-h-full max-w-full object-contain" />
                        </div>
                        <div class="mb-1 text-xs text-stone-600">{{ currentPodium.third.name ?? '—' }}</div>
                        <div class="flex h-32 w-28 items-center justify-center rounded-t-lg bg-[#757ABC] text-3xl font-bold text-white">3</div>
                    </div>
                    <!-- 1er -->
                    <div class="flex flex-col items-center">
                        <div class="mb-2 flex h-16 w-24 items-center justify-center rounded bg-white p-2">
                            <img v-if="currentPodium.first.logo" :src="currentPodium.first.logo" :alt="currentPodium.first.name ?? ''" class="max-h-full max-w-full object-contain" />
                        </div>
                        <div class="mb-1 text-xs text-stone-600">{{ currentPodium.first.name ?? '—' }}</div>
                        <div class="flex h-48 w-28 items-center justify-center rounded-t-lg bg-[#D6C19B] text-3xl font-bold text-white">1</div>
                    </div>
                    <!-- 2e -->
                    <div class="flex flex-col items-center">
                        <div class="mb-2 flex h-16 w-24 items-center justify-center rounded bg-white p-2">
                            <img v-if="currentPodium.second.logo" :src="currentPodium.second.logo" :alt="currentPodium.second.name ?? ''" class="max-h-full max-w-full object-contain" />
                        </div>
                        <div class="mb-1 text-xs text-stone-600">{{ currentPodium.second.name ?? '—' }}</div>
                        <div class="flex h-40 w-28 items-center justify-center rounded-t-lg bg-[#EC8380] text-3xl font-bold text-white">2</div>
                    </div>
                </div>

                <!-- Classement à droite -->
                <ul class="flex flex-col justify-center gap-6">
                    <li class="flex items-center gap-4 border-b border-[#D6C19B] pb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#D6C19B] text-[#D6C19B]">›</span>
                        <div>
                            <div class="font-semibold text-stone-900">{{ currentPodium.first.name ?? '—' }} — 1ère place</div>
                            <div class="text-sm text-stone-600">{{ currentPodium.first.trophies }} trophée{{ currentPodium.first.trophies > 1 ? 's' : '' }} remporté{{ currentPodium.first.trophies > 1 ? 's' : '' }}</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4 border-b border-[#EC8380] pb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#EC8380] text-[#EC8380]">›</span>
                        <div>
                            <div class="font-semibold text-stone-900">{{ currentPodium.second.name ?? '—' }} — 2ème place</div>
                            <div class="text-sm text-stone-600">{{ currentPodium.second.trophies }} trophée{{ currentPodium.second.trophies > 1 ? 's' : '' }} remporté{{ currentPodium.second.trophies > 1 ? 's' : '' }}</div>
                        </div>
                    </li>
                    <li class="flex items-center gap-4 border-b border-[#757ABC] pb-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-[#757ABC] text-[#757ABC]">›</span>
                        <div>
                            <div class="font-semibold text-stone-900">{{ currentPodium.third.name ?? '—' }} — 3ème place</div>
                            <div class="text-sm text-stone-600">{{ currentPodium.third.trophies }} trophée{{ currentPodium.third.trophies > 1 ? 's' : '' }} remporté{{ currentPodium.third.trophies > 1 ? 's' : '' }}</div>
                        </div>
                    </li>
                </ul>
            </div>
            </template>
        </div>
    </section>
</template>
