<script setup lang="ts">
import { computed } from 'vue';

type Colors = {
    primary: string | null;
    secondary: string | null;
    third: string | null;
};

const props = defineProps<{
    collection: {
        start: string | null;
        end: string | null;
        appointmentUrl: string | null;
    };
    address: string | null;
    zipCode: string | null;
    locality: string | null;
    colors: Colors;
}>();

defineEmits<{
    goToTest: [];
}>();

const MONTHS_FR = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
];

function formatTime(d: Date): string {
    const h = d.getHours();
    const m = d.getMinutes();
    return m === 0 ? `${h}h` : `${h}h${m.toString().padStart(2, '0')}`;
}

function formatDateFr(d: Date): string {
    return `${d.getDate()} ${MONTHS_FR[d.getMonth()]} ${d.getFullYear()}`;
}

// "12 juin 2026, 13h - 17h30"
const dateLabel = computed(() => {
    if (!props.collection.start) return 'à définir';
    const start = new Date(props.collection.start);
    let result = formatDateFr(start) + ', ' + formatTime(start);
    if (props.collection.end) {
        const end = new Date(props.collection.end);
        result += ' - ' + formatTime(end);
    }
    return result;
});

const addressLabel = computed(() => {
    const parts = [props.address, [props.zipCode, props.locality].filter(Boolean).join(' ')].filter(Boolean);
    return parts.length ? parts.join(', ') : 'à définir';
});

// "Inscriptions ouvertes jusqu'au [J-1 du début]" — ou "Inscriptions fermées" si la collecte a commencé
const deadlineLabel = computed(() => {
    if (!props.collection.start) return 'à définir';
    const start = new Date(props.collection.start);
    if (Date.now() >= start.getTime()) {
        return 'Inscriptions fermées';
    }
    const deadline = new Date(start);
    deadline.setDate(deadline.getDate() - 1);
    return `Inscriptions ouvertes jusqu'au ${formatDateFr(deadline)}`;
});

const cards = computed(() => [
    { icon: 'calendar_today', title: 'Date',     text: dateLabel.value },
    { icon: 'location_on',    title: 'Lieu',     text: addressLabel.value },
    { icon: 'schedule',       title: 'Deadline', text: deadlineLabel.value },
]);

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
</script>

<template>
    <section class="mx-auto max-w-5xl px-6 py-10">
        <h2 class="text-left text-[2.2rem] font-bold text-catskillwhite-900 sm:text-center sm:text-display lg:text-display">
            Votre <span :style="primaryTextStyle">entreprise</span> se mobilise pour le don du sang
        </h2>

        <button
            type="button"
            class="rounded-2xl bg-razzmatazz-800 px-8 py-4 mt-5 mx-auto table text-heading-t3 text-white transition hover:bg-razzmatazz-600 lg:hidden lg:px-10"
            @click="$emit('goToTest')"
        >
            <div class="flex items-center justify-center gap-1">
                <span class="material-symbols-outlined" style="font-size: 24px;" aria-hidden="true">Quiz</span>
                Tester votre éligibilité
            </div>
        </button>

        <!-- 3 cards Date / Lieu / Deadline -->
        <div class="mt-10 grid gap-4 sm:grid-cols-3">
            <article
                v-for="(card, idx) in cards"
                :key="idx"
                class="rounded-2xl border-2 p-6"
                :style="cardStyle"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-white"
                        :style="iconBubbleStyle"
                    >
                        <span class="material-symbols-outlined" style="font-size: 24px;" aria-hidden="true">{{ card.icon }}</span>
                    </div>
                    <h3 class="text-[0.9rem] font-medium text-catskillwhite-900 lg:text-heading-t3">{{ card.title }}</h3>
                </div>
                <p class="mt-3 text-[0.9rem] text-catskillwhite-800 lg:text-body">{{ card.text }}</p>
            </article>
        </div>

        <!-- CTA mascots — texte + bouton en haut, mascottes en dessous -->
        <div class="mt-16 flex flex-col items-center gap-6">
            <p class="text-heading-t2 text-catskillwhite-900 hidden lg:block">Vous êtes intéressé·e·s ?</p>
            <button
                type="button"
                class="rounded-2xl bg-razzmatazz-800 px-10 py-4 text-heading-t3 text-white transition hover:bg-razzmatazz-600 hidden lg:block"
                @click="$emit('goToTest')"
            >
                Tester votre éligibilité
            </button>

            <div class="mt-2 flex justify-center hidden lg:block">
                <img :src="'/img/mascots/blutly_sanguy_hey.webp'" alt="" class="h-32 w-auto sm:h-40" />
            </div>
        </div>
    </section>
</template>
