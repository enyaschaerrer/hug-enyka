<script setup lang="ts">
import { useAdminRouter } from '../../../composables/useAdminRouter';
import { useCoBrandedCollecte } from '../../../composables/useCoBrandedCollecte';

const { navigate } = useAdminRouter();
const { collection } = useCoBrandedCollecte();

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
    <section class="mx-auto max-w-3xl px-6 pt-10 pb-32">
        <!-- Critères éliminatoires : carte avec bandeau de titre catskillwhite-800 + 3 colonnes -->
        <div class="overflow-hidden rounded-2xl border-2 border-catskillwhite-800">
            <div class="bg-catskillwhite-800 px-6 py-2 text-center">
                <h3 class="text-body font-semibold text-white">Les critères éliminatoires</h3>
            </div>
            <div class="grid gap-4 bg-white p-4 sm:grid-cols-3 sm:divide-x sm:divide-catskillwhite-300">
                <div
                    v-for="(item, idx) in criteria"
                    :key="idx"
                    class="px-2 text-center"
                >
                    <h4 class="text-heading-t3 text-catskillwhite-900">{{ item.title }}</h4>
                    <p class="mt-1 text-body text-catskillwhite-700">{{ item.description }}</p>
                </div>
            </div>
        </div>

        <!-- Card "Prêt·e à savoir..." — pb-0 + boutons translatés pour dépasser à moitié en bas -->
        <div class="mt-10 rounded-2xl bg-razzmatazz-200 px-6 pt-10 pb-0 text-center sm:px-12">
            <h2 class="text-display text-catskillwhite-900">
                Prêt·e à savoir si vous<br>pouvez donner votre sang ?
            </h2>
            <p class="mt-3 text-body text-catskillwhite-800">
                Passez au test d'éligibilité !
            </p>

            <!-- Mascottes au-dessus, qui débordent légèrement sur les boutons -->
            <div class="-mt-8 flex items-end justify-center gap-16 sm:-mt-16 sm:gap-28">
                <img :src="'/img/mascots/sanguy_devastated.webp'" alt="" class="h-32 w-auto translate-y-6 sm:h-56 sm:translate-y-10" />
                <img :src="'/img/mascots/blutly_run.webp'" alt="" class="h-24 w-auto sm:h-36" />
            </div>

            <!-- Boutons Non / Oui : très gros, translate-y-1/2 → dépassent à moitié en bas de la carte -->
            <div class="-mt-6 flex translate-y-1/2 justify-center gap-4 sm:gap-6">
                <a
                    href="/"
                    class="min-w-44 rounded-2xl bg-razzmatazz-800 px-12 py-6 text-heading-t1 font-semibold text-white transition hover:bg-razzmatazz-900 sm:min-w-56 sm:px-16 sm:py-7"
                >
                    Non
                </a>
                <button
                    type="button"
                    class="min-w-44 rounded-2xl bg-razzmatazz-800 px-12 py-6 text-heading-t1 font-semibold text-white transition hover:bg-razzmatazz-900 sm:min-w-56 sm:px-16 sm:py-7"
                    @click="startQuestionnaire"
                >
                    Oui
                </button>
            </div>
        </div>
    </section>
</template>
