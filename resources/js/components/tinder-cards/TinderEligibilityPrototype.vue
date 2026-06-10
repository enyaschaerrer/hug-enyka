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

type Reason = { title: string; detail: string; status: 'warning' | 'blocker' };

const emit = defineEmits<{
    ineligible: [reasons: Reason[]];
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
    dialogue: string;
    mascot: string;
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
    dialogue: '',
    mascot: '',
    leftOutcome: { status: 'clear', label: '' },
    rightOutcome: { status: 'clear', label: '' },
};

const tinderScenario = tinderScenarioData as TinderScenario;
const items = ref<Card[]>([introCard, ...tinderScenario.cards]);
const answers = ref<TriageAnswer[]>([]);
const introSwiped = ref(false);
const introCardActive = computed(() => !introSwiped.value);
const storedSwipeRight = ref<(() => void) | null>(null);
const storedSwipeLeft = ref<(() => void) | null>(null);
const storedRestore = ref<(() => void) | null>(null);
const showMatch = ref(false);
const pendingModules = ref<string[]>([]);
const viewportWidth = ref(0);
const viewportHeight = ref(0);

const totalCards = computed(() => items.value.filter(item => item.id !== 0).length);
const answeredCount = computed(() => answers.value.length);
const blockerCount = computed(() => answers.value.filter((answer) => answer.status === 'blocker').length);
const warningCount = computed(() => answers.value.filter((answer) => answer.status === 'warning').length);
const hasMatch = computed(() => blockerCount.value === 0);
const progressSegments = computed(() => Array.from({ length: totalCards.value }, (_, index) => introSwiped.value && index <= answeredCount.value));

// Carte (question) actuellement au sommet de la pile = première non répondue
const activeItem = computed<Card | null>(() => {
    if (!introSwiped.value) return null;
    const answered = new Set(answers.value.map((a) => a.cardId));
    return tinderScenario.cards.find((c) => !answered.has(c.id)) ?? null;
});
// Mobile : la mascotte de la carte active + sa bulle
const activeMascot = computed(() => {
    const item = activeItem.value;
    if (!item) return null;
    return { image: item.mascot, dialogue: item.dialogue };
});
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

    // Cartes à critère éliminatoire (les 5 premières). On les passe TOUTES, puis on décide.
    const eligibilityIds = tinderScenario.cards
        .filter((card) => card.leftOutcome.status === 'blocker' || card.rightOutcome.status === 'blocker')
        .map((card) => card.id);
    const eligibilityDone = eligibilityIds.every((id) => answers.value.some((answer) => answer.cardId === id));

    // Fin des 5 premières : si au moins une était éliminatoire → page « Pas éligible » (on ne continue pas).
    if (eligibilityDone && answers.value.some((answer) => answer.status === 'blocker')) {
        const reasons: Reason[] = tinderScenario.cards
            .filter((card) => answers.value.some((a) => a.cardId === card.id && a.status === 'blocker'))
            .map((card) => ({
                title: card.theme,
                detail: answers.value.find((a) => a.cardId === card.id)?.label ?? '',
                status: 'blocker' as const,
            }));
        emit('ineligible', reasons);
        return;
    }

    // Sinon on continue ; à la fin du swipe → carte « match » puis chat (si modules à préciser).
    const allAnswered = answers.value.length === totalCards.value;
    if (allAnswered) {
        pendingModules.value = tinderScenario.cards
            .filter((card) => typeof card.module === 'string'
                && answers.value.some((answer) => answer.cardId === card.id && answer.status === 'warning'))
            .map((card) => card.module as string);
        showMatch.value = true;
    }
}

