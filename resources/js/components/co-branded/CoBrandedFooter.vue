<script setup lang="ts">
import { onMounted, ref } from 'vue';

const dialogEl = ref<HTMLDialogElement | null>(null);

onMounted(() => {
    if (window.matchMedia('(min-width: 1024px)').matches) return;
    if (localStorage.getItem('cobranded-disclaimer-seen') === '1') return;
    dialogEl.value?.showModal();
});

function dismiss() {
    dialogEl.value?.close();
    localStorage.setItem('cobranded-disclaimer-seen', '1');
}
</script>

<template>
    <footer class="bg-catskillwhite-200 px-6 py-3 text-caption text-catskillwhite-800">
        <div class="mx-auto flex max-w-6xl flex-col gap-1 text-left sm:flex-row sm:items-center sm:justify-between sm:gap-4">
            <p>
                Vous naviguez sur un projet réalisé dans le cadre d'un cours étudiant à la HEIG-VD. Aucune donnée n'est officielle.
            </p>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
                <a href="mailto:laurent.berthelot@heig-vd.ch" class="underline hover:text-catskillwhite-900">
                    laurent.berthelot@heig-vd.ch
                </a>
                <a href="https://www.hug.ch/don-du-sang" target="_blank" rel="noopener" class="underline hover:text-catskillwhite-900">
                    Site officiel du CTS
                </a>
            </div>
        </div>
    </footer>

    <!-- Popup mobile premier chargement (lg:hidden, état mémorisé dans localStorage) -->
    <dialog
        ref="dialogEl"
        class="m-auto max-w-xs rounded-2xl border border-catskillwhite-300 bg-white p-6 text-catskillwhite-900 backdrop:bg-black/50 lg:hidden"
    >
        <div class="flex flex-col gap-4">
            <h2 class="text-heading-t3 font-semibold text-catskillwhite-900">Projet d'étudiants</h2>
            <p class="text-body">
                Vous naviguez sur un projet réalisé dans le cadre d'un cours étudiant à la HEIG-VD.
                Aucune donnée n'est officielle.
            </p>
            <div class="flex flex-col gap-1 text-caption">
                <a href="mailto:laurent.berthelot@heig-vd.ch" class="underline hover:text-catskillwhite-700">
                    laurent.berthelot@heig-vd.ch
                </a>
                <a href="https://www.hug.ch/don-du-sang" target="_blank" rel="noopener" class="underline hover:text-catskillwhite-700">
                    Site officiel du CTS
                </a>
            </div>
            <button
                type="button"
                class="mt-2 rounded-full bg-catskillwhite-800 px-5 py-2 text-body font-semibold text-white transition hover:bg-catskillwhite-900"
                @click="dismiss"
            >
                J'ai compris
            </button>
        </div>
    </dialog>
</template>
