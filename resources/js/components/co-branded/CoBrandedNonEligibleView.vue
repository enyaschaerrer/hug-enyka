<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';

const props = defineProps<{
    reasons?: { title: string; detail: string; status?: 'warning' | 'blocker' }[];
}>();

const form = reactive({
    nom: '',
    prenom: '',
    email: '',
});

const bubbleText = 'Vous n\'avez malheureusement pas un match pour le moment. Voici la raison. Cependant, nous vous envoyons volontiers un rappel par message pour repasser le test !';
const typedText = ref('');

function animateText(delay = 22) {
    let index = 0;
    const tick = () => {
        typedText.value = bubbleText.slice(0, index);
        index += 1;
        if (index <= bubbleText.length) {
            window.setTimeout(tick, delay);
        }
    };
    tick();
}

onMounted(() => {
    animateText();
});

// Causes réelles transmises par le quiz / chat (plus de raisons en dur).
const displayReasons = computed(() => props.reasons ?? []);
</script>

<template>
    <div class="font-cooper overflow-y-auto">
        <div class="mx-auto max-w-5xl px-6 py-10 lg:px-12">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-12">

                <!-- Gauche : bulle + Sanguy -->
                <div class="flex shrink-0 flex-col items-center lg:w-80">
                    <div class="non-eligible-bubble relative mt-36 w-full rounded-2xl border border-[#2f1725] bg-white px-4 py-3 text-sm font-semibold leading-snug text-[#2f1725] shadow-sm">
                        <span class="invisible">{{ bubbleText }}</span>
                        <span class="absolute inset-0 px-4 py-3 text-sm font-semibold leading-snug">{{ typedText }}</span>
                    </div>
                    <img
                        :src="'/img/mascots/sanguy_devastated.webp'"
                        alt="Sanguy"
                        class="-mt-12 w-full object-contain drop-shadow-[0_12px_22px_rgba(109,0,46,0.18)]"
                    />
                </div>

                <!-- Droite : contenu -->
                <div class="flex-1">
                    <h1 class="mb-6 text-4xl font-bold text-[#5f0f35]">Inéligible</h1>

                    <!-- Cartes raisons -->
                    <div class="mb-8 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div
                            v-for="(reason, index) in displayReasons"
                            :key="index"
                            class="rounded-2xl border-1 border-razzmatazz-950 bg-razzmatazz-50 px-4 py-4 text-center"
                        >
                            <p class="font-bold text-heading-t3 text-razzmatazz-900">{{ reason.title }}</p>
                            <p class="mt-2 text-body text-razzmatazz-900">{{ reason.detail }}</p>
                        </div>
                    </div>

                    <!-- Formulaire rappel -->
                    <h2 class="mb-4 text-xl font-bold text-[#2f1725]">Vous aimeriez un rappel ?</h2>
                    <form class="rounded-2xl bg-razzmatazz-800 px-6 py-6 space-y-4 shadow-[0_8px_40px_rgba(109,0,46,0.10)]" @submit.prevent>
                        <label class="flex flex-col gap-1.5">
                            <span class="text-sm font-semibold text-razzmatazz-50">Nom</span>
                            <input
                                v-model="form.nom"
                                type="text"
                                class="w-full rounded-xl border-2 border-razzmatazz-950 bg-razzmatazz-50 px-4 py-2.5 text-sm font-semibold text-[#2f1725] placeholder:text-[#c9a0b4] outline-none focus:border-black focus:text-black"
                                placeholder="Nom"
                            />
                        </label>
                        <label class="flex flex-col gap-1.5">
                            <span class="text-sm font-semibold text-razzmatazz-50">Prénom</span>
                            <input
                                v-model="form.prenom"
                                type="text"
                                class="w-full rounded-xl border-2 border-razzmatazz-950 bg-razzmatazz-50 px-4 py-2.5 text-sm font-semibold text-[#2f1725] placeholder:text-[#c9a0b4] outline-none focus:border-black focus:text-black"
                                placeholder="Prénom"
                            />
                        </label>
                        <label class="flex flex-col gap-1.5">
                            <span class="text-sm font-semibold text-razzmatazz-50">Email</span>
                            <input
                                v-model="form.email"
                                type="email"
                                class="w-full rounded-xl border-2 border-razzmatazz-950 bg-razzmatazz-50 px-4 py-2.5 text-sm font-semibold text-[#2f1725] placeholder:text-[#c9a0b4] outline-none focus:border-black focus:text-black"
                                placeholder="ton@email.com"
                            />
                        </label>
                        <div class="flex justify-center pt-1">
                            <button
                                type="submit"
                                class="rounded-xl bg-razzmatazz-950 px-8 py-2.5 text-body font-medium text-razzmatazz-50 transition hover:bg-razzmatazz-400 hover:text-white"
                            >
                                S'inscrire pour un rappel
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.non-eligible-bubble::before {
    content: '';
    position: absolute;
    bottom: -6px;
    left: 28px;
    width: 11px;
    height: 11px;
    background: #ffffff;
    border-right: 1px solid #2f1725;
    border-bottom: 1px solid #2f1725;
    transform: rotate(45deg);
}

.non-eligible-bubble::after {
    content: '';
    position: absolute;
    bottom: 0px;
    left: 27px;
    width: 14px;
    height: 5px;
    background: #ffffff;
    z-index: 1;
}
</style>
