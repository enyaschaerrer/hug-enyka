<script setup lang="ts">
type TinderItem = {
    id: number;
    text: string;
    description: string;
    image: string;
};

defineProps<{
    item: TinderItem;
}>();
</script>

<template>
    <article
        class="relative z-10 h-[500px] w-full overflow-hidden rounded-xl border border-white/10 bg-cover bg-center shadow-2xl"
        :style="{ backgroundImage: `url(${item.image})` }"
    >
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent p-5 text-white">
            <h2 class="mb-2 text-2xl font-bold drop-shadow-lg">
                {{ item.text }}
            </h2>
            <p class="text-base drop-shadow-md">
                {{ item.description }}
            </p>
        </div>

        <div class="pointer-events-none absolute inset-0 rounded-xl border border-white/20" />
    </article>
</template>
