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
    match: [modules: string[]];
}>();

type SwipeDirection = 'left' | 'right';
type TriageStatus = 'clear' | 'warning' | 'blocker';

type Card = Record<string, unknown> & {
    id: number;
    module?: string;
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

const introCard: Card = {
    id: 0,
    theme: '',
    title: '',
    question: '',
    bio: '',
    hint: '',
    image: '',
    tone: 'red',
    leftDialogue: '',
    rightDialogue: '',
    leftOutcome: { status: 'clear', label: '' },
    rightOutcome: { status: 'clear', label: '' },
};

const tinderScenario = tinderScenarioData as TinderScenario;
const items = ref<Card[]>([introCard, ...tinderScenario.cards]);
const answers = ref<TriageAnswer[]>([]);
const introSwiped = ref(false);
const introCardActive = computed(() => !introSwiped.value);
const storedSwipeRight = ref<(() => void) | null>(null);
const viewportWidth = ref(0);
const viewportHeight = ref(0);
const totalCards = computed(() => items.value.filter(item => item.id !== 0).length);
const answeredCount = computed(() => answers.value.length);
const blockerCount = computed(() => answers.value.filter((answer) => answer.status === 'blocker').length);
const warningCount = computed(() => answers.value.filter((answer) => answer.status === 'warning').length);
const hasMatch = computed(() => blockerCount.value === 0);
const progressSegments = computed(() => Array.from({ length: totalCards.value }, (_, index) => introSwiped.value && index <= answeredCount.value));
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
    if (item.id === 0) {
        introSwiped.value = true;
        return;
    }

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
        return;
    }

    const allAnswered = answers.value.length === totalCards.value;
    if (allAnswered) {
        // Modules de messagerie « enregistrés » : cartes swipées vers un avertissement (oui),
        // dans l'ordre des cartes du scénario.
        const registeredModules = tinderScenario.cards
            .filter((card) => typeof card.module === 'string'
                && answers.value.some((answer) => answer.cardId === card.id && answer.status === 'warning'))
            .map((card) => card.module as string);
        emit('match', registeredModules);
    }
}

function handleRestore(item: Card) {
    answers.value = answers.value.filter((answer) => answer.cardId !== item.id);
}

function getCardPosition(item: Card) {
    return Math.max(1, items.value.findIndex((card) => card.id === item.id));
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
                        @intro-start="storedSwipeRight?.()"
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
                            <span>NON</span>
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
                            <span>OUI</span>
                        </div>
                    </div>
                </template>


                <template #empty>
                    <div />
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
                    <div :ref="() => storedSwipeRight = swipeRight">
                        <TinderActions
                            v-if="!introCardActive"
                            :left="swipeLeft"
                            :right="swipeRight"
                            :restore="restore"
                            :is-end="isEnd"
                            :can-restore="canRestore && answeredCount > 0"
                        />
                    </div>
                </template>
            </FlashCards>
        </div>
    </section>

    <section
        v-else-if="layoutMode === 'tablet'"
        class="font-cooper flex w-full items-center px-3 py-2"
        :class="props.contained ? 'min-h-0 w-full bg-transparent pb-0' : 'min-h-[100svh] w-screen bg-rose-50 pb-12'"
    >
        <div class="relative mx-auto w-full max-w-[600px]" :class="props.contained ? '' : '-mt-8'">
            <div class="mb-[20px] flex items-center justify-center gap-2 px-6">
                <span
                    v-for="(isCompleted, index) in progressSegments"
                    :key="index"
                    class="h-2 flex-1 rounded-full transition-colors duration-200"
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
                        layout="compact"
                        @intro-start="storedSwipeRight?.()"
                    />
                </template>

                <template #left="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/25"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div class="-rotate-12 inline-flex items-center rounded-2xl border-4 border-[#ef4444] bg-white px-3 py-1.5 text-xl font-bold leading-none uppercase text-[#ef4444] shadow-lg">
                            <span>NON</span>
                        </div>
                    </div>
                </template>

                <template #right="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/25"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div class="inline-flex rotate-12 items-center rounded-2xl border-4 border-[#22c55e] bg-white px-3 py-1.5 text-xl font-bold leading-none uppercase text-[#22c55e] shadow-lg">
                            <span>OUI</span>
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div />
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
                    <div :ref="() => storedSwipeRight = swipeRight">
                        <TinderActions
                            v-if="!introCardActive"
                            :left="swipeLeft"
                            :right="swipeRight"
                            :restore="restore"
                            :is-end="isEnd"
                            :can-restore="canRestore && answeredCount > 0"
                        />
                    </div>
                </template>
            </FlashCards>
        </div>
    </section>

    <section
        v-else
        class="font-cooper flex w-full flex-col px-4 pt-3"
        :class="props.contained ? 'min-h-0 w-full bg-transparent' : 'min-h-[100svh] w-screen bg-rose-50 pb-6'"
    >
        <div class="mb-[22px] flex items-center justify-center gap-1.5 px-2">
            <span
                v-for="(isCompleted, index) in progressSegments"
                :key="index"
                class="h-1.5 flex-1 rounded-full transition-colors duration-200"
                :class="isCompleted ? 'bg-[#6d002e]' : 'bg-[#f4b5ca]'"
            ></span>
        </div>

        <FlashCards
            :items="items"
            :swipe-direction="['left', 'right']"
            :swipe-threshold="100"
            :stack="3"
            :stack-offset="5"
            :stack-scale="0.015"
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
                    layout="mobile"
                    @intro-start="storedSwipeRight?.()"
                />
            </template>

            <template #left="{ delta }">
                <div
                    class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/25"
                    :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                >
                    <div class="-rotate-12 inline-flex items-center rounded-2xl border-4 border-[#ef4444] bg-white px-3 py-1.5 text-lg font-bold leading-none uppercase text-[#ef4444] shadow-lg">
                        <span>NON</span>
                    </div>
                </div>
            </template>

            <template #right="{ delta }">
                <div
                    class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[2rem] bg-white/25"
                    :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                >
                    <div class="inline-flex rotate-12 items-center rounded-2xl border-4 border-[#22c55e] bg-white px-3 py-1.5 text-lg font-bold leading-none uppercase text-[#22c55e] shadow-lg">
                        <span>OUI</span>
                    </div>
                </div>
            </template>

            <template #empty>
                <div />
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
                <div :ref="() => storedSwipeRight = swipeRight">
                    <TinderActions
                        v-if="!introCardActive"
                        :left="swipeLeft"
                        :right="swipeRight"
                        :restore="restore"
                        :is-end="isEnd"
                        :can-restore="canRestore && answeredCount > 0"
                        pill
                    />
                </div>
            </template>
        </FlashCards>
    </section>
</template>
