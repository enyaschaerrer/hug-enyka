<script setup lang="ts">
import { computed, ref } from 'vue';
import { FlashCards } from 'vue3-flashcards';
import tinderScenarioData from '../../data/tinder-scenario.json';
import SmsConversationPrototype from '../../components/sms-chat/SmsConversationPrototype.vue';
import InteractiveWorldMap from '../../components/interactive-map/InteractiveWorldMap.vue';
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

const navItems = [
    { label: 'Home', href: '/' },
    { label: 'Collecte', href: '/collecte' },
    { label: 'Trophee', href: '/trophee' },
    { label: 'Label', href: '/label' },
    { label: 'Contact', href: '/contact' },
];

const currentPath = window.location.pathname;

function isActivePath(href: string) {
    return currentPath === href;
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
        <header class="sticky top-0 z-40 border-b border-red-100 bg-white/90 px-4 py-3 shadow-sm backdrop-blur">
            <nav class="navbar mx-auto min-h-0 w-full max-w-6xl p-0">
                <div class="navbar-start">
                    <a class="inline-flex items-center gap-3" href="/" aria-label="Accueil">
                        <img class="h-9 w-auto object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
                        <span class="text-sm font-black uppercase text-stone-400">X</span>
                        <img class="h-11 w-auto object-contain" :src="'/img/logo_heig-vd.png'" alt="HEIG-VD" />
                    </a>
                </div>

                <div class="navbar-center hidden lg:flex">
                    <ul class="menu menu-horizontal gap-1 rounded-full border border-red-100 bg-rose-50 px-2 py-1">
                        <li v-for="item in navItems" :key="item.href">
                            <a
                                class="rounded-full text-sm font-semibold text-red-950 hover:bg-white"
                                :class="isActivePath(item.href) ? 'bg-white shadow-sm' : ''"
                                :href="item.href"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="navbar-end gap-2">
                    <div class="dropdown dropdown-end lg:hidden">
                        <button class="btn btn-circle btn-ghost text-red-950" type="button" aria-label="Ouvrir le menu" tabindex="0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                        <ul class="menu dropdown-content z-50 mt-3 w-52 rounded-2xl border border-red-100 bg-white p-2 shadow-xl" tabindex="0">
                            <li v-for="item in navItems" :key="item.href">
                                <a :class="isActivePath(item.href) ? 'bg-rose-50 font-bold text-red-700' : ''" :href="item.href">
                                    {{ item.label }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <a class="btn btn-sm rounded-full border-red-200 bg-white text-red-700 hover:border-red-300 hover:bg-red-50" href="/admin">
                        <svg xmlns="http://www.w3.org/2000/svg" class="lucide lucide-user-round-cog-icon lucide-user-round-cog h-4 w-4" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m14.305 19.53.923-.382"/>
                            <path d="m15.228 16.852-.923-.383"/>
                            <path d="m16.852 15.228-.383-.923"/>
                            <path d="m16.852 20.772-.383.924"/>
                            <path d="m19.148 15.228.383-.923"/>
                            <path d="m19.53 21.696-.382-.924"/>
                            <path d="M2 21a8 8 0 0 1 10.434-7.62"/>
                            <path d="m20.772 16.852.924-.383"/>
                            <path d="m20.772 19.148.924.383"/>
                            <circle cx="10" cy="8" r="5"/>
                            <circle cx="18" cy="18" r="3"/>
                        </svg>
                        Admin
                    </a>
                </div>
            </nav>
        </header>

        <section class="mx-auto flex min-h-[100svh] w-full max-w-5xl items-center bg-rose-50 px-4 pb-12 pt-0">
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
                            class="-rotate-12 rounded-2xl border-4 border-red-500 bg-white px-6 py-3 text-4xl font-black uppercase text-red-600 shadow-lg"
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
                            class="rotate-12 rounded-2xl border-4 border-emerald-500 bg-white px-6 py-3 text-4xl font-black uppercase text-emerald-600 shadow-lg"
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
                            <span class="text-3xl font-black">{{ hasMatch ? 'OK' : '!' }}</span>
                        </div>
                        <h2 class="text-2xl font-black">
                            {{ hasMatch ? 'Match pour continuer' : 'Pas de match pour le moment' }}
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-stone-600">
                            {{
                                hasMatch
                                    ? warningCount > 0
                                        ? 'Sanguy a repere quelques points a clarifier. Le SMS peut prendre le relais.'
                                        : 'Aucun point bloquant dans ce premier tri. Tu peux passer au SMS.'
                                    : 'Un ou plusieurs points demandent de reporter ou de verifier avant de poursuivre.'
                            }}
                        </p>
                        <div class="mt-5 flex justify-center gap-2">
                            <span class="badge badge-error badge-outline">{{ blockerCount }} blocage</span>
                            <span class="badge badge-warning badge-outline">{{ warningCount }} a verifier</span>
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
