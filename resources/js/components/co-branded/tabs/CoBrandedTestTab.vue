<script setup lang="ts">
import { useAdminRouter } from '../../../composables/useAdminRouter';
import { useCoBrandedCollecte } from '../../../composables/useCoBrandedCollecte';

const { navigate } = useAdminRouter();
const { collection } = useCoBrandedCollecte();

const emit = defineEmits<{
    /** Retour vers l'onglet « Informations » de la page collecte. */
    goToInformations: [];
}>();

const criteria = [
    { title: 'Âge', description: 'Vous êtes âgé·e entre 18 et 60 ans.', short: '18-60 ans' },
    { title: 'Poids', description: 'Vous pesez minimum 50 kg.', short: 'Min. 50 kg' },
    { title: 'Globale', description: 'Vous êtes en bonne santé globale et vous n\'êtes pas enceinte.', short: 'Pas enceinte' },
];

function startQuestionnaire() {
    navigate(collection.eligibilityUrl);
}
</script>

<template>
    <section class="mx-auto max-w-3xl px-6 pt-6 pb-32 sm:pt-10">
        <!-- Mobile : juste les 3 infos, sans boîte ni sous-titres -->
        <!-- <ul class="flex flex-col items-center gap-2 sm:hidden">
            <li
                v-for="(item, idx) in criteria"
                :key="idx"
                class="flex items-center gap-2 text-body font-medium text-catskillwhite-900"
            >
                <span class="material-symbols-outlined text-razzmatazz-700" style="font-size: 20px;" aria-hidden="true">check_circle</span>
                {{ item.short }}
            </li>
        </ul> -->

        <!-- Desktop : carte « critères éliminatoires » avec bandeau de titre + 3 colonnes -->
        <div class=" overflow-hidden rounded-2xl border-2 border-catskillwhite-800">
            <div class="bg-catskillwhite-800 px-6 py-2 text-center">
                <h3 class="text-body font-semibold text-white">Les critères éliminatoires</h3>
            </div>
            <div class="grid gap-3 lg:gap-4 bg-white p-2 lg:p-4 sm:grid-cols-3 sm:divide-x sm:divide-catskillwhite-300">
                <div
                    v-for="(item, idx) in criteria"
                    :key="idx"
                    class="px-2 text-center"
                >
                    <h4 class="text-[1rem] font-medium lg:text-heading-t3 text-catskillwhite-900">{{ item.title }}</h4>
                    <p class="lg:mt-1 text-[0.8rem] lg:text-body text-catskillwhite-700">{{ item.description }}</p>
                </div>
            </div>
        </div>

        <!-- Card "Prêt·e à savoir..." — pb-0 + boutons translatés pour dépasser à moitié en bas -->
        <div class="rounded-2xl bg-razzmatazz-200 px-6 pt-10 pb-0 text-center mt-5 lg:mt-10 lg:px-12">
            <h2 class="text-[1.3rem] font-semibold text-razzmatazz-950 lg:text-display">
                Prêt·e à savoir si vous<br>pouvez donner votre sang ?
            </h2>
            <p class="mt-3 text-[0.8rem] text-razzmatazz-950 lg:text-body">
                Passez au test d'éligibilité !
            </p>

            <!-- Mascottes au-dessus, qui débordent légèrement sur les boutons -->
            <div class="mt-4 flex items-end justify-center gap-12 sm:mt-4 sm:gap-28">
                <img :src="'/img/mascots/blutly_sanguy_hey.webp'" alt="" class="h-12 w-auto sm:h-40" />
            </div>

            <!-- Boutons Non / Oui : très gros, translate-y-1/2 → dépassent à moitié en bas de la carte -->
            <div class="mt-2 flex translate-y-1/2 justify-center gap-4 lg:gap-6">
                <button
                    type="button"
                    class="min-w-30 rounded-2xl border-razzmatazz-800 border-2 bg-white text-heading-t3 py-3 font-semibold text-razzmatazz-800 transition hover:bg-razzmatazz-100 lg:min-w-40 lg:px-12 sm:py-4"
                    @click="emit('goToInformations')"
                >
                    Pas encore
                </button>
                <button
                    type="button"
                    class="min-w-28 rounded-2xl bg-razzmatazz-800 px-8 py-3 text-heading-t3 font-semibold text-white transition hover:bg-razzmatazz-600 lg:min-w-40 lg:px-12 lg:py-4"
                    @click="startQuestionnaire"
                >
                    Oui
                </button>
            </div>
        </div>
    </section>
</template>
