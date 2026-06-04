<script setup lang="ts">
type Colors = {
    primary: string | null;
    secondary: string | null;
    third: string | null;
};

const props = defineProps<{
    companyName: string;
    collection: {
        start: string | null;
        end: string | null;
        appointmentUrl: string | null;
    };
    colors: Colors;
}>();

defineEmits<{
    goToTest: [];
}>();

const cards = [
    { icon: 'calendar_today', title: 'Date',     text: null },
    { icon: 'location_on',    title: 'Lieu',     text: 'à définir' },
    { icon: 'schedule',       title: 'Deadline', text: 'Inscriptions ouvertes' },
];

const cardStyle = {
    backgroundColor: props.colors.third ?? 'var(--color-razzmatazz-100)',
    borderColor:     props.colors.secondary ?? 'var(--color-razzmatazz-300)',
};

const iconBubbleStyle = {
    backgroundColor: props.colors.primary ?? 'var(--color-razzmatazz-700)',
};

const primaryTextStyle = {
    color: props.colors.primary ?? 'var(--color-razzmatazz-700)',
};

const ctaStyle = {
    backgroundColor: props.colors.primary ?? 'var(--color-razzmatazz-700)',
};
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10">
        <h2 class="text-center text-display text-catskillwhite-900">
            Votre <span :style="primaryTextStyle">entreprise</span> se mobilise pour le don du sang
        </h2>

        <!-- 3 cards Date / Lieu / Deadline -->
        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <article
                v-for="(card, idx) in cards"
                :key="idx"
                class="rounded-2xl border-2 p-6 text-center"
                :style="cardStyle"
            >
                <div
                    class="mx-auto inline-flex h-12 w-12 items-center justify-center rounded-full text-white"
                    :style="iconBubbleStyle"
                >
                    <span class="material-symbols-outlined" style="font-size: 28px;" aria-hidden="true">{{ card.icon }}</span>
                </div>
                <h3 class="mt-3 text-heading-t3 text-catskillwhite-900">{{ card.title }}</h3>
                <p class="mt-1 text-body text-catskillwhite-800">
                    <template v-if="card.title === 'Date'">
                        <!-- TODO formatter collection.start / collection.end -->
                        {{ collection.start ?? 'à définir' }}
                    </template>
                    <template v-else>{{ card.text }}</template>
                </p>
            </article>
        </div>

        <!-- CTA mascots -->
        <div class="mt-12 flex flex-col items-center gap-6 sm:flex-row sm:justify-center">
            <img :src="'/img/mascots/blutly_sanguy_home.webp'" alt="" class="h-32 w-auto" />
            <div class="rounded-2xl px-8 py-6 text-center text-white" :style="ctaStyle">
                <p class="text-body">Vous êtes intéressé·es ?</p>
                <button
                    type="button"
                    class="mt-3 rounded-full bg-white px-6 py-2 text-body font-semibold transition hover:opacity-90"
                    :style="primaryTextStyle"
                    @click="$emit('goToTest')"
                >
                    Tester votre éligibilité
                </button>
            </div>
        </div>
    </section>
</template>
