<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';
import QRCodeStyling from 'qr-code-styling';
import { readableTextColor } from '../../utils/contrast';

const props = defineProps<{
    open: boolean;
    primaryColor: string | null;
}>();

defineEmits<{
    close: [];
}>();

const qrCodeContainer = ref<HTMLElement | null>(null);
const qrCodeReady = ref(false);
const actionBackgroundColor = computed(() => props.primaryColor ?? '#111111');
const actionTextColor = computed(() => readableTextColor(actionBackgroundColor.value));
let qrCode: QRCodeStyling | null = null;
let previousBodyOverflow = '';

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
            type: 'square',
            color: '#111111',
        },
        cornersSquareOptions: {
            type: 'rounded',
            color: '#111111',
        },
        cornersDotOptions: {
            type: 'rounded',
            color: '#111111',
        },
        backgroundOptions: {
            color: '#ffffff',
        },
    });

    qrCode.append(qrCodeContainer.value);
    qrCodeReady.value = true;
}

function lockBodyScroll() {
    previousBodyOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';
}

function unlockBodyScroll() {
    document.body.style.overflow = previousBodyOverflow;
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            lockBodyScroll();
            void renderQrCode();
            return;
        }

        unlockBodyScroll();
    },
);

onBeforeUnmount(() => {
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
            v-if="open"
            class="fixed inset-0 z-[999] grid place-items-end justify-items-end bg-transparent p-6 sm:p-8"
            role="dialog"
            aria-modal="true"
            data-theme="light"
        >
            <button class="absolute inset-0 cursor-default bg-black/70" type="button" aria-label="Fermer" @click="$emit('close')"></button>

            <div class="modal-box relative max-w-sm scale-100 rounded-3xl bg-white p-4 text-stone-950 opacity-100 translate-0">
                <h2 class="mt-[15px] text-center font-cooper text-[1.65rem] font-semibold text-base-content">Continuer sur mobile ?</h2>
                <p class="mt-2 text-center font-cooper text-sm font-semibold text-base-content/70">
                    Pour une expérience plus fluide et interactive, scannez ce QR code avec votre smartphone.
                </p>

                <div class="mt-6 flex justify-center py-[20px]">
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
                        class="btn relative h-[48px] w-full overflow-hidden rounded-2xl border-none px-6 text-[0.95rem] transition duration-200 ease-out before:absolute before:inset-0 before:bg-black/0 before:transition before:duration-200 before:ease-out hover:before:bg-black/15"
                        type="button"
                        :style="{ backgroundColor: actionBackgroundColor, color: actionTextColor }"
                        @click="$emit('close')"
                    >
                        <span class="relative z-10">Continuer sur cet appareil</span>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>
