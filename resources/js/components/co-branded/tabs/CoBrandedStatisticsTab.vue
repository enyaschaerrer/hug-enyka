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

// Une seule carte active à la fois ; 1ère carte (index 0) activée par défaut
const activeIdx = ref(0);
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10 pt-24 sm:pt-32">
        <div
            class="grid gap-6 sm:grid-cols-3"
            @mouseleave="activeIdx = 0"
        >
            <div
                v-for="(stat, idx) in stats"
                :key="idx"
                class="relative"
                @mouseenter="activeIdx = idx"
            >
                <!-- Mascotte au-dessus, visible uniquement sur la carte active -->
                <img
                    :src="stat.mascot"
                    alt=""
                    :class="[
                        'pointer-events-none absolute -top-24 left-1/2 h-24 w-auto -translate-x-1/2 transition-opacity duration-200 sm:-top-28 sm:h-28',
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
    </section>
</template>
