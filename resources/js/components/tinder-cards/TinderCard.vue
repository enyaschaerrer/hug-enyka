<script setup lang="ts">
import { computed, ref, watch } from 'vue';

type TinderItem = {
    id: number;
    theme: string;
    title: string;
    question: string;
    bio: string;
    hint: string;
    tone: 'red' | 'green' | 'blue' | 'violet' | 'orange' | 'turquoise' | 'pink' | 'emerald';
};

const props = defineProps<{
    item: TinderItem;
    active: boolean;
    current: number;
    total: number;
}>();

const emoteAnimationKey = ref(0);
const leftBubbleText = 'Placeholder dialogue placeholder dialogue.';
const rightBubbleText = 'Placeholder dialogue placeholder dialogue.';
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
    leftTypingTimeout = animateText(leftTypedText, leftBubbleText, 22);
    rightTypingTimeout = window.setTimeout(() => {
        rightTypingTimeout = animateText(rightTypedText, rightBubbleText, 20);
    }, 220);
}
</script>

<template>
    <article
        class="font-cooper relative z-10 flex h-[27rem] w-full flex-col overflow-hidden rounded-[2rem] border-2 px-4 pb-18 pt-4 text-red-950 shadow-[0_24px_70px_rgba(109,0,46,0.14)] sm:h-[28rem] sm:px-6 sm:pb-20 sm:pt-5 lg:h-[29rem] lg:px-7 lg:pb-22"
        :class="toneClasses[item.tone]"
        :style="{ borderColor: '#b81e62' }"
    >
        <template v-if="active">
            <div class="mt-1 px-2 text-center sm:mt-2 sm:px-6">
                <h2 class="text-[clamp(1.35rem,2.4vw,2.35rem)] font-bold leading-[1.05] text-[#5f0f35]">
                    {{ item.question }}
                </h2>
                <p class="mt-1.5 text-[12px] leading-relaxed text-[#7a4b62] sm:text-[13px]">{{ item.hint }}</p>
            </div>

            <div class="relative mt-2 flex min-h-0 flex-1 items-center justify-center">
                <div class="grid w-full grid-cols-2 gap-3 sm:gap-4">
                    <div class="flex min-h-0 flex-col items-center justify-end">
                        <div class="speech-bubble speech-bubble-left min-h-[52px] w-full max-w-[148px] rounded-[1.1rem] border border-[#2f1725] bg-white px-3 py-2 text-left text-[11px] leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:min-h-[58px] sm:max-w-[168px] sm:text-[12px]">
                            <span>{{ leftTypedText }}</span>
                            <span class="type-cursor" aria-hidden="true"></span>
                        </div>
                        <div :key="`${item.id}-${emoteAnimationKey}-left`" class="sanguy-card-emote relative mt-1 flex h-[72px] w-full items-end justify-center sm:h-[88px] lg:h-[96px]">
                            <img
                                class="pointer-events-none h-full select-none object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                                :src="sanguyImage"
                                alt="Sanguy"
                                draggable="false"
                            />
                        </div>
                    </div>

                    <div class="flex min-h-0 flex-col items-center justify-end">
                        <div class="speech-bubble speech-bubble-right min-h-[52px] w-full max-w-[148px] rounded-[1.1rem] border border-[#2f1725] bg-white px-3 py-2 text-left text-[11px] leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:min-h-[58px] sm:max-w-[168px] sm:text-[12px]">
                            <span>{{ rightTypedText }}</span>
                            <span class="type-cursor" aria-hidden="true"></span>
                        </div>
                        <div :key="`${item.id}-${emoteAnimationKey}-right`" class="sanguy-card-emote relative mt-1 flex h-[72px] w-full items-end justify-center sm:h-[88px] lg:h-[96px]">
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

        <template v-else>
            <div class="flex h-full flex-col">
                <div class="pointer-events-none mt-6 flex flex-1 flex-col justify-between">
                    <div class="mx-auto h-10 w-[40%] rounded-full border border-[#b81e62]/45 bg-white/40"></div>
                    <div class="space-y-3 px-4 sm:px-8">
                        <div class="mx-auto h-6 w-[88%] rounded-full bg-[#b81e62]/10"></div>
                        <div class="mx-auto h-6 w-[72%] rounded-full bg-[#b81e62]/10"></div>
                        <div class="mt-6 grid grid-cols-2 gap-4 sm:gap-8">
                            <div class="h-20 rounded-[1.4rem] border border-[#2f1725]/25 bg-white/65"></div>
                            <div class="h-20 rounded-[1.4rem] border border-[#2f1725]/25 bg-white/65"></div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-8 px-6 sm:px-10">
                            <div class="h-28 rounded-[2rem] bg-[#b81e62]/8"></div>
                            <div class="h-28 rounded-[2rem] bg-[#b81e62]/8"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 rounded-[1.6rem] border border-[#ffffff] bg-white p-3 sm:gap-4 sm:p-4">
                        <div class="min-h-[58px] rounded-[1.05rem] bg-[#f6c0d0]/85"></div>
                        <div class="min-h-[58px] rounded-[1.05rem] bg-[#6d002e]/18"></div>
                    </div>
                </div>
            </div>
        </template>

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

.speech-bubble::after {
    content: '';
    position: absolute;
    bottom: -12px;
    width: 0;
    height: 0;
    border-top: 14px solid white;
}

.speech-bubble::before {
    content: '';
    position: absolute;
    bottom: -14px;
    width: 0;
    height: 0;
    border-top: 16px solid #2f1725;
}

.speech-bubble-left::before,
.speech-bubble-left::after {
    left: 20px;
    border-right: 8px solid transparent;
}

.speech-bubble-right::before,
.speech-bubble-right::after {
    right: 20px;
    border-left: 8px solid transparent;
}

.type-cursor {
    display: inline-block;
    height: 1em;
    width: 0.08em;
    margin-left: 0.12em;
    vertical-align: -0.12em;
    background: currentColor;
    animation: type-cursor-blink 1s steps(1, end) infinite;
}

@keyframes sanguy-card-emote-in {
    0% {
        transform: translateY(24px);
    }

    100% {
        transform: translateY(0);
    }
}

@keyframes type-cursor-blink {
    0%,
    49% {
        opacity: 1;
    }

    50%,
    100% {
        opacity: 0;
    }
}
</style>
