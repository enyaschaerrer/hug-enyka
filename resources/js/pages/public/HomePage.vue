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
    image: string;
};

const items = ref<Card[]>([
    {
        id: 1,
        text: 'Mountain Adventure',
        description: 'Explore the peaks and valleys',
        image: 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=800&q=80',
    },
    {
        id: 2,
        text: 'Beach Paradise',
        description: 'Relax by the ocean',
        image: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=800&q=80',
    },
    {
        id: 3,
        text: 'City Life',
        description: 'Urban exploration',
        image: 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&q=80',
    },
    {
        id: 4,
        text: 'Forest Retreat',
        description: 'Connect with nature',
        image: 'https://images.unsplash.com/photo-1511497584788-876760111969?w=800&q=80',
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
    <main class="min-h-screen bg-base-200">
        <nav class="mx-auto mb-8 flex w-full max-w-4xl flex-wrap gap-2 px-4 pt-8">
            <a class="btn btn-sm" href="/">Home</a>
            <a class="btn btn-sm" href="/collecte">Collecte</a>
            <a class="btn btn-sm" href="/trophee">Trophee</a>
            <a class="btn btn-sm" href="/label">Label</a>
            <a class="btn btn-sm" href="/contact">Contact</a>
            <a class="btn btn-sm btn-outline" href="/admin">Admin</a>
        </nav>

        <section class="mx-auto mb-12 w-full max-w-[400px] px-4">
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
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-xl"
                        :style="{ backgroundColor: `rgba(0, 0, 0, ${Math.min(Math.abs(delta), 0.5)})` }"
                    >
                        <div
                            class="-rotate-12 rounded-lg border-4 border-error px-6 py-3 text-5xl font-black uppercase text-error shadow-lg"
                            :style="{ opacity: Math.abs(delta) }"
                        >
                            Nope
                        </div>
                    </div>
                </template>

                <template #right="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-xl"
                        :style="{ backgroundColor: `rgba(0, 0, 0, ${Math.min(Math.abs(delta), 0.5)})` }"
                    >
                        <div
                            class="rotate-12 rounded-lg border-4 border-success px-6 py-3 text-5xl font-black uppercase text-success shadow-lg"
                            :style="{ opacity: Math.abs(delta) }"
                        >
                            Like
                        </div>
                    </div>
                </template>

                <template #top="{ delta }">
                    <div
                        class="pointer-events-none absolute inset-0 z-10 flex items-center justify-center rounded-xl"
                        :style="{ backgroundColor: `rgba(0, 0, 0, ${Math.min(Math.abs(delta), 0.5)})` }"
                    >
                        <div
                            class="rounded-lg border-4 border-info px-4 py-2 text-3xl font-black uppercase text-info shadow-lg"
                            :style="{ opacity: Math.abs(delta) }"
                        >
                            Super Like
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="rounded-xl bg-base-100 p-10 text-center text-xl text-base-content shadow">
                        No more cards
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
        </section>

        <SmsConversationPrototype />

        <InteractiveWorldMap />
    </main>
</template>
