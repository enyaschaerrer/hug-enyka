<script setup lang="ts">
import { computed, ref } from 'vue';

type Company = {
    name: string;
    logo: string | null;
    adhesionYear: number | null;
    trophies: number;
};

const props = withDefaults(
    defineProps<{
        initialCompanies: Company[];
        title?: string;
        description?: string;
        showTrophies?: boolean;
    }>(),
    {
        title: 'Présentation des entreprises participantes',
        description: 'Découvrez les entreprises qui ont pris part au Prix du Coeur depuis sa création.',
        showTrophies: true,
    },
);

const search = ref('');
const pageSize = 8;
const visibleCount = ref(pageSize);

const isFilterOpen = ref(false);
const selectedYears = ref<Set<number>>(new Set());
const minTrophies = ref(0);

const availableYears = computed(() => {
    const years = new Set<number>();
    for (const c of props.initialCompanies) {
        if (c.adhesionYear !== null) years.add(c.adhesionYear);
    }
    return [...years].sort();
});

const filtered = computed(() =>
    props.initialCompanies.filter((c) => {
        if (!c.name.toLowerCase().includes(search.value.toLowerCase())) return false;
        if (selectedYears.value.size > 0 && (c.adhesionYear === null || !selectedYears.value.has(c.adhesionYear))) return false;
        if (c.trophies < minTrophies.value) return false;
        return true;
    }),
);

const visible = computed(() => filtered.value.slice(0, visibleCount.value));
const hasMore = computed(() => visibleCount.value < filtered.value.length);

function showMore() {
    visibleCount.value += pageSize;
}

function toggleYear(year: number) {
    const next = new Set(selectedYears.value);
    if (next.has(year)) next.delete(year);
    else next.add(year);
    selectedYears.value = next;
}

function incrementTrophies() {
    minTrophies.value += 1;
}

function decrementTrophies() {
    if (minTrophies.value > 0) minTrophies.value -= 1;
}

function resetFilters() {
    selectedYears.value = new Set();
    minTrophies.value = 0;
}
</script>

<template>
    <section class="px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div>
                <h2 class="text-display text-martinique-950">{{ props.title }}</h2>
                <p class="mt-2 text-body text-martinique-950">{{ props.description }}</p>
            </div>

            <div class="relative mt-8 flex items-center gap-3">
                    <button
                        type="button"
                        class="flex h-9 w-9 items-center justify-center rounded-full bg-martinique-100 text-martinique-700 hover:bg-martinique-200"
                        aria-label="Filtrer"
                        @click="isFilterOpen = !isFilterOpen"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">filter_list</span>
                    </button>
                    <div class="relative">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Recherche"
                            class="w-56 rounded-full bg-martinique-100 px-4 py-2 pl-11 text-body text-martinique-700 placeholder-martinique-700 focus:border-martinique-500 focus:outline-none"
                        />
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-martinique-700" aria-hidden="true">search</span>
                    </div>

                    <!-- Filtre popup -->
                    <div
                        v-if="isFilterOpen"
                        class="absolute left-0 top-full z-30 mt-3 flex max-h-[80vh] w-80 flex-col overflow-y-auto rounded-2xl border border-martinique-200 bg-martinique-50 p-5 shadow-lg"
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

                        <div class="mt-4 border-b border-martinique-200 pb-2 text-body text-martinique-800">Année d'adhésion</div>

                        <ul class="mt-3 flex flex-col gap-3">
                            <li v-for="year in availableYears" :key="year">
                                <label class="flex cursor-pointer items-center gap-3 text-body text-martinique-800">
                                    <input
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-martinique-300 text-martinique-700 focus:ring-martinique-500"
                                        :checked="selectedYears.has(year)"
                                        @change="toggleYear(year)"
                                    />
                                    {{ year }}
                                </label>
                            </li>
                        </ul>

                        <template v-if="props.showTrophies">
                            <div class="mt-5 border-b border-martinique-200 pb-2 text-body text-martinique-800">Nombre de trophées gagnés</div>

                            <div class="mt-3 inline-flex items-center gap-2 rounded-md border border-martinique-300 bg-white px-3 py-1">
                                <span class="text-body text-martinique-800">{{ minTrophies }}</span>
                                <div class="flex flex-col">
                                    <button
                                        type="button"
                                        class="flex h-4 w-4 items-center justify-center text-martinique-800 hover:text-martinique-700"
                                        aria-label="Augmenter"
                                        @click="incrementTrophies"
                                    >
                                        <span class="material-symbols-outlined -rotate-90" style="font-size: 16px;" aria-hidden="true">arrow_forward_ios</span>
                                    </button>
                                    <button
                                        type="button"
                                        class="flex h-4 w-4 items-center justify-center text-martinique-800 hover:text-martinique-700 disabled:opacity-40"
                                        aria-label="Diminuer"
                                        :disabled="minTrophies <= 0"
                                        @click="decrementTrophies"
                                    >
                                        <span class="material-symbols-outlined rotate-90" style="font-size: 16px;" aria-hidden="true">arrow_forward_ios</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <button
                            type="button"
                            class="mt-5 rounded-full border border-martinique-300 px-4 py-2 text-body text-martinique-800 transition hover:bg-martinique-100"
                            @click="resetFilters"
                        >
                            Réinitialiser
                        </button>
                    </div>
                </div>

            <div class="mt-16 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
                <article v-for="company in visible" :key="company.name" class="flex flex-col">
                    <div class="flex h-24 items-center justify-center rounded bg-white p-3">
                        <img
                            v-if="company.logo"
                            :src="company.logo"
                            :alt="company.name"
                            class="max-h-full max-w-full object-contain"
                        />
                        <span v-else class="text-heading-t3 text-martinique-950">{{ company.name }}</span>
                    </div>
                    <div class="mt-3 flex items-end justify-between">
                        <div>
                            <div class="text-heading-t3 uppercase text-martinique-950">{{ company.name }}</div>
                            <div class="mt-1 text-caption text-martinique-950">
                                Année d'adhésion <span class="ml-1 font-semibold">{{ company.adhesionYear ?? '—' }}</span>
                            </div>
                            <div v-if="props.showTrophies" class="text-caption text-martinique-950">
                                Prix gagnés
                                <span class="ml-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-fuzzywuzzybrown-700 px-1 text-caption font-medium text-white">
                                    {{ company.trophies }}
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="visible.length === 0" class="mt-10 text-center text-body text-martinique-950">
                Aucune entreprise ne correspond à votre recherche.
            </div>

            <div v-if="hasMore" class="mt-10 flex justify-center">
                <button
                    type="button"
                    class="rounded-full bg-martinique-700 px-8 py-3 text-body text-white transition hover:bg-martinique-800"
                    @click="showMore"
                >
                    Voir plus
                </button>
            </div>
        </div>
    </section>
</template>
