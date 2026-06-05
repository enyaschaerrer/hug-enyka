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
    <div class="relative z-20 mt-[-3.8rem] flex w-full items-center justify-center px-3 sm:mt-[-4.4rem] sm:px-6">
        <button
            class="btn btn-ghost absolute -top-8 right-3 h-8 w-8 rounded-full border border-[#b81e62]/20 bg-white/90 p-0 text-[#7d2d53] shadow-[0_8px_18px_rgba(109,0,46,0.08)] transition hover:-translate-y-0.5 hover:bg-white hover:text-[#5f0f35] disabled:border-[#b81e62]/10 disabled:bg-white/60 disabled:text-[#b894a8] disabled:opacity-100 sm:right-6 sm:h-9 sm:w-9"
            type="button"
            :disabled="!canRestore"
            aria-label="Revenir a la carte precedente"
            @click="restore"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14 4 9m0 0 5-5M4 9h10.5A5.5 5.5 0 0 1 20 14.5v0A5.5 5.5 0 0 1 14.5 20H11" />
            </svg>
        </button>

        <div class="grid w-full max-w-[560px] grid-cols-2 gap-2.5 rounded-[1.35rem] border border-white/65 bg-white/28 p-2.5 backdrop-blur-sm sm:gap-3 sm:p-3">
            <button
                class="group inline-flex min-h-[50px] items-center justify-center gap-2 rounded-[0.95rem] border border-[#f6a0bb] bg-[#f68eaf] px-3 py-3 text-left text-[#5f0f35] shadow-[0_14px_28px_rgba(246,142,175,0.24)] transition-transform hover:-translate-y-0.5 disabled:opacity-35 sm:min-h-[56px] sm:px-5"
                type="button"
                :disabled="isEnd"
                aria-label="Refuser la carte"
                @click="left"
            >
                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:scale-110 sm:text-[22px]" aria-hidden="true">close</span>
                <span class="text-sm font-semibold sm:text-base">Non</span>
            </button>

            <button
                class="group inline-flex min-h-[50px] items-center justify-center gap-2 rounded-[0.95rem] border border-[#6d002e] bg-[#6d002e] px-3 py-3 text-left text-white shadow-[0_14px_28px_rgba(109,0,46,0.24)] transition-transform hover:-translate-y-0.5 disabled:opacity-35 sm:min-h-[56px] sm:px-5"
                type="button"
                :disabled="isEnd"
                aria-label="Valider la carte"
                @click="right"
            >
                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:scale-110 sm:text-[22px]" aria-hidden="true">check</span>
                <span class="text-sm font-semibold sm:text-base">Oui</span>
            </button>
        </div>
    </div>
</template>
