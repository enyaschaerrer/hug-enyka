<script setup lang="ts">
import CoBrandedHeader from '../../components/public/CoBrandedHeader.vue';
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

</script>

<template>
    <div class="min-h-screen bg-white">
        <CoBrandedHeader :company="company" />

        <TinderEligibilityPrototype />

        <SmsConversationPrototype />
    </div>
</template>
