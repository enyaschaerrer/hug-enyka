<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { getCookieConsentPreferences, hasCookieConsentDecision, saveCookieConsentPreferences } from '../../services/cookieConsent';
import type { CookieConsentCategory, CookieConsentPreferences } from '../../types/cookie-consent';

const categories: CookieConsentCategory[] = [
    {
        id: 'necessary',
        title: 'Cookies nécessaires',
        description: 'Ils permettent au site de fonctionner et de garder en mémoire vos préférences.',
        required: true,
    },
    {
        id: 'analytics',
        title: 'Mesure d’audience anonyme',
        description: 'Ces données anonymes aident les HUG à améliorer l’efficacité des collectes de sang.',
        required: false,
    },
];

const isOpen = ref(false);
const view = ref<'summary' | 'settings'>('summary');
const analyticsEnabled = ref(false);
const hasDecision = ref(false);
const toggleTransition = ref(false);
let previousBodyOverflow = '';

const modalTitle = computed(() => (view.value === 'summary' ? 'Aidez-nous à améliorer l’expérience de collecte !' : 'Configurer les cookies'));

function syncPreferences(preferences: CookieConsentPreferences | null = getCookieConsentPreferences()) {
    hasDecision.value = preferences !== null;
    analyticsEnabled.value = preferences?.analytics ?? false;
}

function openSettings() {
    view.value = 'settings';
}

function openModal() {
    syncPreferences();
    view.value = hasDecision.value ? 'settings' : 'summary';
    isOpen.value = true;
}

function closeModal() {
    if (!hasDecision.value) {
        return;
    }

    isOpen.value = false;
}

function acceptAnalytics() {
    syncPreferences(saveCookieConsentPreferences(true));
    isOpen.value = false;
}

function saveSettings() {
    syncPreferences(saveCookieConsentPreferences(analyticsEnabled.value));
    isOpen.value = false;
}

function handleConsentChange(event: Event) {
    syncPreferences((event as CustomEvent<CookieConsentPreferences>).detail);
}

function lockBodyScroll() {
    previousBodyOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
}

function unlockBodyScroll() {
    document.body.style.overflow = previousBodyOverflow;
}

onMounted(() => {
    syncPreferences();

    if (!hasCookieConsentDecision()) {
        isOpen.value = true;
    }

    window.addEventListener('cookie-consent:changed', handleConsentChange);
});

watch(isOpen, (isModalOpen) => {
    if (isModalOpen) {
        lockBodyScroll();
        toggleTransition.value = false;
        setTimeout(() => {
            toggleTransition.value = true;
        }, 50);
        return;
    }

    unlockBodyScroll();
});

