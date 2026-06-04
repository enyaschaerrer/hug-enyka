<script setup lang="ts">
const steps = [
    {
        icon: 'badge',
        title: 'Administration',
        description:
            'Se munir d\'une pièce d\'identité (non électronique) & remplir le questionnaire (feuille A4 recto verso).',
    },
    {
        icon: 'verified',
        title: 'Validation',
        description:
            'Contrôle d\'hémoglobine (piqûre au doigt) & entretien confidentiel avec une infirmière.',
    },
    {
        icon: 'bloodtype',
        title: 'Prélèvement',
        description: 'Le prélèvement de 450 ml de sang est effectué.',
    },
    {
        icon: 'chair',
        title: 'Repos',
        description:
            'Un temps de repos et une collation vous sont proposés et fortement conseillés.',
    },
];
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article
                v-for="(step, idx) in steps"
                :key="idx"
                class="rounded-2xl border-2 border-catskillwhite-400 bg-catskillwhite-100 p-6 text-center"
            >
                <span
                    class="material-symbols-outlined text-catskillwhite-800"
                    style="font-size: 48px;"
                    aria-hidden="true"
                >{{ step.icon }}</span>
                <h3 class="mt-3 text-heading-t3 text-catskillwhite-900">{{ step.title }}</h3>
                <p class="mt-2 text-caption text-catskillwhite-700">{{ step.description }}</p>
            </article>
        </div>
    </section>
</template>
