<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { FlashCards } from 'vue3-flashcards';
import tinderScenarioData from '../../data/tinder-scenario.json';
import TinderActions from './TinderActions.vue';
import TinderCard from './TinderCard.vue';

const props = withDefaults(defineProps<{
    contained?: boolean;
}>(), {
    contained: false,
});

const emit = defineEmits<{
    ineligible: [];
}>();

type SwipeDirection = 'left' | 'right';
type TriageStatus = 'clear' | 'warning' | 'blocker';

type Card = Record<string, unknown> & {
    id: number;
    theme: string;
    title: string;
    question: string;
    bio: string;
    hint: string;
    image: string;
    tone: 'red' | 'green' | 'blue' | 'violet' | 'orange' | 'turquoise' | 'pink' | 'emerald';
    leftDialogue: string;
    rightDialogue: string;
    leftOutcome: {
        status: TriageStatus;
        label: string;
    };
    rightOutcome: {
        status: TriageStatus;
        label: string;
    };
};

type TinderScenario = {
    title: string;
    cards: Card[];
};

type TriageAnswer = {
    cardId: number;
    direction: SwipeDirection;
    status: TriageStatus;
    label: string;
};

const tinderScenario = tinderScenarioData as TinderScenario;
const items = ref<Card[]>(tinderScenario.cards);
const answers = ref<TriageAnswer[]>([]);
const viewportWidth = ref(0);
const viewportHeight = ref(0);
const totalCards = computed(() => items.value.length);
const answeredCount = computed(() => answers.value.length);
const blockerCount = computed(() => answers.value.filter((answer) => answer.status === 'blocker').length);
const warningCount = computed(() => answers.value.filter((answer) => answer.status === 'warning').length);
const hasMatch = computed(() => blockerCount.value === 0);
const progressSegments = computed(() => Array.from({ length: totalCards.value }, (_, index) => index <= answeredCount.value));
const layoutMode = computed<'mobile' | 'tablet' | 'desktop'>(() => {
    const width = viewportWidth.value;
    const height = viewportHeight.value;

    if (width >= 1100 && height >= 700) {
        return 'desktop';
    }

    if (width >= 768 && height >= 560) {
        return 'tablet';
    }

    return 'mobile';
});

function syncViewport() {
    viewportWidth.value = window.innerWidth;
    viewportHeight.value = window.innerHeight;
}

function handleSwipe(item: Card, direction: SwipeDirection) {
    const outcome = direction === 'right' ? item.rightOutcome : item.leftOutcome;
    answers.value = [
        ...answers.value.filter((answer) => answer.cardId !== item.id),
        {
            cardId: item.id,
            direction,
            status: outcome.status,
            label: outcome.label,
        },
    ];

    if (outcome.status === 'blocker') {
        emit('ineligible');
    }
}

function handleRestore(item: Card) {
    answers.value = answers.value.filter((answer) => answer.cardId !== item.id);
}

function getCardPosition(item: Card) {
    return Math.max(1, items.value.findIndex((card) => card.id === item.id) + 1);
}

onMounted(() => {
    syncViewport();
    window.addEventListener('resize', syncViewport);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', syncViewport);
});
</script>

<template>
    <section
        v-if="layoutMode === 'desktop'"
        class="font-cooper flex w-full items-center px-3 py-2 sm:px-4 sm:py-3"
        :class="props.contained ? 'min-h-0 w-full bg-transparent pb-0' : 'min-h-[100svh] w-screen bg-rose-50 pb-12'"
    >
        <div class="relative mx-auto w-full max-w-[680px]" :class="props.contained ? '' : '-mt-10 sm:-mt-12'">
            <div class="mb-[33px] flex items-center justify-center gap-2 px-6 sm:mb-[34px] sm:gap-3 sm:px-10">
                <span
                    v-for="(isCompleted, index) in progressSegments"
                    :key="index"
                    class="h-2 flex-1 rounded-full transition-colors duration-200 sm:h-2.5"
                    :class="isCompleted ? 'bg-[#6d002e]' : 'bg-[#f4b5ca]'"
                ></span>
            </div>

            <FlashCards
                :items="items"
                :swipe-direction="['left', 'right']"
                :swipe-threshold="140"
                :stack="3"
                :stack-offset="6"
                :stack-scale="0.018"
                @swipe-left="(item) => handleSwipe(item, 'left')"
                @swipe-right="(item) => handleSwipe(item, 'right')"
                @restore="handleRestore"
            >
                <template #default="{ item, activeItemKey }">
                    <TinderCard
                        :item="item"
                        :active="item.id === activeItemKey"
                        :current="getCardPosition(item)"
                        :total="totalCards"
                    />
                </template>

                <template #left="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/25"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div
                            class="-rotate-12 inline-flex items-center rounded-2xl border-4 border-[#ef4444] bg-white px-3 py-1.5 text-xl font-bold leading-none uppercase text-[#ef4444] shadow-lg sm:px-5 sm:py-2.5 sm:text-3xl"
                        >
                            <span>C'est faux</span>
                        </div>
                    </div>
                </template>

                <template #right="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/25"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div
                            class="inline-flex rotate-12 items-center rounded-2xl border-4 border-[#22c55e] bg-white px-3 py-1.5 text-xl font-bold leading-none uppercase text-[#22c55e] shadow-lg sm:px-5 sm:py-2.5 sm:text-3xl"
                        >
                            <span>Je valide</span>
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="rounded-[2rem] border-2 border-[#b81e62] bg-[#f9edf0] p-8 text-center text-red-950 shadow-[0_24px_70px_rgba(109,0,46,0.14)]">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl"
                            :class="hasMatch ? 'bg-white text-[#6d002e]' : 'bg-white text-[#cc4d7d]'"
                        >
                            <span class="text-3xl font-bold">{{ hasMatch ? 'OK' : '!' }}</span>
                        </div>
                        <h2 class="text-2xl font-bold leading-tight text-[#5f0f35]">
                            {{ hasMatch ? 'Match pour continuer' : 'Pas de match pour le moment' }}
                        </h2>
                        <p class="mt-3 text-sm font-normal leading-relaxed text-[#7a4b62]">
                            {{
                                hasMatch
                                    ? warningCount > 0
                                        ? 'Sanguy a repere quelques points a clarifier. Le SMS peut prendre le relais.'
                                        : 'Aucun point bloquant dans ce premier tri. Tu peux passer au SMS.'
                                    : 'Un ou plusieurs points demandent de reporter ou de verifier avant de poursuivre.'
                            }}
                        </p>
                        <div class="mt-5 flex justify-center gap-2">
                            <span class="badge border-[#f68eaf] bg-white font-medium text-[#cc4d7d]">{{ blockerCount }} blocage</span>
                            <span class="badge border-[#6d002e] bg-white font-medium text-[#6d002e]">{{ warningCount }} a verifier</span>
                        </div>
                    </div>
                </template>

                <template
                    #actions="{
                        restore,
                        swipeLeft,
                        swipeRight,
                        isEnd,
                        canRestore,
                    }"
                >
                    <TinderActions
                        :left="swipeLeft"
                        :right="swipeRight"
                        :restore="restore"
                        :is-end="isEnd"
                        :can-restore="canRestore"
                    />
                </template>
            </FlashCards>
        </div>
    </section>
</template>
