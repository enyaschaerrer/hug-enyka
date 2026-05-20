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
    <div class="mt-6 flex items-center justify-center gap-3">
        <button
            class="btn btn-circle btn-outline"
            type="button"
            :disabled="!canRestore"
            aria-label="Restore previous card"
            @click="restore"
        >
            Retour
        </button>

        <button
            class="btn btn-circle btn-error text-error-content"
            type="button"
            :disabled="isEnd"
            aria-label="Swipe left"
            @click="left"
        >
            Non
        </button>

        <button
            class="btn btn-circle btn-info text-info-content"
            type="button"
            :disabled="isEnd"
            aria-label="Swipe top"
            @click="top"
        >
            Top
        </button>

        <button
            class="btn btn-circle btn-success text-success-content"
            type="button"
            :disabled="isEnd"
            aria-label="Swipe right"
            @click="right"
        >
            Oui
        </button>

        <button
            class="btn btn-circle btn-outline"
            type="button"
            :disabled="isStart || isEnd"
            aria-label="Swipe bottom"
            @click="bottom"
        >
            Passer
        </button>
    </div>
</template>
