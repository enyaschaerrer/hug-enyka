<script setup lang="ts">
import { computed, ref, watch } from 'vue';

type PodiumEntry = { name: string | null; logo: string | null; trophies: number };
type YearPodium = { year: number; first: PodiumEntry; second: PodiumEntry; third: PodiumEntry };
type PrizeType = 'donneur' | 'ambassadeur' | 'prixJury';

const props = defineProps<{
    initialPodiums: Record<PrizeType, YearPodium[]>;
}>();

const prizeTypeOptions: { value: PrizeType; label: string }[] = [
    { value: 'donneur', label: 'Meilleur donneur' },
    { value: 'ambassadeur', label: 'Meilleur ambassadeur' },
    { value: 'prixJury', label: 'Coup de cœur du jury' },
];

const selectedPrizeType = ref<PrizeType>('donneur');
const isFilterOpen = ref(false);

const podiumsForType = computed(() => props.initialPodiums[selectedPrizeType.value] ?? []);

const podiumByYear = computed<Record<number, YearPodium>>(() =>
    Object.fromEntries(podiumsForType.value.map((p) => [p.year, p])),
);

const availableYears = computed(() => podiumsForType.value.map((p) => p.year).sort());

const selectedYear = ref<number | undefined>(availableYears.value.at(-1));

// Reset selectedYear quand on change de type de prix
watch(selectedPrizeType, () => {
    selectedYear.value = availableYears.value.at(-1);
});

const currentPodium = computed(() =>
    selectedYear.value !== undefined ? podiumByYear.value[selectedYear.value] : undefined,
);

function prevYear() {
    if (selectedYear.value === undefined) return;
    const idx = availableYears.value.indexOf(selectedYear.value);
    if (idx > 0) selectedYear.value = availableYears.value[idx - 1];
}

function nextYear() {
    if (selectedYear.value === undefined) return;
    const idx = availableYears.value.indexOf(selectedYear.value);
    if (idx < availableYears.value.length - 1) selectedYear.value = availableYears.value[idx + 1];
}

function selectPrizeType(type: PrizeType) {
    selectedPrizeType.value = type;
}
</script>

