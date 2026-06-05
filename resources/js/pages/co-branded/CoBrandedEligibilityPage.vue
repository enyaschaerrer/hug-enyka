<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import CoBrandedFooter from '../../components/co-branded/CoBrandedFooter.vue';
import TinderEligibilityPrototype from '../../components/tinder-cards/TinderEligibilityPrototype.vue';
import CoBrandedPhoneModal from '../../components/co-branded/CoBrandedPhoneModal.vue';
import QuestionnaireExitModal from '../../components/modals/QuestionnaireExitModal.vue';
import CoBrandedModuleHeader from '../../components/public/CoBrandedModuleHeader.vue';
import CoBrandedAuthGate from '../../components/co-branded/CoBrandedAuthGate.vue';
import CoBrandedNonEligibleView from '../../components/co-branded/CoBrandedNonEligibleView.vue';
import SmsConversationPrototype from '../../components/sms-chat/SmsConversationPrototype.vue';
import { useAdminRouter } from '../../composables/useAdminRouter';
import { useCoBrandedCollecte } from '../../composables/useCoBrandedCollecte';

const { navigate, forceNavigate, setNavigationBlocker } = useAdminRouter();
const { csrfToken, company, collection, auth } = useCoBrandedCollecte();
const qrModalOpen = ref(false);
const isDesktop = ref(false);
const exitModalOpen = ref(false);
const showNonEligible = ref(false);
const showSms = ref(false);
const showConfetti = ref(false);
const nonEligibleBgStyle = computed(() => showNonEligible.value ? { backgroundImage: 'url(/img/cobranded-background/bg-cobranded.webp)' } : {});

const CONFETTI_COLORS = ['#6d002e', '#b81e62', '#f4b5ca', '#355755', '#8ab4b0', '#ffffff'];
const confettiPieces = Array.from({ length: 55 }, (_, i) => ({
    id: i,
    color: CONFETTI_COLORS[i % CONFETTI_COLORS.length],
    left: `${(i * 97 + 13) % 100}%`,
    delay: `${(i * 137) % 900}ms`,
    duration: `${1100 + (i * 73) % 700}ms`,
    size: `${6 + (i * 31) % 7}px`,
    round: i % 3 === 0,
}));

function onMatch() {
    showConfetti.value = true;
    window.setTimeout(() => {
        showSms.value = true;
    }, 1600);
    window.setTimeout(() => {
        showConfetti.value = false;
    }, 2600);
}
let desktopMediaQuery: MediaQueryList | null = null;
let phoneModalTimeout: number | null = null;
let pendingLeaveAction: (() => void | Promise<void>) | null = null;
let bypassExitGuard = false;

function goHome() {
    navigate(collection.publicUrl);
}

function goToEligibilityPage() {
    navigate(collection.eligibilityUrl);
}

function syncPhoneModal(event?: MediaQueryListEvent) {
    isDesktop.value = event?.matches ?? desktopMediaQuery?.matches ?? false;

    if (isDesktop.value) {
        qrModalOpen.value = true;
    }
}

function openExitModal(action: () => void | Promise<void>) {
    pendingLeaveAction = action;
    exitModalOpen.value = true;
}

function closeExitModal() {
    exitModalOpen.value = false;
    pendingLeaveAction = null;
}

async function confirmExit() {
    const action = pendingLeaveAction;

    bypassExitGuard = true;
    closeExitModal();

    try {
        await action?.();
    } finally {
        window.setTimeout(() => {
            bypassExitGuard = false;
        }, 0);
    }
}

async function logout() {
    try {
        const res = await fetch(auth.logoutUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (res.ok) {
            window.location.reload();
            return;
        }
    } catch {
        // Fall through to reload the page and let the server state decide.
    }

    window.location.reload();
}

function interceptPageExit(event: MouseEvent) {
    if (bypassExitGuard || !auth.canAccess || event.defaultPrevented) {
        return;
    }

    if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const target = event.target;

    if (!(target instanceof Element)) {
        return;
    }

    const link = target.closest('a[href]') as HTMLAnchorElement | null;

    if (!link) {
        return;
    }

    const href = link.getAttribute('href');

    if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
        return;
    }

    const destination = new URL(href, window.location.href);
    const current = new URL(window.location.href);

    if (
        destination.pathname === current.pathname
        && destination.search === current.search
        && destination.hash === current.hash
    ) {
        return;
    }

    event.preventDefault();
    openExitModal(() => {
        if (link.target === '_blank') {
            window.open(destination.toString(), '_blank', 'noopener');
            return;
        }

        window.location.assign(destination.toString());
    });
}

function preventBrowserUnload(event: BeforeUnloadEvent) {
    if (bypassExitGuard || !auth.canAccess) {
        return;
    }

    event.preventDefault();
    event.returnValue = '';
}

