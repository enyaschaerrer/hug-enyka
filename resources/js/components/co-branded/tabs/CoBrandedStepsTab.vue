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

const mascot = '/img/mascots/sanguy_satisfied.webp';
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10 pt-24 sm:pt-32">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div
                v-for="(step, idx) in steps"
                :key="idx"
                class="group relative"
            >
                <!-- Mascotte au-dessus, apparaît au hover -->
                <img
                    :src="mascot"
                    alt=""
                    class="pointer-events-none absolute -top-16 left-1/2 h-24 w-auto -translate-x-1/2 opacity-0 transition-opacity duration-200 group-hover:opacity-100 sm:-top-24 sm:h-28"
                />

                <article class="flex h-full min-h-[280px] flex-col items-center rounded-2xl border-2 border-catskillwhite-800 bg-catskillwhite-50 px-6 py-10 text-center transition-colors group-hover:bg-catskillwhite-200">
                    <span
                        class="material-symbols-outlined text-catskillwhite-800"
                        style="font-size: 48px; font-variation-settings: 'FILL' 1;"
                        aria-hidden="true"
                    >{{ step.icon }}</span>
                    <h3 class="mt-4 text-heading-t1 text-catskillwhite-900">{{ step.title }}</h3>
                    <p class="mt-3 text-body text-catskillwhite-800">{{ step.description }}</p>
                </article>
            </div>
        </div>
    </section>
</template>