function continueToChat() {
    emit('match', pendingModules.value);
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
    <!-- Carte « match » : fin du swipe sans exclusion -->
    <section
        v-if="showMatch"
        class="font-cooper flex w-full items-center justify-center px-4 py-4"
        :class="props.contained ? 'min-h-0 flex-1' : 'min-h-[100svh] w-screen bg-rose-50'"
    >
        <div class="relative w-full max-w-[420px] rounded-[2rem] border-2 border-razzmatazz-900 bg-razzmatazz-200 px-6 py-10 text-center shadow-[0_24px_70px_rgba(109,0,46,0.14)]">
            <img
                :src="'/img/mascots/blutly_sanguy_love.webp'"
                alt="Match"
                class="mx-auto h-44 w-auto object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                draggable="false"
            />
            <h2 class="mt-4 text-heading-t1 font-bold text-razzmatazz-800">Tu as un match !</h2>
            <p class="mt-3 text-body leading-snug text-razzmatazz-800">
                Tu peux maintenant passer aux questions d'approfondissement sous forme d'un chat avec nos mascottes.
            </p>
            <button
                type="button"
                class="mt-6 inline-flex items-center gap-2 rounded-2xl bg-razzmatazz-800 px-8 py-3 text-base font-semibold text-white transition hover:bg-razzmatazz-600"
                @click="continueToChat"
            >
                <span>Discuter avec les mascottes</span>
            </button>
        </div>
    </section>

    <section
        v-else-if="layoutMode === 'desktop'"
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
        class="font-cooper flex w-full flex-col px-2 pt-4"
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

        <div class="relative">
            <!-- Flèche retour : coin haut-gauche de la carte -->
            <button
                v-if="!introCardActive"
                type="button"
                class="absolute left-4 top-4 z-30 inline-flex h-8 w-8 items-center justify-center rounded-full border border-razzmatazz-700/20 bg-white/90 text-razzmatazz-800 shadow transition hover:bg-white disabled:opacity-40"
                :disabled="answeredCount === 0"
                aria-label="Revenir à la carte précédente"
                @click="storedRestore?.()"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14 4 9m0 0 5-5M4 9h10.5A5.5 5.5 0 0 1 20 14.5v0A5.5 5.5 0 0 1 14.5 20H11" />
                </svg>
            </button>

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
                    @swipe-left="storedSwipeLeft?.()"
                    @swipe-right="storedSwipeRight?.()"
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

            <!-- Boutons Non/Oui = dans la carte ; flèche retour = au-dessus de la carte -->
            <template #actions="{ restore, swipeLeft, swipeRight }">
                <span
                    class="hidden"
                    :ref="() => { storedSwipeRight = swipeRight; storedSwipeLeft = swipeLeft; storedRestore = restore; }"
                />
            </template>
            </FlashCards>
        </div>

        <!-- Mascotte unique + bulle large à côté (fondu croisé à chaque changement de carte) -->
        <div class="relative mt-8 h-32">
            <Transition
                enter-active-class="transition-opacity duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-300 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="activeMascot"
                    :key="activeItem?.id"
                    class="absolute inset-0 flex items-center justify-center gap-2 px-1"
                >
                    <img :src="activeMascot.image" alt="Mascotte" class="h-20 w-auto shrink-0 object-contain" draggable="false" />
                    <div
                        v-if="activeMascot.dialogue"
                        class="mascot-bubble flex-1 rounded-2xl border border-razzmatazz-950 bg-white px-3 py-2 text-caption font-semibold leading-snug text-razzmatazz-950"
                    >
                        {{ activeMascot.dialogue }}
                    </div>
                </div>
            </Transition>
        </div>
    </section>
</template>

<style scoped>
.mascot-bubble {
    position: relative;
}

/* Pointe vers la gauche (vers la mascotte) */
.mascot-bubble::after {
    content: '';
    position: absolute;
    top: 50%;
    left: -7px;
    width: 12px;
    height: 12px;
    background: #ffffff;
    border-left: 1px solid #2f1725;
    border-bottom: 1px solid #2f1725;
    transform: translateY(-50%) rotate(45deg);
}
</style>
