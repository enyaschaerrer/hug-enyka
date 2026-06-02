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
    <section class="px-6 py-8 lg:px-12 lg:py-16">
        <div class="mx-auto max-w-6xl">
            <h2 class="text-display text-martinique-950">Le podium du Prix du Coeur</h2>

            <!-- Tabs type de prix -->
            <div class="mt-8 flex flex-wrap gap-2 border-b border-martinique-200">
                <button
                    v-for="option in prizeTypeOptions"
                    :key="option.value"
                    type="button"
                    :class="selectedPrizeType === option.value ? 'border-fuzzywuzzybrown-700 text-fuzzywuzzybrown-700' : 'border-transparent text-martinique-700 hover:text-martinique-950'"
                    class="-mb-px border-b-2 px-4 py-3 text-body transition"
                    @click="selectPrizeType(option.value)"
                >
                    {{ option.label }}
                </button>
            </div>

            <!-- Sélecteur d'année -->
            <div class="mt-8 flex items-center gap-3">
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-martinique-100 text-martinique-700 transition hover:bg-martinique-200 disabled:opacity-40"
                    :disabled="!availableYears.length || selectedYear === availableYears[0]"
                    aria-label="Année précédente"
                    @click="prevYear"
                >
                    <span class="material-symbols-outlined" style="font-size: 30px;" aria-hidden="true">chevron_left</span>
                </button>
                <span class="min-w-[3rem] text-center text-body text-martinique-700">{{ selectedYear ?? '—' }}</span>
                <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-full bg-martinique-100 text-martinique-700 transition hover:bg-martinique-200 disabled:opacity-40"
                    :disabled="!availableYears.length || selectedYear === availableYears.at(-1)"
                    aria-label="Année suivante"
                    @click="nextYear"
                >
                    <span class="material-symbols-outlined" style="font-size: 30px;" aria-hidden="true">chevron_right</span>
                </button>
            </div>

            <div v-if="!currentPodium" class="mt-10 text-center text-body text-martinique-950">
                Aucun podium disponible pour ce filtre.
            </div>

            <template v-else>
                <div class="mt-13 grid grid-cols-1 gap-12 lg:grid-cols-2">
                    <!-- Podium 3 marches : 3e à gauche, 1er au centre, 2e à droite. Pour prixJury : 1 seule marche. -->
                    <div class="flex items-end justify-center gap-2 lg:gap-3">
                        <!-- 3e -->
                        <div v-if="selectedPrizeType !== 'prixJury'" class="flex flex-col items-center">
                            <div class="mb-2 flex h-12 w-20 items-center justify-center p-2 lg:h-16 lg:w-24">
                                <img v-if="currentPodium.third.logo" :src="currentPodium.third.logo" :alt="currentPodium.third.name ?? ''" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div class="flex h-24 w-20 items-center justify-center rounded-t-lg bg-martinique-500 text-display text-white lg:h-32 lg:w-28">3</div>
                        </div>
                        <!-- 1er -->
                        <div class="flex flex-col items-center">
                            <div class="mb-2 flex h-12 w-20 items-center justify-center p-2 lg:h-16 lg:w-24">
                                <img v-if="currentPodium.first.logo" :src="currentPodium.first.logo" :alt="currentPodium.first.name ?? ''" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div class="flex h-36 w-20 items-center justify-center rounded-t-lg bg-merino-300 text-display text-white lg:h-48 lg:w-28">1</div>
                        </div>
                        <!-- 2e -->
                        <div v-if="selectedPrizeType !== 'prixJury'" class="flex flex-col items-center">
                            <div class="mb-2 flex h-12 w-20 items-center justify-center p-2 lg:h-16 lg:w-24">
                                <img v-if="currentPodium.second.logo" :src="currentPodium.second.logo" :alt="currentPodium.second.name ?? ''" class="max-h-full max-w-full object-contain" />
                            </div>
                            <div class="flex h-28 w-20 items-center justify-center rounded-t-lg bg-fuzzywuzzybrown-400 text-display text-white lg:h-40 lg:w-28">2</div>
                        </div>
                    </div>

                    <!-- Classement à droite -->
                    <ul class="flex flex-col justify-center gap-6">
                        <li class="flex items-center gap-4 border-b border-merino-300 pb-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-merino-300 text-merino-300">›</span>
                            <div>
                                <div class="text-heading-t3 text-martinique-950">{{ currentPodium.first.name ?? '—' }}<template v-if="selectedPrizeType !== 'prixJury'"> — 1ère place</template></div>
                                <div class="text-caption text-martinique-950">{{ currentPodium.first.trophies }} prix remporté{{ currentPodium.first.trophies > 1 ? 's' : '' }} au total (toutes catégories confondues)</div>
                            </div>
                        </li>
                        <li v-if="selectedPrizeType !== 'prixJury'" class="flex items-center gap-4 border-b border-fuzzywuzzybrown-400 pb-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-fuzzywuzzybrown-400 text-fuzzywuzzybrown-400">›</span>
                            <div>
                                <div class="text-heading-t3 text-martinique-950">{{ currentPodium.second.name ?? '—' }} — 2ème place</div>
                                <div class="text-caption text-martinique-950">{{ currentPodium.second.trophies }} prix remporté{{ currentPodium.second.trophies > 1 ? 's' : '' }} au total (toutes catégories confondues)</div>
                            </div>
                        </li>
                        <li v-if="selectedPrizeType !== 'prixJury'" class="flex items-center gap-4 border-b border-martinique-500 pb-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-martinique-500 text-martinique-500">›</span>
                            <div>
                                <div class="text-heading-t3 text-martinique-950">{{ currentPodium.third.name ?? '—' }} — 3ème place</div>
                                <div class="text-caption text-martinique-950">{{ currentPodium.third.trophies }} prix remporté{{ currentPodium.third.trophies > 1 ? 's' : '' }} au total (toutes catégories confondues)</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </template>
        </div>
    </section>
</template>
