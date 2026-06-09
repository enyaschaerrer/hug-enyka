<script setup lang="ts">
import { ref } from 'vue';

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

const mascot = '/img/mascots/sanguy_hero.webp';

const activeIdx = ref(0);

function toggleMobile(idx: number) {
    activeIdx.value = activeIdx.value === idx ? -1 : idx;
}
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10 pt-5 lg:pt-32">

        <!-- Version desktop -->
        <div
            class="hidden gap-4 sm:grid sm:grid-cols-2 lg:grid-cols-4"
            @mouseleave="activeIdx = 0"
        >
            <div
                v-for="(step, idx) in steps"
                :key="idx"
                class="relative"
                @mouseenter="activeIdx = idx"
            >
                <img
                    :src="mascot"
                    alt=""
                    :class="[
                        'pointer-events-none absolute -top-28 left-28 h-32 w-auto -translate-x-1/2 transition-opacity duration-200 lg:-top-35 lg:h-45',
                        activeIdx === idx ? 'opacity-100' : 'opacity-0',
                    ]"
                />
                <article
                    :class="[
                        'flex h-full min-h-[280px] flex-col items-center rounded-2xl border-2 border-catskillwhite-800 px-6 py-10 text-center transition-colors',
                        activeIdx === idx ? 'bg-catskillwhite-200' : 'bg-catskillwhite-50',
                    ]"
                >
                    <span
                        class="material-symbols-outlined text-catskillwhite-800"
                        style="font-size: 48px; font-variation-settings: 'FILL' 1;"
                        aria-hidden="true"
                    >{{ step.icon }}</span>
                    <h3 class="mt-4 text-heading-t2 text-catskillwhite-900">{{ step.title }}</h3>
                    <p class="mt-3 text-body text-catskillwhite-800">{{ step.description }}</p>
                </article>
            </div>
        </div>

        <!-- Version mobile -->
        <div class="flex flex-col gap-3 sm:hidden">
            <div
                v-for="(step, idx) in steps"
                :key="idx"
            >
                <article
                    class="overflow-hidden rounded-2xl border-2 border-catskillwhite-800"
                    :class="activeIdx === idx ? 'bg-catskillwhite-200' : 'bg-catskillwhite-50'"
                >
                    <button
                        type="button"
                        class="flex w-full items-start gap-4 px-5 py-4"
                        @click="toggleMobile(idx)"
                    >
                        <!-- Icône à gauche -->
                        <span
                            class="material-symbols-outlined shrink-0 text-catskillwhite-800"
                            style="font-size: 55px; font-variation-settings: 'FILL' 1;"
                            aria-hidden="true"
                        >{{ step.icon }}</span>

                        <!-- Titre + Description + Lire plus -->
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <h3 class="text-left text-heading-t2 text-catskillwhite-900">{{ step.title }}</h3>

                            <!-- Mascotte + Description côte à côte quand déroulé -->
                            <div
                                v-if="activeIdx === idx"
                                class="flex items-center gap-5 px-5 pb-5"
                            >
                                <img
                                    :src="mascot"
                                    alt=""
                                    class="-ml-27 h-34 mt-5 w-auto shrink-0 object-contain"
                                />
                                <p class="text-center text-body text-catskillwhite-800">{{ step.description }}</p>
                            </div>

                            <div class="mt-1 flex items-center gap-1">
                                <span class="text-caption text-catskillwhite-800">
                                    {{ activeIdx === idx ? 'Réduire' : 'Lire plus' }}
                                </span>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-catskillwhite-800 transition-transform duration-200"
                                    :class="activeIdx === idx ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </button>
                </article>
            </div>
        </div>

    </section>
</template>