<script setup lang="ts">
import { ref, watch } from 'vue';

type TinderItem = {
    id: number;
    theme: string;
    title: string;
    question: string;
    bio: string;
    hint: string;
    image: string;
    tone: 'red' | 'green' | 'blue' | 'violet' | 'orange' | 'turquoise' | 'pink' | 'emerald';
};

const props = defineProps<{
    item: TinderItem;
    active: boolean;
    current: number;
    total: number;
}>();

const emoteAnimationKey = ref(0);

watch(
    () => props.active,
    (active) => {
        if (active) {
            emoteAnimationKey.value += 1;
        }
    },
    { immediate: true },
);

const toneClasses: Record<TinderItem['tone'], string> = {
    red: 'from-red-50 via-white to-rose-100',
    green: 'from-green-50 via-white to-emerald-50',
    blue: 'from-blue-50 via-white to-sky-50',
    violet: 'from-violet-50 via-white to-purple-50',
    orange: 'from-orange-50 via-white to-amber-50',
    turquoise: 'from-cyan-50 via-white to-teal-50',
    pink: 'from-pink-50 via-white to-rose-50',
    emerald: 'from-emerald-50 via-white to-green-50',
};

const themeClasses: Record<TinderItem['tone'], string> = {
    red: 'border-red-200 bg-red-50 text-red-700',
    green: 'border-green-200 bg-green-50 text-green-700',
    blue: 'border-blue-200 bg-blue-50 text-blue-700',
    violet: 'border-violet-200 bg-violet-50 text-violet-700',
    orange: 'border-orange-200 bg-orange-50 text-orange-700',
    turquoise: 'border-cyan-200 bg-cyan-50 text-cyan-700',
    pink: 'border-pink-200 bg-pink-50 text-pink-700',
    emerald: 'border-emerald-200 bg-emerald-50 text-emerald-700',
};

const counterClasses: Record<TinderItem['tone'], string> = {
    red: 'border-red-600 bg-red-600 text-white',
    green: 'border-green-600 bg-green-600 text-white',
    blue: 'border-blue-600 bg-blue-600 text-white',
    violet: 'border-violet-600 bg-violet-600 text-white',
    orange: 'border-orange-500 bg-orange-500 text-white',
    turquoise: 'border-cyan-600 bg-cyan-600 text-white',
    pink: 'border-pink-600 bg-pink-600 text-white',
    emerald: 'border-emerald-600 bg-emerald-600 text-white',
};

const glowClasses: Record<TinderItem['tone'], string> = {
    red: 'bg-red-300/55',
    green: 'bg-green-300/55',
    blue: 'bg-blue-300/55',
    violet: 'bg-violet-300/55',
    orange: 'bg-orange-300/55',
    turquoise: 'bg-cyan-300/55',
    pink: 'bg-pink-300/55',
    emerald: 'bg-emerald-300/55',
};
</script>

<template>
    <article
        class="font-cooper relative z-10 flex h-[540px] w-full flex-col overflow-hidden rounded-[1.75rem] border border-red-100 bg-gradient-to-br p-5 text-red-950 shadow-[0_20px_60px_rgba(127,29,29,0.12)]"
        :class="toneClasses[item.tone]"
    >
        <div class="flex items-center justify-between gap-3">
            <span class="rounded-full border-2 px-3 py-1 text-xs font-semibold uppercase tracking-wide" :class="themeClasses[item.tone]">
                <span class="cooper-baseline">{{ item.theme }}</span>
            </span>
            <span class="rounded-full border-2 px-3 py-1 text-sm font-bold tabular-nums" :class="counterClasses[item.tone]">
                <span class="cooper-baseline">{{ current }}/{{ total }}</span>
            </span>
        </div>

        <div class="relative mt-5 flex min-h-0 flex-1 items-center justify-center">
            <div class="absolute inset-x-5 bottom-10 h-24 rounded-full blur-2xl" :class="glowClasses[item.tone]"></div>
            <div :key="`${item.id}-${emoteAnimationKey}`" class="sanguy-card-emote relative flex w-full items-center justify-center">
                <img
                    class="pointer-events-none max-h-[300px] w-full select-none object-contain drop-shadow-2xl"
                    :src="item.image"
                    :alt="item.title"
                    draggable="false"
                />
            </div>
        </div>

        <div class="rounded-3xl border border-red-100 bg-white/88 p-5 shadow-[0_12px_36px_rgba(127,29,29,0.08)] backdrop-blur">
            <h2 class="text-2xl font-bold leading-tight text-red-950">
                {{ item.question }}
            </h2>
            <p class="mt-3 text-base font-normal leading-relaxed text-stone-500">{{ item.bio }}</p>
        </div>

        <div class="pointer-events-none absolute inset-0 rounded-[1.75rem] border border-white/80" />
    </article>
</template>

<style scoped>
.sanguy-card-emote {
    animation: sanguy-card-emote-in 360ms ease-out both;
    will-change: transform;
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
