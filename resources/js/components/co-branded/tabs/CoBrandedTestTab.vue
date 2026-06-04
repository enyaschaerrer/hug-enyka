<script setup lang="ts">
import { ref } from 'vue';
import { useAdminRouter } from '../../../composables/useAdminRouter';
import { useCoBrandedCollecte } from '../../../composables/useCoBrandedCollecte';
// TODO: brancher TinderEligibilityPrototype quand started === true
// import TinderEligibilityPrototype from '../../tinder-cards/TinderEligibilityPrototype.vue';

const { navigate } = useAdminRouter();
const { collection } = useCoBrandedCollecte();
const started = ref(false);

const criteria = [
    { title: 'Âge', description: 'Vous êtes âgé·e entre 18 et 60 ans.' },
    { title: 'Poids', description: 'Vous pesez minimum 50 kg.' },
    { title: 'Globale', description: 'Vous êtes en bonne santé globale et vous n\'êtes pas enceinte.' },
];

function startQuestionnaire() {
    navigate(collection.eligibilityUrl);
}
</script>

<template>
    <section class="mx-auto max-w-3xl px-6 py-10">
        <!-- Critères d'éligibilité -->
        <div class="rounded-2xl border-2 border-catskillwhite-400 bg-catskillwhite-100 p-6">
            <div class="grid gap-4 sm:grid-cols-3 sm:divide-x sm:divide-catskillwhite-400">
                <div
                    v-for="(item, idx) in criteria"
                    :key="idx"
                    class="px-4 text-center"
                >
                    <h3 class="text-heading-t3 text-catskillwhite-900">{{ item.title }}</h3>
                    <p class="mt-1 text-caption text-catskillwhite-700">{{ item.description }}</p>
                </div>
            </div>
        </div>

        <!-- Card Oui/Non -->
        <div
            v-if="!started"
            class="mt-10 rounded-2xl bg-razzmatazz-200 p-10 text-center"
        >
            <h2 class="text-display text-catskillwhite-900">
                Prêt·e à savoir si vous pouvez donner votre sang ?
            </h2>
            <p class="mt-3 text-body text-catskillwhite-800">
                Passez au test d'éligibilité !
            </p>

            <div class="mt-8 flex justify-center gap-4">
                <button
                    type="button"
                    class="rounded-2xl bg-razzmatazz-700 px-12 py-3 text-body font-semibold text-white transition hover:bg-razzmatazz-800"
                    @click="started = false"
                >
                    Non
                </button>
                <button
                    type="button"
                    class="rounded-2xl bg-razzmatazz-700 px-12 py-3 text-body font-semibold text-white transition hover:bg-razzmatazz-800"
                    @click="startQuestionnaire"
                >
                    Oui
                </button>
            </div>
        </div>

        <!-- Test (à brancher sur TinderEligibilityPrototype) -->
        <div v-else class="mt-10 rounded-2xl border-2 border-catskillwhite-400 bg-catskillwhite-100 p-10 text-center">
            <!-- <TinderEligibilityPrototype /> -->
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
