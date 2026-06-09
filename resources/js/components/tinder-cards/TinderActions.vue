<script setup lang="ts">
defineProps<{
    left: () => void;
    right: () => void;
    restore: () => void;
    isEnd: boolean;
    canRestore: boolean;
    pill?: boolean;
    restoreOnly?: boolean;
}>();
</script>

<template>
    <div
        class="relative z-20 flex w-full items-center justify-center px-4"
        :class="restoreOnly ? '-mt-4' : 'mt-[-4.9rem] sm:mt-[-5.4rem] sm:px-8'"
    >
        <button
            class="btn btn-ghost h-7 w-7 rounded-full border border-[var(--color-razzmatazz-700)]/20 bg-white/90 p-0 text-[var(--color-razzmatazz-800)] shadow-[0_8px_18px_rgba(109,0,46,0.08)] transition hover:-translate-y-0.5 hover:bg-white hover:text-[var(--color-razzmatazz-950)] disabled:border-[var(--color-razzmatazz-700)]/10 disabled:bg-white/60 disabled:text-[var(--color-razzmatazz-300)] disabled:opacity-100 sm:h-8 sm:w-8"
            :class="restoreOnly ? '' : 'absolute -top-8 left-4 sm:left-8'"
            type="button"
            :disabled="!canRestore"
            aria-label="Revenir a la carte precedente"
            @click="restore"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14 4 9m0 0 5-5M4 9h10.5A5.5 5.5 0 0 1 20 14.5v0A5.5 5.5 0 0 1 14.5 20H11" />
            </svg>
        </button>

        <div v-if="!restoreOnly" class="grid w-full max-w-[520px] grid-cols-2 gap-3 sm:max-w-[560px] sm:gap-4">
            <button
                :class="pill ? 'rounded-full' : 'rounded-[0.95rem]'"
            class="group inline-flex min-h-[50px] items-center justify-center gap-2 bg-[var(--color-razzmatazz-300)] px-3 py-3 text-left text-[var(--color-razzmatazz-950)] shadow-[0_14px_28px_rgba(246,142,175,0.24)] transition-transform hover:-translate-y-0.5 disabled:opacity-35 sm:min-h-[56px] sm:px-5"
                type="button"
                :disabled="isEnd"
                aria-label="Refuser la carte"
                @click="left"
            >
                <span class="material-symbols-outlined text-[20px] transition-transform group-hover:scale-110 sm:text-[22px]" aria-hidden="true">close</span>
                <span class="text-sm font-semibold sm:text-base">Non</span>
            </button>

            <button
                :class="pill ? 'rounded-full' : 'rounded-[0.95rem]'"
            class="group inline-flex min-h-[50px] items-center justify-center gap-2 bg-[var(--color-razzmatazz-900)] px-3 py-3 text-left text-white shadow-[0_14px_28px_rgba(109,0,46,0.24)] transition-transform hover:-translate-y-0.5 disabled:opacity-35 sm:min-h-[56px] sm:px-5"
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
