<script setup lang="ts">
import { ref } from 'vue';
import { FlashCards } from 'vue3-flashcards';
import SmsConversationPrototype from '../../components/sms-chat/SmsConversationPrototype.vue';
import InteractiveWorldMap from '../../components/interactive-map/InteractiveWorldMap.vue';
import TinderActions from '../../components/tinder-cards/TinderActions.vue';
import TinderCard from '../../components/tinder-cards/TinderCard.vue';

type SwipeDirection = 'left' | 'right' | 'top';

type Card = Record<string, unknown> & {
    id: number;
    text: string;
    description: string;
    prompt: string;
    image: string;
    tag: string;
    points: number;
    tone: 'calm' | 'boost' | 'alert' | 'win';
};

const items = ref<Card[]>([
    {
        id: 1,
        text: 'Premier don',
        description: 'Tu n as jamais donne, mais tu veux aider une premiere fois.',
        prompt: 'Match si tu veux recevoir un parcours simple et rassurant.',
        image: '/img/sanguy/sanguy_happy.png',
        tag: 'Starter',
        points: 120,
        tone: 'boost',
    },
    {
        id: 2,
        text: 'Pause midi',
        description: 'Une collecte proche du travail, entre deux reunions.',
        prompt: 'Swipe oui si un creneau court te motive.',
        image: '/img/sanguy/sanguy-alt-happy.png',
        tag: 'Rapide',
        points: 90,
        tone: 'calm',
    },
    {
        id: 3,
        text: 'Voyage recent',
        description: 'Tu rentres d un pays qui peut demander un delai.',
        prompt: 'Super like pour verifier avec la carte monde apres.',
        image: '/img/sanguy/sanguy-alt-angry.png',
        tag: 'Check',
        points: 60,
        tone: 'alert',
    },
    {
        id: 4,
        text: 'Equipe solidaire',
        description: 'Tu aimerais venir avec des collegues pour te lancer.',
        prompt: 'Match si le mode groupe te donne envie.',
        image: '/img/sanguy/sanguy_happy.png',
        tag: 'Team',
        points: 150,
        tone: 'win',
    },
    {
        id: 5,
        text: 'Petit doute',
        description: 'Tu ne sais pas si tu es eligible aujourd hui.',
        prompt: 'Swipe oui pour passer au mini-chat Sanguy.',
        image: '/img/sanguy/sanguy-angry.png',
        tag: 'Question',
        points: 80,
        tone: 'alert',
    },
    {
        id: 6,
        text: 'Retour au don',
        description: 'Tu as deja donne et tu veux reprendre sans pression.',
        prompt: 'Match si un rappel clair peut t aider.',
        image: '/img/sanguy/sanguy-alt-happy.png',
        tag: 'Comeback',
        points: 110,
        tone: 'boost',
    },
    {
        id: 7,
        text: 'Objectif HUG',
        description: 'Chaque poche compte pour les patientes et patients.',
        prompt: 'Super like si tu veux viser le badge Hero du jour.',
        image: '/img/sanguy/sanguy_happy.png',
        tag: 'Impact',
        points: 200,
        tone: 'win',
    },
    {
        id: 8,
        text: 'Pas dispo',
        description: 'Aujourd hui c est complique, mais plus tard oui.',
        prompt: 'Passe la carte pour garder le parcours fluide.',
        image: '/img/sanguy/sanguy-alt-angry.png',
        tag: 'Timing',
        points: 40,
        tone: 'calm',
    },
    {
        id: 9,
        text: 'Collecte entreprise',
        description: 'Ton entreprise pourrait accueillir une action de don.',
        prompt: 'Match si tu veux imaginer une page co-brandee.',
        image: '/img/sanguy/sanguy-alt-happy.png',
        tag: 'Campus',
        points: 170,
        tone: 'boost',
    },
    {
        id: 10,
        text: 'Boss final',
        description: 'Tu es pret a continuer vers le chat et la carte.',
        prompt: 'Super like pour enchainer le parcours complet.',
        image: '/img/sanguy/sanguy_happy.png',
        tag: 'Combo',
        points: 250,
        tone: 'win',
    },
]);

function handleSwipe(item: Card, direction: SwipeDirection) {
    if (direction === 'top') {
        return;
    }

    console.info(`${direction}: ${item.text}`);
}
</script>

