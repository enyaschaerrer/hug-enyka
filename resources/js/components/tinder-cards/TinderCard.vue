<script setup lang="ts">
import { ref, watch } from 'vue';
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
    dialogue: string;
    mascot: string;
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
    swipeLeft: [];
    swipeRight: [];
}>();

const emoteAnimationKey = ref(0);
const typedText = ref('');
let typingTimeout: number | null = null;

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
    red: 'bg-[var(--color-razzmatazz-50)]',
    green: 'bg-[var(--color-razzmatazz-50)]',
    blue: 'bg-[var(--color-razzmatazz-50)]',
    violet: 'bg-[var(--color-razzmatazz-50)]',
    orange: 'bg-[var(--color-razzmatazz-50)]',
    turquoise: 'bg-[var(--color-razzmatazz-50)]',
    pink: 'bg-[var(--color-razzmatazz-50)]',
    emerald: 'bg-[var(--color-razzmatazz-50)]',
};

function startTypewriter() {
    if (typingTimeout !== null) {
        window.clearTimeout(typingTimeout);
        typingTimeout = null;
    }

    typedText.value = '';
    const fullText = props.item.dialogue;
    let index = 0;

    const tick = () => {
        typedText.value = fullText.slice(0, index);
        index += 1;
        typingTimeout = index <= fullText.length ? window.setTimeout(tick, 22) : null;
    };

    tick();
}
</script>

