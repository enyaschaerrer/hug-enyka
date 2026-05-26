<script setup lang="ts">
import { computed, ref } from 'vue';
import { FlashCards } from 'vue3-flashcards';
import tinderScenarioData from '../../data/tinder-scenario.json';
import SmsConversationPrototype from '../../components/sms-chat/SmsConversationPrototype.vue';
import InteractiveWorldMap from '../../components/interactive-map/InteractiveWorldMap.vue';
import PublicHeader from '../../components/public/PublicHeader.vue';
import TinderActions from '../../components/tinder-cards/TinderActions.vue';
import TinderCard from '../../components/tinder-cards/TinderCard.vue';

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
const totalCards = computed(() => items.value.length);
const blockerCount = computed(() => answers.value.filter((answer) => answer.status === 'blocker').length);
const warningCount = computed(() => answers.value.filter((answer) => answer.status === 'warning').length);
const hasMatch = computed(() => blockerCount.value === 0);

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
}

function handleRestore(item: Card) {
    answers.value = answers.value.filter((answer) => answer.cardId !== item.id);
}

function getCardPosition(item: Card) {
    return Math.max(1, items.value.findIndex((card) => card.id === item.id) + 1);
}
</script>

<template>
    <main class="min-h-screen bg-rose-50 text-stone-950">
        <PublicHeader />

        <section class="font-cooper mx-auto flex min-h-[100svh] w-full max-w-5xl items-center bg-rose-50 px-4 pb-12 pt-0">
            <div class="relative mx-auto w-full max-w-[430px]">
            <FlashCards
                :items="items"
                :swipe-direction="['left', 'right']"
                :swipe-threshold="140"
                :stack="3"
                :stack-offset="10"
                :stack-scale="0.03"
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
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[1.75rem] bg-white/70 backdrop-blur-[2px]"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div
                            class="cooper-baseline -rotate-12 rounded-2xl border-4 border-red-500 bg-white px-6 py-3 text-4xl font-bold uppercase text-red-600 shadow-lg"
                        >
                            C'est faux
                        </div>
                    </div>
                </template>

                <template #right="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[1.75rem] bg-red-50/80 backdrop-blur-[2px]"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div
                            class="cooper-baseline rotate-12 rounded-2xl border-4 border-emerald-500 bg-white px-6 py-3 text-4xl font-bold uppercase text-emerald-600 shadow-lg"
                        >
                            Je valide
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="rounded-[1.75rem] border border-red-100 bg-white p-8 text-center text-red-950 shadow-[0_20px_60px_rgba(127,29,29,0.12)]">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl"
                            :class="hasMatch ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600'"
                        >
                            <span class="cooper-baseline text-3xl font-bold">{{ hasMatch ? 'OK' : '!' }}</span>
                        </div>
                        <h2 class="text-2xl font-bold leading-tight">
                            {{ hasMatch ? 'Match pour continuer' : 'Pas de match pour le moment' }}
                        </h2>
                        <p class="mt-3 text-sm font-normal leading-relaxed text-stone-600">
                            {{
                                hasMatch
                                    ? warningCount > 0
                                        ? 'Sanguy a repere quelques points a clarifier. Le SMS peut prendre le relais.'
                                        : 'Aucun point bloquant dans ce premier tri. Tu peux passer au SMS.'
                                    : 'Un ou plusieurs points demandent de reporter ou de verifier avant de poursuivre.'
                            }}
                        </p>
                        <div class="mt-5 flex justify-center gap-2">
                            <span class="badge badge-error badge-outline font-medium">{{ blockerCount }} blocage</span>
                            <span class="badge badge-warning badge-outline font-medium">{{ warningCount }} a verifier</span>
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

        <SmsConversationPrototype />

        <InteractiveWorldMap />
    </main>
</template>
