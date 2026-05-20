<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { gsap } from 'gsap';

const title = ref<HTMLElement | null>(null);

onMounted(() => {
    gsap.from(title.value, {
        y: 16,
        opacity: 0,
        duration: 0.55,
        ease: 'power2.out',
    });
});
</script>

<template>
    <section class="flex min-h-40 items-center justify-center px-6 py-10">
        <h1
            ref="title"
            class="btn btn-primary text-3xl font-bold shadow-lg"
        >
            Bonjour
        </h1>
    </section>
</template>