<template>
    <article
        class="font-cooper relative z-10 flex w-full flex-col overflow-hidden rounded-[2rem] border-2 px-4 text-razzmatazz-900 shadow-[0_24px_70px_rgba(109,0,46,0.14)]"
        :class="[
            toneClasses[item.tone],
            layout === 'mobile'
                ? 'h-[16rem] pb-6 pt-6'
                : layout === 'compact'
                    ? 'h-[23rem] pb-16 pt-3'
                    : 'h-[27rem] pb-18 pt-4 sm:h-[28rem] sm:px-6 sm:pb-20 sm:pt-5 lg:h-[29rem] lg:px-7 lg:pb-22',
        ]"
        :style="{ borderColor: 'var(--color-razzmatazz-900)' }"
    >
        <!-- Carte intro (id === 0) -->
        <template v-if="item.id === 0">
            <!-- Mobile : intro centrée -->
            <template v-if="layout === 'mobile'">
                <div class="flex h-full flex-col items-center justify-start px-4 pt-4 text-center">
                    <Vue3Lottie
                        :animation-data="swipeLottieData"
                        :height="84"
                        :loop="true"
                        :auto-play="true"
                    />
                    <p class="mt-2 text-heading-t1 font-bold text-[var(--color-razzmatazz-950)]">Comment ça marche ?</p>
                    <p class="mt-2 text-body font-semibold leading-snug text-[var(--color-razzmatazz-900)]">
                        Touchez <span class="text-[var(--color-geraldine-500)]">Non</span> si vous n'êtes pas concerné(e),
                        <span class="text-[var(--color-vistablue-500)]">Oui</span> si oui.
                    </p>
                    <button
                        type="button"
                        class="mt-4 w-44 rounded-2xl py-3 text-sm font-bold text-white transition hover:opacity-90"
                        style="background-color: var(--color-razzmatazz-900);"
                        @click="emit('introStart')"
                    >
                        C'est parti !
                    </button>
                </div>
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
                        <p class="text-2xl font-bold text-[var(--color-razzmatazz-950)]">Comment ça marche ?</p>
                        <p class="mt-3 text-base font-semibold leading-snug text-[var(--color-razzmatazz-900)]">
                            Swipez à <span class="text-[var(--color-geraldine-500)]">gauche</span> si vous souhaitez répondre non,
                            à <span class="text-[var(--color-vistablue-500)]">droite</span> si oui.
                        </p>
                        <p class="mt-1 text-base font-semibold leading-snug text-[var(--color-razzmatazz-900)]">
                            Lisez bien chaque question avant de répondre.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute bottom-6 left-1/2 -translate-x-1/2 w-44 rounded-2xl py-3 text-sm font-bold text-white transition hover:opacity-90"
                    style="background-color: var(--color-razzmatazz-900);"
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
                        <p class="text-3xl font-bold text-[var(--color-razzmatazz-950)]">Comment ça marche ?</p>
                        <p class="mt-4 text-lg font-semibold leading-snug text-[var(--color-razzmatazz-900)]">
                            Swipez à <span class="text-[var(--color-geraldine-500)]">gauche</span> si vous souhaitez répondre non,
                            à <span class="text-[var(--color-vistablue-500)]">droite</span> si oui.
                        </p>
                        <p class="mt-1 text-lg font-semibold leading-snug text-[var(--color-razzmatazz-900)]">
                            Lisez bien chaque question avant de répondre.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute bottom-8 left-1/2 -translate-x-1/2 w-48 rounded-2xl py-3.5 text-base font-bold text-white transition hover:opacity-90"
                    style="background-color: var(--color-razzmatazz-900);"
                    @click="emit('introStart')"
                >
                    C'est parti !
                </button>
            </template>
        </template>

        <template v-else-if="active">
            <!-- Layout desktop / tablet : question centrée dans l'espace dispo, mascotte en bas -->
            <template v-if="layout !== 'mobile'">
                <div class="flex flex-1 items-center justify-center px-2 text-center sm:px-6">
                    <h2 class="text-heading-t1 font-bold leading-[1.32] text-[var(--color-razzmatazz-950)]">
                        {{ item.question }}
                    </h2>
                </div>

                <div class="relative flex flex-col items-center">
                    <div v-if="item.dialogue !== ''" class="speech-bubble speech-bubble-center relative mb-3 min-h-[52px] w-full max-w-[320px] shrink-0 rounded-[1.1rem] border border-[var(--color-razzmatazz-950)] bg-white px-4 py-2 text-center text-[12px] font-medium leading-snug text-[var(--color-razzmatazz-950)] shadow-[0_10px_24px_rgba(47,23,37,0.08)] sm:text-[13px]">
                        {{ typedText }}
                    </div>
                    <div :key="`${item.id}-${emoteAnimationKey}-emote`" class="sanguy-card-emote relative flex shrink-0 items-end justify-center" :class="layout === 'compact' ? 'h-[92px]' : 'h-[104px] sm:h-[118px] lg:h-[128px]'">
                        <img
                            class="pointer-events-none h-full select-none object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                            :src="item.mascot"
                            alt="Mascotte"
                            draggable="false"
                        />
                    </div>
                </div>
            </template>

            <!-- Layout mobile : hauteur fixe, question centrée, boutons en bas -->
            <template v-else>
                <div class="flex h-full flex-col">
                    <div class="flex flex-1 items-center justify-center px-3 pt-3 text-center">
                        <h2 class="text-heading-t3 font-bold leading-snug text-[var(--color-razzmatazz-950)]">
                            {{ item.question }}
                        </h2>
                    </div>

                    <div class="flex justify-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-2xl bg-[var(--color-razzmatazz-300)] px-6 py-3 text-body font-semibold text-[var(--color-razzmatazz-950)] transition hover:-translate-y-0.5"
                            @click="emit('swipeLeft')"
                        >
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">close</span>
                            <span>Non</span>
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-2xl bg-[var(--color-razzmatazz-900)] px-6 py-3 text-body font-semibold text-white transition hover:-translate-y-0.5"
                            @click="emit('swipeRight')"
                        >
                            <span class="material-symbols-outlined text-[20px]" aria-hidden="true">check</span>
                            <span>Oui</span>
                        </button>
                    </div>
                </div>
            </template>
        </template>

        <template v-else>
            <div class="h-full w-full bg-[var(--color-razzmatazz-50)]"></div>
        </template>

        <div v-if="item.id !== 0" class="absolute right-4 top-4 rounded-full bg-[var(--color-razzmatazz-900)] px-2.5 py-0.5 text-xs font-bold text-white">
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
    border-right: 1px solid var(--color-razzmatazz-950);
    border-bottom: 1px solid var(--color-razzmatazz-950);
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
