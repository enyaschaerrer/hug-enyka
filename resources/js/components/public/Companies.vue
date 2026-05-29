<script setup lang="ts">
import { computed, ref } from 'vue';

type Company = {
    name: string;
    logo: string | null;
    adhesionYear: number | null;
    trophies: number;
};

const props = defineProps<{
    initialCompanies: Company[];
}>();

const search = ref('');
const pageSize = 8;
const visibleCount = ref(pageSize);

const filtered = computed(() =>
    props.initialCompanies.filter((c) => c.name.toLowerCase().includes(search.value.toLowerCase())),
);

const visible = computed(() => filtered.value.slice(0, visibleCount.value));
const hasMore = computed(() => visibleCount.value < filtered.value.length);

function showMore() {
    visibleCount.value += pageSize;
}
</script>

<template>
    <section class="bg-merino-50 px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <h2 class="text-display text-martinique-950">Présentation des entreprises participantes</h2>
                    <p class="mt-2 text-body text-martinique-950">Découvrez les entreprises qui ont pris part au Prix du Coeur depuis sa création. </p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-martinique-100 text-martinique-700 hover:bg-martinique-200" aria-label="Filtrer">
                        ☰
                    </button>
                    <div class="relative">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Recherche"
                            class="w-56 rounded-full border border-martinique-200 bg-white px-4 py-2 pl-9 text-body text-martinique-950 placeholder-martinique-400 focus:border-martinique-500 focus:outline-none"
                        />
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-martinique-400">⌕</span>
                    </div>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
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
                            <div class="text-caption text-martinique-950">
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
                    class="rounded-full bg-martinique-700 px-8 py-3 text-body font-semibold text-white transition hover:bg-martinique-800"
                    @click="showMore"
                >
                    Voir plus
                </button>
            </div>
        </div>
    </section>
</template>
