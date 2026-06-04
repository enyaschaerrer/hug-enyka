<script setup lang="ts">
const stats = [
    {
        title: '700 dons par jour sont nécessaires en Suisse',
        description:
            'Chaque jour, des patient·es ont besoin de sang pour des opérations, des traitements contre le cancer, les accidents ou les maladies du sang.',
    },
    {
        title: '2,5 % de la population donne régulièrement',
        description:
            'Les réserves de sang reposent sur une petite partie de la population engagée dans des collectes régulières.',
    },
    {
        title: 'Près d\'un don sur deux vient d\'une collecte mobile',
        description:
            'Les collectes organisées en entreprise jouent un rôle essentiel pour atteindre les bénévoles sur leur lieu de travail.',
    },
];
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10">
        <div class="grid gap-6 sm:grid-cols-3">
            <article
                v-for="(stat, idx) in stats"
                :key="idx"
                class="rounded-2xl border-2 border-catskillwhite-400 bg-catskillwhite-100 p-6 text-center"
            >
                <h3 class="text-heading-t3 text-catskillwhite-900">{{ stat.title }}</h3>
                <p class="mt-3 text-caption text-catskillwhite-700">{{ stat.description }}</p>
            </article>
        </div>
    </section>
</template>
