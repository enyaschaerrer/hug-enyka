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
    <section class="bg-[#FAF8F2] px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex items-start justify-between gap-6">
                <div>
                    <h2 class="text-2xl font-bold text-stone-900">Présentation des entreprises participantes</h2>
                    <p class="mt-2 text-sm text-stone-600">Découvrez nos entreprises participantes [...]</p>
                </div>

                <div class="flex shrink-0 items-center gap-3">
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-full bg-stone-200 text-stone-700 hover:bg-stone-300" aria-label="Filtrer">
                        ☰
                    </button>
                    <div class="relative">
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Recherche"
                            class="w-56 rounded-full border border-stone-200 bg-white px-4 py-2 pl-9 text-sm text-stone-500 focus:border-stone-400 focus:outline-none"
                        />
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-stone-400">⌕</span>
                    </div>
                </div>
            </div>

            <div class="mt-10 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-4">
                <article v-for="company in visible" :key="company.name" class="flex flex-col">
                    <div class="flex h-24 items-center justify-center rounded bg-stone-100 p-3">
                        <img
                            v-if="company.logo"
                            :src="company.logo"
                            :alt="company.name"
                            class="max-h-full max-w-full object-contain"
                        />
                        <span v-else class="text-lg font-bold text-stone-800">{{ company.name }}</span>
                    </div>
                    <div class="mt-3 flex items-end justify-between">
                        <div>
                            <div class="text-sm font-semibold uppercase text-stone-900">{{ company.name }}</div>
                            <div class="mt-1 text-xs text-stone-500">
                                Année d'adhésion <span class="ml-1 font-semibold text-stone-700">{{ company.adhesionYear ?? '—' }}</span>
                            </div>
                            <div class="text-xs text-stone-500">
                                Trophées gagnés
                                <span class="ml-1 inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-[#AB252E] px-1 text-xs font-bold text-white">
                                    {{ company.trophies }}
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <div v-if="visible.length === 0" class="mt-10 text-center text-sm text-stone-500">
                Aucune entreprise ne correspond à votre recherche.
            </div>

            <div v-if="hasMore" class="mt-10 flex justify-center">
                <button
                    type="button"
                    class="rounded-full bg-[#5A579E] px-8 py-3 text-sm font-semibold text-white transition hover:bg-[#4a4885]"
                    @click="showMore"
                >
                    Voir plus
                </button>
            </div>
        </div>
    </section>
</template>
