<script setup lang="ts">
defineProps<{
    left: () => void;
    right: () => void;
    restore: () => void;
    isEnd: boolean;
    canRestore: boolean;
}>();
</script>

<template>
    <div class="relative mt-7 flex items-center justify-center">
        <button
            class="btn absolute left-1/2 h-11 w-11 -translate-x-[8.5rem] rounded-xl border border-red-100 bg-white/70 p-0 text-red-950 shadow-sm shadow-red-100 transition hover:-translate-y-0.5 hover:border-red-200 hover:bg-white disabled:translate-y-0 disabled:opacity-25"
            type="button"
            :disabled="!canRestore"
            aria-label="Revenir a la carte precedente"
            @click="restore"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14 4 9m0 0 5-5M4 9h10.5A5.5 5.5 0 0 1 20 14.5v0A5.5 5.5 0 0 1 14.5 20H11" />
            </svg>
        </button>

        <div class="flex items-center justify-center gap-5">
            <button
                class="btn h-20 w-20 rounded-3xl border-2 border-red-200 bg-white p-3 shadow-xl shadow-red-100 transition hover:-translate-y-1 hover:rotate-[-2deg] hover:border-red-300 hover:bg-red-50 disabled:translate-y-0 disabled:rotate-0 disabled:opacity-35"
                type="button"
                :disabled="isEnd"
                aria-label="Refuser la carte"
                @click="left"
            >
                <img class="pointer-events-none h-full w-full select-none object-contain" :src="'/img/neg-button.png'" alt="" draggable="false" />
            </button>

            <button
                class="btn h-20 w-20 rounded-3xl border-2 border-emerald-200 bg-white p-3 shadow-xl shadow-emerald-100 transition hover:-translate-y-1 hover:rotate-2 hover:border-emerald-300 hover:bg-emerald-50 disabled:translate-y-0 disabled:rotate-0 disabled:opacity-35"
                type="button"
                :disabled="isEnd"
                aria-label="Valider la carte"
                @click="right"
            >
                <img class="pointer-events-none h-full w-full select-none object-contain" :src="'/img/pos-button.png'" alt="" draggable="false" />
            </button>
        </div>
    </div>
</template>