<template>
    <section class="px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex items-center justify-between">
                <h2 class="text-display text-martinique-950">Le podium du Prix du Coeur</h2>

                <div class="relative flex items-center gap-3">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-chicago-200 text-martinique-950 transition hover:bg-chicago-300 disabled:opacity-40"
                        :disabled="!availableYears.length || selectedYear === availableYears[0]"
                        aria-label="Année précédente"
                        @click="prevYear"
                    >
                        <span class="material-symbols-outlined rotate-180" aria-hidden="true">arrow_forward_ios</span>
                    </button>
                    <span class="min-w-[3rem] text-center text-heading-t2 text-martinique-950">{{ selectedYear ?? '—' }}</span>
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-chicago-200 text-martinique-950 transition hover:bg-chicago-300 disabled:opacity-40"
                        :disabled="!availableYears.length || selectedYear === availableYears.at(-1)"
                        aria-label="Année suivante"
                        @click="nextYear"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">arrow_forward_ios</span>
                    </button>

                    <button
                        type="button"
                        class="ml-4 flex h-9 w-9 items-center justify-center rounded-full bg-chicago-200 text-martinique-950 hover:bg-chicago-300"
                        aria-label="Filtrer"
                        @click="isFilterOpen = !isFilterOpen"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                    </button>

                    <!-- Filtre popup -->
                    <div
                        v-if="isFilterOpen"
                        class="absolute right-0 top-full z-30 mt-3 w-80 rounded-2xl border border-martinique-200 bg-martinique-50 p-5 shadow-lg"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-heading-t3 text-martinique-800">
                                <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                                Filtrer par
                            </div>
                            <button
                                type="button"
                                class="text-martinique-800 hover:text-martinique-700"
                                aria-label="Fermer le filtre"
                                @click="isFilterOpen = false"
                            >
                                <span class="material-symbols-outlined" aria-hidden="true">close</span>
                            </button>
                        </div>

                        <div class="mt-4 border-b border-martinique-200 pb-2 text-body text-martinique-800">Type de prix</div>

                        <ul class="mt-3 flex flex-col gap-3">
                            <li v-for="option in prizeTypeOptions" :key="option.value">
                                <label class="flex cursor-pointer items-center gap-3 text-body text-martinique-800">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-martinique-300 text-martinique-700 focus:ring-martinique-500"
                                        :checked="selectedPrizeType === option.value"
                                        @change="selectPrizeType(option.value)"
                                    />
                                    {{ option.label }}
                                </label>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div v-if="!currentPodium" class="mt-10 text-center text-body text-martinique-950">
                Aucun podium disponible pour ce filtre.
            </div>

            <template v-else>
                <div class="mt-10 grid grid-cols-1 gap-12 lg:grid-cols-2">
                    <!-- Podium 3 marches : 3e à gauche, 1er au centre, 2e à droite -->
                    <div class="flex items-end justify-center gap-3">
                        <!-- 3e -->
                        <div class="flex flex-col items-center">
                            <div class="mb-2 flex h-16 w-24 items-center justify-center rounded bg-white p-2">
                                <img v-if="currentPodium.third.logo" :src="currentPodium.third.logo" :alt="currentPodium.third.name ?? ''" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div class="mb-1 text-caption text-martinique-950">{{ currentPodium.third.name ?? '—' }}</div>
                            <div class="flex h-32 w-28 items-center justify-center rounded-t-lg bg-martinique-500 text-display text-white">3</div>
                        </div>
                        <!-- 1er -->
                        <div class="flex flex-col items-center">
                            <div class="mb-2 flex h-16 w-24 items-center justify-center rounded bg-white p-2">
                                <img v-if="currentPodium.first.logo" :src="currentPodium.first.logo" :alt="currentPodium.first.name ?? ''" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div class="mb-1 text-caption text-martinique-950">{{ currentPodium.first.name ?? '—' }}</div>
                            <div class="flex h-48 w-28 items-center justify-center rounded-t-lg bg-merino-300 text-display text-white">1</div>
                        </div>
                        <!-- 2e -->
                        <div class="flex flex-col items-center">
                            <div class="mb-2 flex h-16 w-24 items-center justify-center rounded bg-white p-2">
                                <img v-if="currentPodium.second.logo" :src="currentPodium.second.logo" :alt="currentPodium.second.name ?? ''" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div class="mb-1 text-caption text-martinique-950">{{ currentPodium.second.name ?? '—' }}</div>
                            <div class="flex h-40 w-28 items-center justify-center rounded-t-lg bg-fuzzywuzzybrown-400 text-display text-white">2</div>
                        </div>
                    </div>

                    <!-- Classement à droite -->
                    <ul class="flex flex-col justify-center gap-6">
                        <li class="flex items-center gap-4 border-b border-merino-300 pb-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-merino-300 text-merino-300">›</span>
                            <div>
                                <div class="text-heading-t3 text-martinique-950">{{ currentPodium.first.name ?? '—' }} — 1ère place</div>
                                <div class="text-caption text-martinique-950">{{ currentPodium.first.trophies }} trophée{{ currentPodium.first.trophies > 1 ? 's' : '' }} remporté{{ currentPodium.first.trophies > 1 ? 's' : '' }} au total</div>
                            </div>
                        </li>
                        <li class="flex items-center gap-4 border-b border-fuzzywuzzybrown-400 pb-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-fuzzywuzzybrown-400 text-fuzzywuzzybrown-400">›</span>
                            <div>
                                <div class="text-heading-t3 text-martinique-950">{{ currentPodium.second.name ?? '—' }} — 2ème place</div>
                                <div class="text-caption text-martinique-950">{{ currentPodium.second.trophies }} trophée{{ currentPodium.second.trophies > 1 ? 's' : '' }} remporté{{ currentPodium.second.trophies > 1 ? 's' : '' }} au total</div>
                            </div>
                        </li>
                        <li class="flex items-center gap-4 border-b border-martinique-500 pb-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-martinique-500 text-martinique-500">›</span>
                            <div>
                                <div class="text-heading-t3 text-martinique-950">{{ currentPodium.third.name ?? '—' }} — 3ème place</div>
                                <div class="text-caption text-martinique-950">{{ currentPodium.third.trophies }} trophée{{ currentPodium.third.trophies > 1 ? 's' : '' }} remporté{{ currentPodium.third.trophies > 1 ? 's' : '' }} au total</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </template>
        </div>
    </section>
</template>
