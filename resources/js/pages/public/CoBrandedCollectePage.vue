<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CoBrandedHeader from '../../components/public/CoBrandedHeader.vue';
import CoBrandedPhoneModal from '../../components/modals/CoBrandedPhoneModal.vue';
import SmsConversationPrototype from '../../components/sms-chat/SmsConversationPrototype.vue';
import TinderEligibilityPrototype from '../../components/tinder-cards/TinderEligibilityPrototype.vue';

type CoBrandedCollecte = {
    company: {
        name: string;
        logo: string | null;
        shortDescription: string | null;
        slug: string | null;
        colors: {
            primary: string | null;
            secondary: string | null;
            third: string | null;
        };
    };
    collection: {
        start: string | null;
        end: string | null;
        appointmentUrl: string;
    };
};

type AppState = {
    coBrandedCollecte?: CoBrandedCollecte | null;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const coBrandedCollecte = appState?.coBrandedCollecte;
const company = coBrandedCollecte?.company ?? {
    name: 'Entreprise',
    logo: null,
    shortDescription: null,
    slug: null,
    colors: {
        primary: '#ffffff',
        secondary: '#ffffff',
        third: '#ffffff',
    },
};

const showPhoneModal = ref(false);
const isDesktop = ref(false);
let desktopMediaQuery: MediaQueryList | null = null;
let phoneModalTimeout: number | null = null;

function syncPhoneModal(event?: MediaQueryListEvent) {
    isDesktop.value = event?.matches ?? desktopMediaQuery?.matches ?? false;

    if (isDesktop.value) {
        showPhoneModal.value = true;
    }
}

onMounted(() => {
    desktopMediaQuery = window.matchMedia('(min-width: 640px)');
    phoneModalTimeout = window.setTimeout(() => syncPhoneModal(), 450);
    desktopMediaQuery.addEventListener('change', syncPhoneModal);
});

onBeforeUnmount(() => {
    if (phoneModalTimeout !== null) {
        window.clearTimeout(phoneModalTimeout);
    }

    desktopMediaQuery?.removeEventListener('change', syncPhoneModal);
});
</script>

<template>
    <div class="min-h-screen bg-white">
        <CoBrandedHeader :company="company" />

        <TinderEligibilityPrototype />

        <SmsConversationPrototype />

        <CoBrandedPhoneModal
            :open="showPhoneModal"
            :primary-color="company.colors.primary"
            @close="showPhoneModal = false"
        />

        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <button
                v-if="isDesktop && !showPhoneModal"
                class="btn fixed right-8 bottom-8 z-[900] h-[60px] w-[60px] rounded-full border-none bg-white !p-0 shadow-lg transition-transform duration-200 ease-out hover:scale-110"
                type="button"
                title="Afficher le QR code mobile"
                aria-label="Afficher le QR code mobile"
                :style="{ color: company.colors.primary ?? '#111111' }"
                @click="showPhoneModal = true"
            >
                <svg
                    id="Mobile_Qr_Code_24"
                    class="h-[45px] w-[45px]"
                    width="24"
                    height="24"
                    viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <g transform="matrix(0.83 0 0 0.83 12 12)">
                        <g transform="matrix(1 0 0 1 0 0)">
                            <path
                                transform="translate(-12, -12)"
                                d="M 16.5 0.5 L 7.5 0.5 C 6.39543 0.5 5.5 1.39543 5.5 2.5 L 5.5 21.5 C 5.5 22.6046 6.39543 23.5 7.5 23.5 L 16.5 23.5 C 17.6046 23.5 18.5 22.6046 18.5 21.5 L 18.5 2.5 C 18.5 1.39543 17.6046 0.5 16.5 0.5 Z"
                            />
                        </g>
                        <g transform="matrix(1 0 0 1 0 -7.5)">
                            <path transform="translate(-12, -4.5)" d="M 18.5 4.5 L 5.5 4.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 0 7.5)">
                            <path transform="translate(-12, -19.5)" d="M 18.5 19.5 L 5.5 19.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 0 -9.5)">
                            <path transform="translate(-12, -2.5)" d="M 9.5 2.5 L 14.5 2.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 -3.5 -3.5)">
                            <path transform="translate(-8.5, -8.5)" d="M 9.49597 7.5 L 7.49597 7.5 L 7.49597 9.5 L 9.49597 9.5 L 9.49597 7.5 Z" />
                        </g>
                        <g transform="matrix(1 0 0 1 -3.5 3.5)">
                            <path transform="translate(-8.5, -15.5)" d="M 9.49597 14.5 L 7.49597 14.5 L 7.49597 16.5 L 9.49597 16.5 L 9.49597 14.5 Z" />
                        </g>
                        <g transform="matrix(1 0 0 1 3.5 -3.5)">
                            <path transform="translate(-15.5, -8.5)" d="M 16.496 7.5 L 14.496 7.5 L 14.496 9.5 L 16.496 9.5 L 16.496 7.5 Z" />
                        </g>
                        <g transform="matrix(1 0 0 1 -2.5 1)">
                            <path transform="translate(-9.5, -13)" d="M 7.49597 12.5 L 11.496 12.5 L 11.496 13.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 3 3)">
                            <path transform="translate(-15, -15)" d="M 13.496 13.5 L 13.496 16.5 L 16.496 16.5 L 16.496 13.5 L 15.496 13.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 -0.5 4)">
                            <path transform="translate(-11.5, -16)" d="M 11.496 15.5 L 11.496 16.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 0 -3)">
                            <path transform="translate(-12, -9)" d="M 11.496 7.5 L 11.496 10.5 L 12.496 10.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 3.5 -0.5)">
                            <path transform="translate(-15.5, -11.5)" d="M 14.496 11.5 L 16.496 11.5" />
                        </g>
                        <g transform="matrix(1 0 0 1 -0.13 9.5)">
                            <path transform="translate(-11.88, -21.5)" d="M 12 21.75 C 11.8619 21.75 11.75 21.6381 11.75 21.5 C 11.75 21.3619 11.8619 21.25 12 21.25" />
                        </g>
                        <g transform="matrix(1 0 0 1 0.13 9.5)">
                            <path transform="translate(-12.13, -21.5)" d="M 12 21.75 C 12.1381 21.75 12.25 21.6381 12.25 21.5 C 12.25 21.3619 12.1381 21.25 12 21.25" />
                        </g>
                    </g>
                </svg>
            </button>
        </Transition>
    </div>
</template>
