<script setup lang="ts">
import { ref } from 'vue';

const started = ref(false);

const criteria = [
    { title: 'Âge', description: 'Vous êtes âgé·e entre 18 et 60 ans.' },
    { title: 'Poids', description: 'Vous pesez minimum 50 kg.' },
    { title: 'Globale', description: 'Vous êtes en bonne santé globale et vous n\'êtes pas enceinte.' },
];
</script>

<template>
    <section class="mx-auto max-w-3xl px-6 py-10">
        <!-- Critères éliminatoires : carte avec bandeau de titre catskillwhite-800 + 3 colonnes -->
        <div class="overflow-hidden rounded-2xl border-2 border-catskillwhite-800">
            <div class="bg-catskillwhite-800 px-6 py-2 text-center">
                <h3 class="text-body font-semibold text-white">Les critères éliminatoires</h3>
            </div>
            <div class="grid gap-4 bg-white p-6 sm:grid-cols-3 sm:divide-x sm:divide-catskillwhite-300">
                <div
                    v-for="(item, idx) in criteria"
                    :key="idx"
                    class="px-4 text-center"
                >
                    <h4 class="text-heading-t3 text-catskillwhite-900">{{ item.title }}</h4>
                    <p class="mt-1 text-caption text-catskillwhite-700">{{ item.description }}</p>
                </div>
            </div>
        </div>

        <!-- Card "Prêt·e à savoir..." -->
        <div
            v-if="!started"
            class="mt-10 rounded-2xl bg-razzmatazz-200 px-6 py-10 text-center sm:px-12"
        >
            <h2 class="text-display text-catskillwhite-900">
                Prêt·e à savoir si vous<br>pouvez donner votre sang ?
            </h2>
            <p class="mt-3 text-caption text-catskillwhite-800">
                Passez au test d'éligibilité !
            </p>

            <!-- Mascottes au-dessus, qui débordent légèrement sur les boutons -->
            <div class="mt-8 flex justify-center gap-16 sm:gap-28">
                <img :src="'/img/mascots/sanguy_devastated.webp'" alt="" class="h-24 w-auto sm:h-28" />
                <img :src="'/img/mascots/blutly_run.webp'" alt="" class="h-24 w-auto sm:h-28" />
            </div>

            <!-- Boutons Non / Oui — remontés pour passer derrière le bas des mascottes -->
            <div class="-mt-6 flex justify-center gap-4">
                <button
                    type="button"
                    class="min-w-32 rounded-2xl bg-razzmatazz-800 px-10 py-4 text-heading-t2 font-semibold text-white transition hover:bg-razzmatazz-900 sm:min-w-40"
                    @click="started = false"
                >
                    Non
                </button>
                <button
                    type="button"
                    class="min-w-32 rounded-2xl bg-razzmatazz-800 px-10 py-4 text-heading-t2 font-semibold text-white transition hover:bg-razzmatazz-900 sm:min-w-40"
                    @click="started = true"
                >
                    Oui
                </button>
            </div>
        </div>

        <!-- Test placeholder (à brancher sur TinderEligibilityPrototype) -->
        <div v-else class="mt-10 rounded-2xl border-2 border-catskillwhite-400 bg-catskillwhite-100 p-10 text-center">
            <p class="text-body text-catskillwhite-700">Test à brancher ici.</p>
            <button
                type="button"
                class="mt-4 rounded-full bg-catskillwhite-700 px-6 py-2 text-body font-semibold text-white"
                @click="started = false"
            >
                Retour
            </button>
        </div>
    </section>
</template>
