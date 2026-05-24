<script setup lang="ts">
defineProps<{
    top: () => void;
    left: () => void;
    right: () => void;
    bottom: () => void;
    restore: () => void;
    isEnd: boolean;
    isStart: boolean;
    canRestore: boolean;
}>();
</script>

<template>
    <div class="mt-6 grid grid-cols-5 items-center gap-2 rounded-3xl border border-red-100 bg-white p-2 shadow-lg">
        <button
            class="btn btn-circle border-stone-200 bg-white text-xs font-bold text-stone-600 hover:border-stone-300 hover:bg-stone-50"
            type="button"
            :disabled="!canRestore"
            aria-label="Restore previous card"
            @click="restore"
        >
            Back
        </button>

        <button
            class="btn btn-circle border-red-200 bg-red-50 text-xs font-bold text-red-700 hover:border-red-300 hover:bg-red-100"
            type="button"
            :disabled="isEnd"
            aria-label="Swipe left"
            @click="left"
        >
            Non
        </button>

        <button
            class="btn btn-circle border-red-500 bg-red-600 text-xs font-black text-white shadow-md shadow-red-200 hover:border-red-600 hover:bg-red-700"
            type="button"
            :disabled="isEnd"
            aria-label="Swipe top"
            @click="top"
        >
            Hero
        </button>

        <button
            class="btn btn-circle border-emerald-200 bg-emerald-50 text-xs font-bold text-emerald-700 hover:border-emerald-300 hover:bg-emerald-100"
            type="button"
            :disabled="isEnd"
            aria-label="Swipe right"
            @click="right"
        >
            Oui
        </button>

        <button
            class="btn btn-circle border-stone-200 bg-white text-xs font-bold text-stone-600 hover:border-stone-300 hover:bg-stone-50"
            type="button"
            :disabled="isStart || isEnd"
            aria-label="Swipe bottom"
            @click="bottom"
        >
            Passer
        </button>
    </div>
</template>
