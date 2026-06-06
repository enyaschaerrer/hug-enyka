<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Vue3Lottie } from 'vue3-lottie';
import swipeLottieData from '../../data/swipe-lottie.json';

type TinderItem = {
    id: number;
    theme: string;
    title: string;
    question: string;
    bio: string;
    hint: string;
    tone: 'red' | 'green' | 'blue' | 'violet' | 'orange' | 'turquoise' | 'pink' | 'emerald';
    leftDialogue: string;
    rightDialogue: string;
};

const props = defineProps<{
    item: TinderItem;
    active: boolean;
    current: number;
    total: number;
    layout?: 'compact' | 'mobile';
}>();

const emit = defineEmits<{
    introStart: [];
}>();

const emoteAnimationKey = ref(0);
const leftTypedText = ref('');
const rightTypedText = ref('');
let leftTypingTimeout: number | null = null;
let rightTypingTimeout: number | null = null;

const sanguyVariants = [
    '/img/mascots/sanguy_hero.webp',
    '/img/mascots/sanguy_thumbs_up.webp',
    '/img/mascots/sanguy_satisfied.webp',
    '/img/mascots/sanguy_above.webp',
];

const blutlyVariants = [
    '/img/mascots/blutly_hero.webp',
    '/img/mascots/blutly_thumbs_up.webp',
    '/img/mascots/blutly_above.webp',
    '/img/mascots/blutly_sanguy_hey.webp',
];

const sanguyImage = computed(() => sanguyVariants[props.item.id % sanguyVariants.length]);
const blutlyImage = computed(() => blutlyVariants[(props.item.id + 1) % blutlyVariants.length]);

const mobileMascot = computed(() => {
    if (props.item.leftDialogue) {
        return { image: sanguyImage.value, alt: 'Sanguy', isLeft: true };
    }
    if (props.item.rightDialogue) {
        return { image: blutlyImage.value, alt: 'Blutly', isLeft: false };
    }
    return { image: sanguyImage.value, alt: 'Sanguy', isLeft: true };
});

watch(
    () => props.active,
    (active) => {
        if (active) {
            emoteAnimationKey.value += 1;
            startTypewriter();
        }
    },
    { immediate: true },
);

const toneClasses: Record<TinderItem['tone'], string> = {
    red: 'bg-[#f8eef1]',
    green: 'bg-[#f8eef1]',
    blue: 'bg-[#f8eef1]',
    violet: 'bg-[#f8eef1]',
    orange: 'bg-[#f8eef1]',
    turquoise: 'bg-[#f8eef1]',
    pink: 'bg-[#f8eef1]',
    emerald: 'bg-[#f8eef1]',
};

function clearTypingTimers() {
    if (leftTypingTimeout !== null) {
        window.clearTimeout(leftTypingTimeout);
        leftTypingTimeout = null;
    }

    if (rightTypingTimeout !== null) {
        window.clearTimeout(rightTypingTimeout);
        rightTypingTimeout = null;
    }
}

function animateText(target: typeof leftTypedText, fullText: string, delay = 24) {
    let index = 0;

    const tick = () => {
        target.value = fullText.slice(0, index);
        index += 1;

        if (index <= fullText.length) {
            return window.setTimeout(tick, delay);
        }

        return null;
    };

    return tick();
}

function startTypewriter() {
    clearTypingTimers();
    leftTypedText.value = '';
    rightTypedText.value = '';
    leftTypingTimeout = animateText(leftTypedText, props.item.leftDialogue, 22);
    rightTypingTimeout = window.setTimeout(() => {
        rightTypingTimeout = animateText(rightTypedText, props.item.rightDialogue, 20);
    }, 220);
}
</script>

