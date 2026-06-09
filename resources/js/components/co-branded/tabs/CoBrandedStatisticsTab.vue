<script setup lang="ts">
import { ref } from 'vue';

const stats = [
    {
        title: '700 dons par jour sont nécessaires en Suisse',
        description:
            'Chaque jour, des patient·es ont besoin de sang pour des opérations, des traitements contre le cancer, les accidents ou les maladies du sang.',
        mascot: '/img/mascots/sanguy_above.webp',
    },
    {
        title: '2,5 % de la population donne régulièrement',
        description:
            'Les réserves de sang reposent sur une petite partie de la population engagée dans des collectes régulières.',
        mascot: '/img/mascots/blutly_above.webp',
    },
    {
        title: 'Près d\'un don sur deux vient d\'une collecte mobile',
        description:
            'Les collectes organisées en entreprise jouent un rôle essentiel pour atteindre les bénévoles sur leur lieu de travail.',
        mascot: '/img/mascots/sanguy_above.webp',
    },
];

const activeIdx = ref(0);

function toggleMobile(idx: number) {
    activeIdx.value = activeIdx.value === idx ? -1 : idx;
}
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10 pt-5 lg:pt-24 lg:pt-32">

        <!-- Version desktop -->
        <div
            class="hidden gap-6 sm:grid sm:grid-cols-3"
            @mouseleave="activeIdx = 0"
        >
            <div
                v-for="(stat, idx) in stats"
                :key="idx"
                class="relative"
                @mouseenter="activeIdx = idx"
            >
                <img
                    :src="stat.mascot"
                    alt=""
                    :class="[
                        'pointer-events-none absolute -top-29 left-1/2 h-48 w-auto -translate-x-1/2 transition-opacity duration-200 sm:-top-53 sm:h-58',
                        activeIdx === idx ? 'opacity-100' : 'opacity-0',
                    ]"
                />
                <article
                    :class="[
                        'flex h-full flex-col items-center rounded-2xl border-2 border-catskillwhite-800 px-8 py-10 text-center transition-colors',
                        activeIdx === idx ? 'bg-catskillwhite-200' : 'bg-catskillwhite-50',
                    ]"
                >
                    <div class="rounded-xl bg-catskillwhite-800 px-5 py-2 text-white">
                        <h3 class="text-heading-t2">{{ stat.title }}</h3>
                    </div>
                    <p class="mt-6 text-body text-catskillwhite-800">{{ stat.description }}</p>
                </article>
            </div>
        </div>

        <!-- Version mobile -->
        <div class="flex flex-col gap-4 sm:hidden">
            <div
                v-for="(stat, idx) in stats"
                :key="idx"
                class="relative transition-all duration-200"
                :class="activeIdx === idx ? 'mt-15' : 'mt-0'"
            >
                <!-- Mascotte positionnée en absolu au-dessus -->
                <img
                    v-if="activeIdx === idx"
                    :src="stat.mascot"
                    alt=""
                    class="pointer-events-none absolute -top-36.5 left-1/2 h-40 w-auto -translate-x-1/2"
                />

                <article
                    class="rounded-2xl border-2 border-catskillwhite-800 overflow-hidden"
                    :class="activeIdx === idx ? 'bg-catskillwhite-200' : 'bg-catskillwhite-50'"
                >
                    <!-- Titre centré dans son bloc -->
                    <div class="px-6 pt-6">
                        <div class="rounded-xl bg-catskillwhite-800 px-5 py-2 text-center text-white">
                            <h3 class="text-heading-t2">{{ stat.title }}</h3>
                        </div>
                    </div>

                    <!-- Contenu déroulable -->
                    <div
                        v-if="activeIdx === idx"
                        class="px-6 pb-4 pt-4"
                    >
                        <p class="text-body text-catskillwhite-800 text-center">{{ stat.description }}</p>
                    </div>

                    <!-- Bouton centré -->
                    <button
                        type="button"
                        class="flex w-full flex-col items-center gap-1 pb-5 pt-4"
                        @click="toggleMobile(idx)"
                    >
                        <span class="text-caption text-catskillwhite-800">
                            {{ activeIdx === idx ? 'Réduire' : 'Lire plus' }}
                        </span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-catskillwhite-800 transition-transform duration-200"
                            :class="activeIdx === idx ? 'rotate-180' : ''"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </article>
            </div>
        </div>

    </section>
</template>