<template>
    <main class="min-h-screen bg-rose-50 text-stone-950">
        <header class="sticky top-0 z-40 border-b border-red-100 bg-white/90 px-4 py-3 shadow-sm backdrop-blur">
            <nav class="navbar mx-auto min-h-0 w-full max-w-6xl p-0">
                <div class="navbar-start">
                    <a class="btn btn-ghost h-auto min-h-0 gap-2 px-2 py-1 text-red-950" href="/">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-red-600 text-sm font-black text-white">H</span>
                        <span class="leading-tight">
                            <span class="block text-base font-black">HUG</span>
                            <span class="block text-xs font-semibold text-stone-500">Don du sang</span>
                        </span>
                    </a>
                </div>

                <div class="navbar-center hidden lg:flex">
                    <ul class="menu menu-horizontal gap-1 rounded-full border border-red-100 bg-rose-50 px-2 py-1">
                        <li><a class="rounded-full text-sm font-semibold text-red-950 hover:bg-white" href="/">Home</a></li>
                        <li><a class="rounded-full text-sm font-semibold text-red-950 hover:bg-white" href="/collecte">Collecte</a></li>
                        <li><a class="rounded-full text-sm font-semibold text-red-950 hover:bg-white" href="/trophee">Trophee</a></li>
                        <li><a class="rounded-full text-sm font-semibold text-red-950 hover:bg-white" href="/label">Label</a></li>
                        <li><a class="rounded-full text-sm font-semibold text-red-950 hover:bg-white" href="/contact">Contact</a></li>
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
                            <li><a href="/">Home</a></li>
                            <li><a href="/collecte">Collecte</a></li>
                            <li><a href="/trophee">Trophee</a></li>
                            <li><a href="/label">Label</a></li>
                            <li><a href="/contact">Contact</a></li>
                        </ul>
                    </div>

                    <a class="btn btn-sm rounded-full border-red-200 bg-white text-red-700 hover:border-red-300 hover:bg-red-50" href="/admin">
                        Admin
                    </a>
                </div>
            </nav>
        </header>

        <section class="mx-auto mb-12 mt-8 w-full max-w-5xl bg-rose-50 px-4">
            <div class="mx-auto mb-6 max-w-2xl text-center">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-red-500">Mini-jeu HUG</p>
                <h1 class="mt-2 text-3xl font-bold text-red-950 md:text-4xl">Swipe ton parcours avec Sanguy</h1>
                <p class="mt-3 text-base leading-relaxed text-stone-600">
                    Choisis les situations qui te ressemblent, gagne des points symboliques et prepare la suite du parcours don du sang.
                </p>
            </div>

            <div class="mx-auto w-full max-w-[430px]">
            <FlashCards
                :items="items"
                :swipe-direction="['left', 'right', 'top']"
                :swipe-threshold="140"
                :stack="3"
                :stack-offset="10"
                :stack-scale="0.03"
                @swipe-left="(item) => handleSwipe(item, 'left')"
                @swipe-right="(item) => handleSwipe(item, 'right')"
                @swipe-top="(item) => handleSwipe(item, 'top')"
            >
                <template #default="{ item }">
                    <TinderCard :item="item" />
                </template>

                <template #left="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[1.75rem] bg-white/70 backdrop-blur-[2px]"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div
                            class="-rotate-12 rounded-2xl border-4 border-red-500 bg-white px-6 py-3 text-4xl font-black uppercase text-red-600 shadow-lg"
                        >
                            Plus tard
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
                            Match
                        </div>
                    </div>
                </template>

                <template #top="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-[1.75rem] bg-red-100/85 backdrop-blur-[2px]"
                        :style="{ opacity: Math.min(Math.abs(delta), 0.92) }"
                    >
                        <div
                            class="rounded-2xl border-4 border-red-600 bg-white px-5 py-3 text-3xl font-black uppercase text-red-700 shadow-lg"
                        >
                            Hero boost
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="rounded-[1.75rem] border border-red-100 bg-white p-10 text-center text-xl font-semibold text-red-950 shadow-xl">
                        Parcours termine
                        <p class="mt-2 text-sm font-normal text-stone-500">Sanguy t attend dans le chat pour continuer.</p>
                    </div>
                </template>

                <template
                    #actions="{
                        restore,
                        swipeTop,
                        swipeLeft,
                        swipeRight,
                        swipeBottom,
                        isEnd,
                        isStart,
                        canRestore,
                    }"
                >
                    <TinderActions
                        :top="swipeTop"
                        :left="swipeLeft"
                        :right="swipeRight"
                        :bottom="swipeBottom"
                        :restore="restore"
                        :is-end="isEnd"
                        :is-start="isStart"
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