onBeforeUnmount(() => {
    window.removeEventListener('cookie-consent:changed', handleConsentChange);
    unlockBodyScroll();
});
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
            v-if="isOpen"
            class="fixed inset-0 z-[1000] grid place-items-end justify-items-start bg-transparent p-3 sm:p-8"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="'cookie-consent-title'"
            data-theme="light"
        >
            <button
                class="absolute inset-0 cursor-default bg-black/70"
                type="button"
                aria-label="Fermer les préférences cookies"
                @click="closeModal"
            ></button>

            <div class="modal-box relative max-h-[calc(100svh-1.5rem)] w-[calc(100vw-1.5rem)] max-w-md scale-100 overflow-y-auto rounded-3xl bg-white p-4 text-stone-950 opacity-100 shadow-2xl translate-0 sm:max-h-[calc(100svh-4rem)] sm:p-5">
                <h2 id="cookie-consent-title" class="cooper-text-baseline mt-2 text-center font-cooper text-[1.55rem] font-semibold leading-tight text-base-content sm:text-[1.65rem]">
                    {{ modalTitle }}
                </h2>

                <template v-if="view === 'summary'">
                    <div class="mt-6 space-y-3 rounded-2xl bg-black/[0.02] p-4 font-cooper text-sm leading-relaxed text-base-content/75">
                        <p class="cooper-text-baseline">
                            Les cookies nécessaires gardent le site fonctionnel. Avec votre accord, une mesure d’audience anonyme aidera les HUG à comprendre ce qui facilite le passage à l’inscription et à améliorer les prochaines collectes. Cette mesure reste désactivée sans votre accord, et vous pouvez changer d’avis à tout moment.
                        </p>
                    </div>

                    <div class="modal-action mt-6 grid gap-3 sm:grid-cols-2">
                        <button
                            class="btn h-[48px] rounded-2xl border-base-300 bg-white font-cooper text-[0.95rem] text-base-content"
                            type="button"
                            @click="openSettings"
                        >
                            <span class="cooper-baseline">Configurer</span>
                        </button>
                        <button
                            class="btn h-[48px] rounded-2xl border-none font-cooper text-[0.95rem] text-white transition-[filter] duration-150 hover:brightness-90 ease-in-out"
                            style="background-color: #5a579e;"
                            type="button"
                            @click="acceptAnalytics"
                        >
                            <span class="cooper-baseline">Je consens</span>
                        </button>
                    </div>
                </template>

                <template v-else>
                    <div class="mt-6 space-y-3">
                        <section
                            v-for="category in categories"
                            :key="category.id"
                            class="rounded-2xl border border-base-300 bg-black/[0.02] p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="cooper-text-baseline font-cooper text-base font-semibold leading-tight text-base-content">
                                        {{ category.title }}
                                    </h3>
                                    <p class="cooper-text-baseline mt-2 font-cooper text-sm font-medium leading-relaxed text-base-content/65">
                                        {{ category.description }}
                                    </p>
                                </div>

                                <input
                                    v-if="category.id === 'analytics'"
                                    v-model="analyticsEnabled"
                                    class="toggle mt-1 shrink-0 transition-colors ease-in-out checked:border-[#5a579e] checked:bg-[#5a579e] checked:text-white"
                                    :class="toggleTransition ? 'duration-150' : 'duration-0'"
                                    type="checkbox"
                                    aria-label="Activer la mesure d’audience et les KPIs"
                                />
                                <input
                                    v-else
                                    class="toggle mt-1 shrink-0 transition-colors ease-in-out checked:border-[#5a579e] checked:bg-[#5a579e] checked:text-white"
                                    :class="toggleTransition ? 'duration-150' : 'duration-0'"
                                    type="checkbox"
                                    checked
                                    disabled
                                    aria-label="Cookies obligatoires activés"
                                />
                            </div>
                        </section>
                    </div>

                    <div class="modal-action mt-6 grid gap-3 sm:grid-cols-2">
                        <button
                            v-if="!hasDecision"
                            class="btn h-[48px] rounded-2xl border-base-300 bg-white font-cooper text-[0.95rem] text-base-content"
                            type="button"
                            @click="view = 'summary'"
                        >
                            <span class="cooper-baseline">Retour</span>
                        </button>
                        <button
                            v-else
                            class="btn h-[48px] rounded-2xl border-base-300 bg-white font-cooper text-[0.95rem] text-base-content"
                            type="button"
                            @click="closeModal"
                        >
                            <span class="cooper-baseline">Annuler</span>
                        </button>
                        <button
                            class="btn h-[48px] rounded-2xl border-none font-cooper text-[0.95rem] text-white transition-[filter] duration-150 hover:brightness-90 ease-in-out"
                            style="background-color: #5a579e;"
                            type="button"
                            @click="saveSettings"
                        >
                            <span class="cooper-baseline">Enregistrer</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </Transition>

    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <button
            v-if="hasDecision && !isOpen"
            class="btn fixed bottom-4 left-4 z-[900] h-[60px] w-[60px] rounded-full border-none bg-white !p-0 text-black shadow-lg transition-transform duration-200 ease-out hover:scale-110 sm:bottom-8 sm:left-8"
            type="button"
            aria-label="Réouvrir les préférences cookies"
            title="Préférences cookies"
            @click="openModal"
        >
            <svg
                id="Cookie_24"
                class="h-[42px] w-[42px]"
                width="24"
                height="24"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >
                <rect width="24" height="24" stroke="none" fill="#000000" opacity="0" />
                <g transform="matrix(0.48 0 0 0.48 12 12)">
                    <path
                        style="stroke: none; stroke-width: 1; stroke-dasharray: none; stroke-linecap: butt; stroke-dashoffset: 0; stroke-linejoin: miter; stroke-miterlimit: 4; fill: currentColor; fill-rule: nonzero; opacity: 1;"
                        transform=" translate(-25, -25)"
                        d="M 25 4 C 13.414063 4 4 13.414063 4 25 C 4 36.585938 13.414063 46 25 46 C 36.585938 46 46 36.585938 46 25 C 46 24.378906 45.960938 23.78125 45.910156 23.203125 C 45.878906 22.855469 45.671875 22.546875 45.359375 22.390625 C 45.042969 22.234375 44.671875 22.253906 44.375 22.441406 C 43.824219 22.792969 43.191406 23 42.5 23 C 41.015625 23 39.769531 22.082031 39.253906 20.792969 C 39.148438 20.527344 38.933594 20.320313 38.667969 20.222656 C 38.398438 20.125 38.101563 20.144531 37.847656 20.28125 C 37.003906 20.738281 36.035156 21 35 21 C 31.675781 21 29 18.324219 29 15 C 29 13.964844 29.261719 12.996094 29.71875 12.152344 C 29.855469 11.898438 29.875 11.601563 29.777344 11.332031 C 29.679688 11.066406 29.472656 10.851563 29.207031 10.746094 C 27.917969 10.230469 27 8.984375 27 7.5 C 27 6.808594 27.207031 6.175781 27.558594 5.625 C 27.746094 5.328125 27.765625 4.957031 27.609375 4.640625 C 27.453125 4.328125 27.144531 4.121094 26.796875 4.089844 C 26.21875 4.039063 25.621094 4 25 4 Z M 38 4 C 36.894531 4 36 4.894531 36 6 C 36 7.105469 36.894531 8 38 8 C 39.105469 8 40 7.105469 40 6 C 40 4.894531 39.105469 4 38 4 Z M 25 6 C 25.144531 6 25.292969 6.015625 25.4375 6.023438 C 25.285156 6.519531 25 6.953125 25 7.5 C 25 9.4375 26.136719 10.984375 27.660156 11.960938 C 27.269531 12.90625 27 13.917969 27 15 C 27 19.40625 30.59375 23 35 23 C 36.082031 23 37.09375 22.730469 38.039063 22.339844 C 39.015625 23.863281 40.5625 25 42.5 25 C 43.046875 25 43.480469 24.714844 43.980469 24.5625 C 43.984375 24.707031 44 24.855469 44 25 C 44 35.503906 35.503906 44 25 44 C 14.496094 44 6 35.503906 6 25 C 6 14.496094 14.496094 6 25 6 Z M 36.5 12 C 35.671875 12 35 12.671875 35 13.5 C 35 14.328125 35.671875 15 36.5 15 C 37.328125 15 38 14.328125 38 13.5 C 38 12.671875 37.328125 12 36.5 12 Z M 21.5 15 C 20.671875 15 20 15.671875 20 16.5 C 20 17.328125 20.671875 18 21.5 18 C 22.328125 18 23 17.328125 23 16.5 C 23 15.671875 22.328125 15 21.5 15 Z M 45 15 C 44.449219 15 44 15.449219 44 16 C 44 16.550781 44.449219 17 45 17 C 45.550781 17 46 16.550781 46 16 C 46 15.449219 45.550781 15 45 15 Z M 15 20 C 13.34375 20 12 21.34375 12 23 C 12 24.65625 13.34375 26 15 26 C 16.65625 26 18 24.65625 18 23 C 18 21.34375 16.65625 20 15 20 Z M 24.5 24 C 23.671875 24 23 24.671875 23 25.5 C 23 26.328125 23.671875 27 24.5 27 C 25.328125 27 26 26.328125 26 25.5 C 26 24.671875 25.328125 24 24.5 24 Z M 17 31 C 15.894531 31 15 31.894531 15 33 C 15 34.105469 15.894531 35 17 35 C 18.105469 35 19 34.105469 19 33 C 19 31.894531 18.105469 31 17 31 Z M 30.5 32 C 29.121094 32 28 33.121094 28 34.5 C 28 35.878906 29.121094 37 30.5 37 C 31.878906 37 33 35.878906 33 34.5 C 33 33.121094 31.878906 32 30.5 32 Z"
                        stroke-linecap="round"
                    />
                </g>
            </svg>
        </button>
    </Transition>
</template>
