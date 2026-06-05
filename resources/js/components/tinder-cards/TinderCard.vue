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
    leftDialogue: string;
    rightDialogue: string;
};

const props = defineProps<{
    item: TinderItem;
    active: boolean;
    current: number;
    total: number;
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
        class="font-cooper relative z-10 flex h-[27rem] w-full flex-col overflow-hidden rounded-[2rem] border-2 px-4 pb-18 pt-4 text-red-950 shadow-[0_24px_70px_rgba(109,0,46,0.14)] sm:h-[28rem] sm:px-6 sm:pb-20 sm:pt-5 lg:h-[29rem] lg:px-7 lg:pb-22"
        :class="toneClasses[item.tone]"
        :style="{ borderColor: '#b81e62' }"
    >
        <template v-if="active">
            <div class="mt-[15px] px-2 text-center sm:mt-[15px] sm:px-6">
                <h2 class="text-[1.8rem] font-bold leading-[1.32] text-[#5f0f35]">
                    {{ item.question }}
                </h2>
            </div>

            <div class="relative mt-2 flex min-h-0 flex-1 items-center justify-center">
                <div class="grid w-full grid-cols-2 gap-3 sm:gap-4">
                    <div class="flex min-h-0 flex-col items-center justify-end">
                        <div v-if="item.leftDialogue !== 'Dialogue manquant'" class="speech-bubble speech-bubble-left relative min-h-[52px] w-full max-w-[148px] rounded-[1.1rem] border border-[#2f1725] bg-[#f8eef1] px-3 py-2 text-left text-[11px] leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:min-h-[58px] sm:max-w-[168px] sm:text-[12px]">
                            <span class="invisible">{{ item.leftDialogue }}</span>
                            <span class="absolute inset-0 px-3 py-2 text-[11px] leading-snug sm:text-[12px]">{{ leftTypedText }}</span>
                        </div>
                        <div :key="`${item.id}-${emoteAnimationKey}-left`" class="sanguy-card-emote relative mt-1 flex h-[108px] w-full items-end justify-center sm:h-[124px] lg:h-[136px]">
                            <img
                                class="pointer-events-none h-full select-none object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                                :src="sanguyImage"
                                alt="Sanguy"
                                draggable="false"
                            />
                        </div>
                    </div>

                    <div class="flex min-h-0 flex-col items-center justify-end">
                        <div v-if="item.rightDialogue !== 'Dialogue manquant'" class="speech-bubble speech-bubble-right relative min-h-[52px] w-full max-w-[148px] rounded-[1.1rem] border border-[#2f1725] bg-[#f8eef1] px-3 py-2 text-left text-[11px] leading-snug text-[#2f1725] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:min-h-[58px] sm:max-w-[168px] sm:text-[12px]">
                            <span class="invisible">{{ item.rightDialogue }}</span>
                            <span class="absolute inset-0 px-3 py-2 text-[11px] leading-snug sm:text-[12px]">{{ rightTypedText }}</span>
                        </div>
                        <div :key="`${item.id}-${emoteAnimationKey}-right`" class="sanguy-card-emote relative mt-1 flex h-[108px] w-full items-end justify-center sm:h-[124px] lg:h-[136px]">
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
            <div class="h-full w-full bg-[#f8eef1]"></div>
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

.speech-bubble::before {
    content: '';
    position: absolute;
    bottom: -6px;
    width: 11px;
    height: 11px;
    background: #f8eef1;
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
    background: #f8eef1;
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

@keyframes sanguy-card-emote-in {
    0% {
        transform: translateY(24px);
    }

    100% {
        transform: translateY(0);
    }
}

</style>
