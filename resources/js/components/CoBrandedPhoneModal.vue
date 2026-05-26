<script setup lang="ts">
import { nextTick, ref, watch } from 'vue';
import QRCodeStyling from 'qr-code-styling';

const props = defineProps<{
    open: boolean;
    primaryColor: string | null;
}>();

defineEmits<{
    close: [];
}>();

const qrCodeContainer = ref<HTMLElement | null>(null);
const qrCodeReady = ref(false);
let qrCode: QRCodeStyling | null = null;

async function renderQrCode() {
    await nextTick();

    if (!qrCodeContainer.value) {
        return;
    }

    qrCodeContainer.value.innerHTML = '';
    qrCode = new QRCodeStyling({
        type: 'svg',
        width: 220,
        height: 220,
        margin: 1,
        data: window.location.href,
        qrOptions: {
            errorCorrectionLevel: 'M',
        },
        dotsOptions: {
            type: 'rounded',
            color: '#111111',
        },
        cornersSquareOptions: {
            type: 'extra-rounded',
            color: '#111111',
        },
        cornersDotOptions: {
            type: 'dot',
            color: '#111111',
        },
        backgroundOptions: {
            color: '#ffffff',
        },
    });

    qrCode.append(qrCodeContainer.value);
    qrCodeReady.value = true;
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            void renderQrCode();
        }
    },
);
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition duration-300 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="open"
            class="fixed inset-0 z-[999] grid place-items-end justify-items-end bg-transparent p-4 sm:p-6"
            role="dialog"
            aria-modal="true"
            data-theme="light"
        >
            <button class="absolute inset-0 cursor-default bg-black/50" type="button" aria-label="Fermer" @click="$emit('close')"></button>

            <div class="modal-box relative max-w-sm scale-100 bg-white text-stone-950 opacity-100 translate-0">
                <h2 class="text-lg font-semibold text-base-content">Accès mobile</h2>
                <p class="mt-2 text-sm text-base-content/70">
                    Scanne ce QR code avec ton smartphone pour continuer l'expérience sur mobile.
                </p>

                <div class="mt-6 flex justify-center">
                    <div
                        ref="qrCodeContainer"
                        class="h-56 w-56 overflow-hidden rounded-xl bg-white p-2 [&_svg]:h-full [&_svg]:w-full"
                        :class="{ hidden: !qrCodeReady }"
                        aria-label="QR code vers cette page co-brandée"
                    ></div>
                    <div v-if="!qrCodeReady" class="flex h-56 w-56 items-center justify-center rounded-xl bg-base-200">
                        <span class="loading loading-spinner loading-md" aria-label="Chargement du QR code"></span>
                    </div>
                </div>

                <div class="modal-action">
                    <button
                        class="btn w-full border-none text-white"
                        type="button"
                        :style="{ backgroundColor: primaryColor ?? '#111111' }"
                        @click="$emit('close')"
                    >
                        Continuer sur cet appareil
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
