<script setup lang="ts">
type TinderItem = {
    id: number;
    text: string;
    description: string;
    prompt: string;
    image: string;
    tag: string;
    points: number;
    tone: 'calm' | 'boost' | 'alert' | 'win';
};

defineProps<{
    item: TinderItem;
}>();

const toneClasses: Record<TinderItem['tone'], string> = {
    calm: 'from-white via-red-50 to-white',
    boost: 'from-red-50 via-white to-rose-100',
    alert: 'from-white via-orange-50 to-red-100',
    win: 'from-red-100 via-white to-emerald-50',
};

const badgeClasses: Record<TinderItem['tone'], string> = {
    calm: 'border-stone-200 bg-white text-stone-700',
    boost: 'border-red-200 bg-red-50 text-red-700',
    alert: 'border-orange-200 bg-orange-50 text-orange-700',
    win: 'border-emerald-200 bg-emerald-50 text-emerald-700',
};
</script>

<template>
    <article
        class="relative z-10 flex h-[540px] w-full flex-col overflow-hidden rounded-[1.75rem] border border-red-100 bg-gradient-to-br p-5 text-red-950 shadow-2xl"
        :class="toneClasses[item.tone]"
    >
        <div class="flex items-center justify-between gap-3">
            <span class="rounded-full border px-3 py-1 text-xs font-bold uppercase tracking-wide" :class="badgeClasses[item.tone]">
                {{ item.tag }}
            </span>
            <span class="rounded-full bg-red-950 px-3 py-1 text-sm font-black text-white">
                +{{ item.points }}
            </span>
        </div>

        <div class="relative mt-5 flex min-h-0 flex-1 items-center justify-center">
            <div class="absolute inset-x-5 bottom-10 h-24 rounded-full bg-red-200/60 blur-2xl"></div>
            <img
                :key="item.id"
                class="sanguy-card-emote pointer-events-none relative max-h-[300px] w-full select-none object-contain drop-shadow-2xl"
                :src="item.image"
                :alt="item.text"
                draggable="false"
            />
        </div>

        <div class="rounded-3xl border border-red-100 bg-white/88 p-5 shadow-lg backdrop-blur">
            <h2 class="text-3xl font-black leading-tight text-red-950">
                {{ item.text }}
            </h2>
            <p class="mt-2 text-base leading-relaxed text-stone-600">
                {{ item.description }}
            </p>
            <div class="mt-4 rounded-2xl bg-red-50 px-4 py-3 text-sm font-semibold leading-relaxed text-red-900">
                {{ item.prompt }}
            </div>
        </div>

        <div class="pointer-events-none absolute inset-0 rounded-[1.75rem] border border-white/80" />
    </article>
</template>

<style scoped>
.sanguy-card-emote {
    animation: sanguy-card-emote-in 360ms ease-out both;
}

@keyframes sanguy-card-emote-in {
    from {
        opacity: 0;
        transform: translateY(18px) scale(0.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