<template>
    <article
        class="font-cooper relative z-10 flex w-full flex-col overflow-hidden rounded-[2rem] border-2 px-4 text-red-950 shadow-[0_24px_70px_rgba(109,0,46,0.14)]"
        :class="[
            toneClasses[item.tone],
            layout === 'mobile'
                ? 'h-[28rem] pb-16 pt-4'
                : layout === 'compact'
                    ? 'h-[23rem] pb-16 pt-3'
                    : 'h-[27rem] pb-18 pt-4 sm:h-[28rem] sm:px-6 sm:pb-20 sm:pt-5 lg:h-[29rem] lg:px-7 lg:pb-22',
        ]"
        :style="{ borderColor: '#b81e62' }"
    >
        <!-- Carte intro (id === 0) -->
        <template v-if="item.id === 0">
            <!-- Mobile : lottie réduit, bouton absolu comme desktop -->
            <template v-if="layout === 'mobile'">
                <div class="absolute inset-x-0 bottom-[8.5rem] flex flex-col items-center px-4">
                    <Vue3Lottie
                        :animation-data="swipeLottieData"
                        :height="120"
                        :loop="true"
                        :auto-play="true"
                    />
                    <div class="mt-11 text-center">
                        <p class="text-xl font-bold text-[#5f0f35]">Comment ça marche ?</p>
                        <p class="mt-3 text-xs font-semibold leading-snug text-[#7a4b62]">
                            Swipez à <span class="text-[#ef4444]">gauche</span> si vous n'êtes pas concerné(e),
                            à <span class="text-[#22c55e]">droite</span> si oui.
                        </p>
                        <p class="mt-1.5 text-xs font-semibold leading-snug text-[#7a4b62]">
                            Lisez bien chaque question avant de répondre.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute bottom-8 left-1/2 -translate-x-1/2 w-44 rounded-2xl py-3 text-sm font-bold text-white transition hover:opacity-90"
                    style="background-color: #6d002e;"
                    @click="emit('introStart')"
                >
                    C'est parti !
                </button>
            </template>

            <!-- Tablet (compact) -->
            <template v-else-if="layout === 'compact'">
                <div class="flex h-full flex-col items-center pt-4">
                    <Vue3Lottie
                        :animation-data="swipeLottieData"
                        :height="100"
                        :loop="true"
                        :auto-play="true"
                        class="mt-3"
                    />
                    <div class="absolute inset-x-4 bottom-[6.5rem] px-2 text-center">
                        <p class="text-2xl font-bold text-[#5f0f35]">Comment ça marche ?</p>
                        <p class="mt-3 text-base font-semibold leading-snug text-[#7a4b62]">
                            Swipez à <span class="text-[#ef4444]">gauche</span> si vous n'êtes pas concerné(e),
                            à <span class="text-[#22c55e]">droite</span> si oui.
                        </p>
                        <p class="mt-1 text-base font-semibold leading-snug text-[#7a4b62]">
                            Lisez bien chaque question avant de répondre.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 w-44 rounded-2xl py-3 text-sm font-bold text-white transition hover:opacity-90"
                    style="background-color: #6d002e;"
                    @click="emit('introStart')"
                >
                    C'est parti !
                </button>
            </template>

            <!-- Desktop : layout original -->
            <template v-else>
                <div class="flex h-full flex-col items-center pt-4">
                    <Vue3Lottie
                        :animation-data="swipeLottieData"
                        :height="160"
                        :loop="true"
                        :auto-play="true"
                        class="mt-6"
                    />
                    <div class="absolute inset-x-4 bottom-[8rem] px-2 text-center">
                        <p class="text-3xl font-bold text-[#5f0f35]">Comment ça marche ?</p>
                        <p class="mt-4 text-lg font-semibold leading-snug text-[#7a4b62]">
                            Swipez à <span class="text-[#ef4444]">gauche</span> si vous n'êtes pas concerné(e),
                            à <span class="text-[#22c55e]">droite</span> si oui.
                        </p>
                        <p class="mt-1 text-lg font-semibold leading-snug text-[#7a4b62]">
                            Lisez bien chaque question avant de répondre.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute bottom-8 left-1/2 -translate-x-1/2 w-48 rounded-2xl py-3.5 text-base font-bold text-white transition hover:opacity-90"
                    style="background-color: #6d002e;"
                    @click="emit('introStart')"
                >
                    C'est parti !
                </button>
            </template>
        </template>

        <template v-else-if="active">
            <!-- Layout desktop / tablet : 2 mascottes côte à côte -->
            <template v-if="layout !== 'mobile'">
                <div class="mt-[15px] px-2 text-center sm:mt-[15px] sm:px-6">
                    <h2 class="text-[1.8rem] font-bold leading-[1.32] text-[#5f0f35]">
                        {{ item.question }}
                    </h2>
                </div>

                <div class="relative mt-2 flex min-h-0 flex-1 items-center justify-center">
                    <div class="grid w-full grid-cols-2 gap-3 sm:gap-4">
                        <div class="flex min-h-0 flex-col items-center justify-end">
                            <div v-if="item.leftDialogue !== ''" class="speech-bubble speech-bubble-left relative ml-auto mr-3 min-h-[52px] w-full max-w-[148px] rounded-[1.1rem] border border-[#2f1725] bg-white px-3 py-2 text-left text-[11px] font-semibold leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:min-h-[58px] sm:max-w-[168px] sm:text-[12px]">
                                <span class="invisible">{{ item.leftDialogue }}</span>
                                <span class="absolute inset-0 px-3 py-2 text-[11px] leading-snug sm:text-[12px]">{{ leftTypedText }}</span>
                            </div>
                            <div :key="`${item.id}-${emoteAnimationKey}-left`" class="sanguy-card-emote relative mt-1 flex w-full items-end justify-center" :class="layout === 'compact' ? 'h-[88px]' : 'h-[108px] sm:h-[124px] lg:h-[136px]'">
                                <img
                                    class="pointer-events-none h-full select-none object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                                    :src="sanguyImage"
                                    alt="Sanguy"
                                    draggable="false"
                                />
                            </div>
                        </div>

                        <div class="flex min-h-0 flex-col items-center justify-end">
                            <div v-if="item.rightDialogue !== ''" class="speech-bubble speech-bubble-right relative mr-auto ml-3 min-h-[52px] w-full max-w-[148px] rounded-[1.1rem] border border-[#2f1725] bg-white px-3 py-2 text-left text-[11px] font-semibold leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:min-h-[58px] sm:max-w-[168px] sm:text-[12px]">
                                <span class="invisible">{{ item.rightDialogue }}</span>
                                <span class="absolute inset-0 px-3 py-2 text-[11px] leading-snug sm:text-[12px]">{{ rightTypedText }}</span>
                            </div>
                            <div :key="`${item.id}-${emoteAnimationKey}-right`" class="sanguy-card-emote relative mt-1 flex w-full items-end justify-center" :class="layout === 'compact' ? 'h-[88px]' : 'h-[108px] sm:h-[124px] lg:h-[136px]'">
                                <img
                                    class="pointer-events-none h-full select-none object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                                    :src="blutlyImage"
                                    alt="Blutly"
                                    draggable="false"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Layout mobile : 1 mascotte centrée -->
            <template v-else>
                <div class="px-4 pt-8 text-center">
                    <h2 class="text-[1.15rem] font-bold leading-[1.3] text-[#5f0f35]">
                        {{ item.question }}
                    </h2>
                </div>

                <div class="relative flex flex-1 flex-col items-center justify-end pb-2">
                    <div
                        v-if="mobileMascot.isLeft ? item.leftDialogue : item.rightDialogue"
                        class="speech-bubble speech-bubble-center relative mb-1 min-h-[48px] w-full max-w-[160px] rounded-[1.1rem] border border-[#2f1725] bg-white px-3 py-2 text-center text-[11.5px] font-semibold leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)]"
                    >
                        <span class="invisible">{{ mobileMascot.isLeft ? item.leftDialogue : item.rightDialogue }}</span>
                        <span class="absolute inset-0 px-3 py-2 text-[11.5px] leading-snug">{{ mobileMascot.isLeft ? leftTypedText : rightTypedText }}</span>
                    </div>
                    <div :key="`${item.id}-${emoteAnimationKey}-mobile`" class="sanguy-card-emote relative mt-1 flex h-[148px] w-full items-end justify-center">
                        <img
                            class="pointer-events-none h-full select-none object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                            :src="mobileMascot.image"
                            :alt="mobileMascot.alt"
                            draggable="false"
                        />
                    </div>
                </div>
            </template>
        </template>

        <template v-else>
            <div class="h-full w-full bg-[#f8eef1]"></div>
        </template>

        <div v-if="item.id !== 0" class="absolute right-4 top-4 rounded-full bg-[#6d002e] px-2.5 py-0.5 text-xs font-bold text-white">
            {{ current }}/{{ total }}
        </div>

        <div class="pointer-events-none absolute inset-0 rounded-[1.75rem] border border-white/80" />
    </article>
</template>

<style scoped>
.sanguy-card-emote {
    animation: sanguy-card-emote-in 360ms ease-out both;
    will-change: transform;
}

.speech-bubble {
    position: relative;
}

.speech-bubble::before {
    content: '';
    position: absolute;
    bottom: -6px;
    width: 11px;
    height: 11px;
    background: #ffffff;
    border-right: 1px solid #2f1725;
    border-bottom: 1px solid #2f1725;
    transform: rotate(45deg);
}

.speech-bubble::after {
    content: '';
    position: absolute;
    bottom: 0px;
    width: 14px;
    height: 5px;
    background: #ffffff;
}

.speech-bubble-left::before {
    left: 28px;
}

.speech-bubble-left::after {
    left: 27px;
}

.speech-bubble-right::before {
    right: 28px;
}

.speech-bubble-right::after {
    right: 27px;
}

.speech-bubble-center::before {
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
}

.speech-bubble-center::after {
    left: 50%;
    transform: translateX(-50%);
}

@keyframes sanguy-card-emote-in {
    0% {
        transform: translateY(24px);
    }

    100% {
        transform: translateY(0);
    }
}

</style>
