<script setup lang="ts">
defineProps<{
    companyName: string;
    collection: {
        start: string | null;
        end: string | null;
        appointmentUrl: string | null;
    };
}>();

defineEmits<{
    goToTest: [];
}>();
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10">
        <h2 class="text-center text-display text-catskillwhite-900">
            Votre <span class="text-fuzzywuzzybrown-700">{{ companyName }}</span> se mobilise pour le don du sang
        </h2>

        <!-- 3 cards Date / Lieu / Deadline -->
        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <article class="rounded-2xl bg-fuzzywuzzybrown-100 p-6 text-center">
                <span class="material-symbols-outlined text-fuzzywuzzybrown-700" style="font-size: 32px;" aria-hidden="true">calendar_today</span>
                <h3 class="mt-2 text-heading-t3 text-catskillwhite-900">Date</h3>
                <!-- TODO formatter collection.start / collection.end -->
                <p class="mt-1 text-body text-catskillwhite-800">
                    {{ collection.start ?? 'à définir' }}
                </p>
            </article>

            <article class="rounded-2xl bg-fuzzywuzzybrown-100 p-6 text-center">
                <span class="material-symbols-outlined text-fuzzywuzzybrown-700" style="font-size: 32px;" aria-hidden="true">location_on</span>
                <h3 class="mt-2 text-heading-t3 text-catskillwhite-900">Lieu</h3>
                <!-- TODO récupérer le lieu depuis la collection -->
                <p class="mt-1 text-body text-catskillwhite-800">à définir</p>
            </article>

            <article class="rounded-2xl bg-fuzzywuzzybrown-100 p-6 text-center">
                <span class="material-symbols-outlined text-fuzzywuzzybrown-700" style="font-size: 32px;" aria-hidden="true">schedule</span>
                <h3 class="mt-2 text-heading-t3 text-catskillwhite-900">Deadline</h3>
                <p class="mt-1 text-body text-catskillwhite-800">Inscriptions ouvertes</p>
            </article>
        </div>

        <!-- CTA mascots -->
        <div class="mt-12 flex flex-col items-center gap-6 sm:flex-row sm:justify-center">
            <img :src="'/img/mascots/blutly_sanguy_home.webp'" alt="" class="h-32 w-auto" />
            <div class="rounded-2xl bg-fuzzywuzzybrown-600 px-8 py-6 text-center text-white">
                <p class="text-body">Vous êtes intéressé·es ?</p>
                <button
                    type="button"
                    class="mt-3 rounded-full bg-white px-6 py-2 text-body font-semibold text-fuzzywuzzybrown-700 transition hover:bg-fuzzywuzzybrown-100"
                    @click="$emit('goToTest')"
                >
                    Tester votre éligibilité
                </button>
            </div>
        </div>
    </section>
</template>
