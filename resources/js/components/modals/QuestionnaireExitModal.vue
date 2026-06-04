<script setup lang="ts">
defineProps<{
    open: boolean;
}>();

defineEmits<{
    close: [];
    confirm: [];
}>();
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-[1000] grid place-items-center bg-black/70 px-6"
            role="dialog"
            aria-modal="true"
            data-theme="light"
        >
            <button class="absolute inset-0 cursor-default" type="button" aria-label="Fermer" @click="$emit('close')"></button>

            <div class="relative w-full max-w-md rounded-[2rem] bg-white p-6 text-center text-catskillwhite-900 shadow-2xl sm:p-8">
                <div class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <span class="material-symbols-outlined text-[30px]" aria-hidden="true">warning</span>
                </div>

                <p class="font-cooper text-[1.35rem] font-semibold leading-tight text-catskillwhite-900">
                    Vous vous apprêtez à quitter le questionnaire, votre progression ne sera pas sauvegardée.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <button
                        type="button"
                        class="rounded-full border border-catskillwhite-300 px-6 py-3 text-sm font-semibold text-catskillwhite-900 transition hover:bg-catskillwhite-50"
                        @click="$emit('close')"
                    >
                        Retour au formulaire
                    </button>
                    <button
                        type="button"
                        class="rounded-full bg-catskillwhite-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-catskillwhite-900"
                        @click="$emit('confirm')"
                    >
                        Quitter
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
