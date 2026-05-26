<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import CoBrandedHeader from '../../components/public/CoBrandedHeader.vue';
import CoBrandedPhoneModal from '../../components/CoBrandedPhoneModal.vue';
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
let desktopMediaQuery: MediaQueryList | null = null;
let phoneModalTimeout: number | null = null;

function syncPhoneModal(event?: MediaQueryListEvent) {
    const isDesktop = event?.matches ?? desktopMediaQuery?.matches ?? false;

    if (isDesktop) {
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
    </div>
</template>