onMounted(() => {
    if (!auth.canAccess) {
        return;
    }

    desktopMediaQuery = window.matchMedia('(min-width: 640px)');
    phoneModalTimeout = window.setTimeout(() => syncPhoneModal(), 450);
    desktopMediaQuery.addEventListener('change', syncPhoneModal);
    setNavigationBlocker((path) => {
        if (bypassExitGuard || path === window.location.pathname) {
            return false;
        }

        openExitModal(() => forceNavigate(path));
        return true;
    });
    document.addEventListener('click', interceptPageExit, true);
    window.addEventListener('beforeunload', preventBrowserUnload);
});

onBeforeUnmount(() => {
    if (phoneModalTimeout !== null) {
        window.clearTimeout(phoneModalTimeout);
    }

    desktopMediaQuery?.removeEventListener('change', syncPhoneModal);
    setNavigationBlocker(null);
    document.removeEventListener('click', interceptPageExit, true);
    window.removeEventListener('beforeunload', preventBrowserUnload);
});
</script>

<template>
    <div
        class="flex flex-col"
        :class="showNonEligible ? 'min-h-screen bg-cover bg-center bg-no-repeat' : 'h-[100svh] overflow-hidden bg-catskillwhite-50'"
        :style="nonEligibleBgStyle"
    >
        <CoBrandedAuthGate
            v-if="!auth.canAccess"
            :company="company"
            :csrf-token="csrfToken"
            :email-placeholder="auth.emailPlaceholder"
            :access-code-url="auth.accessCodeUrl"
            :login-url="auth.loginUrl"
        />

        <template v-else>
            <CoBrandedPhoneModal
                :open="qrModalOpen"
                :primary-color="company.colors.primary"
                @close="qrModalOpen = false"
            />

            <QuestionnaireExitModal
                :open="exitModalOpen"
                @close="closeExitModal"
                @confirm="confirmExit"
            />

            <CoBrandedModuleHeader
                :company="company"
                :csrf-token="csrfToken"
                :logout-url="auth.logoutUrl"
                @go-home="goHome"
                @go-test="goToEligibilityPage"
                @logout="openExitModal(logout)"
            />

            <!-- Confetti overlay -->
            <div v-if="showConfetti" class="pointer-events-none fixed inset-0 z-[500] overflow-hidden">
                <div
                    v-for="piece in confettiPieces"
                    :key="piece.id"
                    class="confetti-piece absolute top-0"
                    :style="{
                        left: piece.left,
                        width: piece.size,
                        height: piece.size,
                        backgroundColor: piece.color,
                        borderRadius: piece.round ? '50%' : '2px',
                        animationDuration: piece.duration,
                        animationDelay: piece.delay,
                    }"
                />
            </div>

            <!-- Tinder -->
            <main
                v-if="!showNonEligible && !showSms"
                class="flex flex-1 items-center justify-center overflow-hidden bg-cover bg-center bg-no-repeat"
                style="background-image: url('/img/cobranded-background/bg-cobranded.webp');"
            >
                <div class="flex w-full justify-center px-6 py-4 lg:px-12" style="transform: translateY(-20px);">
                    <TinderEligibilityPrototype contained @ineligible="showNonEligible = true" @match="onMatch" />
                </div>
            </main>

            <!-- SMS -->
            <Transition
                enter-active-class="transition-opacity duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
            >
                <div
                    v-if="showSms"
                    class="flex flex-col flex-1 min-h-0 overflow-hidden bg-cover bg-center bg-no-repeat"
                    style="background-image: url('/img/cobranded-background/bg-cobranded.webp');"
                >
                    <SmsConversationPrototype />
                </div>
            </Transition>

            <!-- Non éligible -->
            <CoBrandedNonEligibleView v-if="showNonEligible" class="flex-1" />

            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <button
                    v-if="isDesktop && !qrModalOpen && !showNonEligible && !showSms"
                    class="btn fixed bottom-8 right-6 z-[900] h-[60px] w-[60px] rounded-full border-none bg-white p-0 shadow-lg transition-transform duration-200 ease-out hover:scale-110"
                    type="button"
                    title="Afficher le QR code mobile"
                    aria-label="Afficher le QR code mobile"
                    :style="{ color: '#355755' }"
                    @click="qrModalOpen = true"
                >
                    <svg
                        id="Mobile_Qr_Code_24"
                        class="h-[42px] w-[42px]"
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

            <CoBrandedFooter />
        </template>
    </div>
</template>

<style scoped>
.confetti-piece {
    animation: confetti-fall linear forwards;
    will-change: transform, opacity;
}

@keyframes confetti-fall {
    0% {
        transform: translateY(-10px) rotate(0deg);
        opacity: 1;
    }
    80% {
        opacity: 1;
    }
    100% {
        transform: translateY(110vh) rotate(540deg);
        opacity: 0;
    }
}
</style>
