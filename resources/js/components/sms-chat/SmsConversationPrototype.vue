<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import scenarioData from '../../data/sms-scenarios.json';
import countriesJson from '../../data/country-donation-rules.json';
import 'flag-icons/css/flag-icons.min.css';
import { SMS_NEXT_MODULE } from '../../types/sms-chat';
import type { SanguyEmotion, SmsAnswer, SmsMessage, SmsScenario } from '../../types/sms-chat';
import type { Country } from '../../types/interactive-map';

const props = withDefaults(defineProps<{
    /** Modules de vérification enregistrés par le swipe, dans l'ordre à jouer. */
    modules?: string[];
}>(), {
    modules: () => [],
});

const scenario = scenarioData as SmsScenario;
const sanguyImages: Record<SanguyEmotion, string> = {
    happy: '/img/mascots/sanguy_thumbs_up.webp',
    angry: '/img/mascots/sanguy_hero.webp',
    'alt-happy': '/img/mascots/sanguy_satisfied.webp',
    'alt-angry': '/img/mascots/sanguy_above.webp',
};

const countries = countriesJson as Country[];

const messagesContainer = ref<HTMLElement | null>(null);
const currentNodeId = ref<string>('');
const moduleQueue = ref<string[]>([]);
const messages = ref<SmsMessage[]>([]);
const isTyping = ref(false);
const sanguyEmotion = ref<SanguyEmotion>('happy');

// État du module « voyage » (machine à états : nombre de pays → choix du pays → confirmation date)
const travelStage = ref<'count' | 'pick' | 'confirm' | 'done'>('count');
const travelTotal = ref(0);               // nombre de pays visités
const travelIndex = ref(0);               // index du pays en cours (0-based)
const travelWaitLabel = ref('');          // délai du pays courant (ex. « 4 mois »)
const countryQuery = ref('');
const countrySuggestions = ref<Country[]>([]);
const showCountrySuggestions = ref(false);

const travelCountOptions = [
    { label: '1 pays', value: 1 },
    { label: '2 pays', value: 2 },
    { label: '3 pays', value: 3 },
    { label: '4 pays ou plus', value: 4 },
];

// Agrégation des issues pour le verdict final
const collectedDelays = ref<{ months: number | null; label: string }[]>([]);
const hasPermanent = ref(false);
// Écran final : 'reminder' = on propose un rappel ; 'done' = conversation terminée
const resultStage = ref<'none' | 'reminder' | 'done'>('none');
const finalDelayLabel = ref('');

// Convertit un libellé de délai (« 4 mois », « 28 jours »…) en mois, pour comparer.
function labelToMonths(label: string): number | null {
    const months = label.match(/(\d+)\s*mois/);
    if (months) return Number(months[1]);
    const weeks = label.match(/(\d+)\s*semaine/);
    if (weeks) return Number(weeks[1]) / 4;
    const days = label.match(/(\d+)\s*jour/);
    if (days) return Number(days[1]) / 30;
    return null;
}

function recordDelay(months: number | null, label: string) {
    collectedDelays.value.push({ months, label });
}

const currentNode = computed(() => scenario.nodes[currentNodeId.value]);
const currentSanguyImage = computed(() => sanguyImages[sanguyEmotion.value]);
const activeBotMessageId = computed(() => {
    return [...messages.value].reverse().find((message) => message.speaker === 'bot')?.id;
});
const activeUserMessageId = computed(() => {
    return [...messages.value].reverse().find((message) => message.speaker === 'user')?.id;
});
const currentAnswers = computed<SmsAnswer[]>(() => {
    if (!currentNode.value || currentNode.value.type !== 'question' || isTyping.value) {
        return [];
    }

    return currentNode.value.answers;
});

function createMessageId(prefix: string) {
    return `${prefix}-${Date.now()}-${messages.value.length}`;
}

async function scrollToLastMessage() {
    await nextTick();

    if (!messagesContainer.value) {
        return;
    }

    messagesContainer.value.scrollTo({
        top: messagesContainer.value.scrollHeight,
        behavior: 'smooth',
    });
}

async function pushBotNode(nodeId: string, nextEmotion?: SanguyEmotion) {
    const node = scenario.nodes[nodeId];

    if (!node) {
        return;
    }

    currentNodeId.value = nodeId;

    // Réinitialise l'état du module voyage à chaque nouvelle entrée
    if (node.type === 'travel') {
        travelStage.value = 'count';
        travelTotal.value = 0;
        travelIndex.value = 0;
        countryQuery.value = '';
        countrySuggestions.value = [];
        showCountrySuggestions.value = false;
    }

    messages.value.push({
        id: createMessageId('bot'),
        speaker: 'bot',
        text: node.text,
        nodeType: node.type,
        cta: node.type === 'appointment' ? node.cta : undefined,
    });

    if (nextEmotion) {
        sanguyEmotion.value = nextEmotion;
    }

    await scrollToLastMessage();
}

// Affiche une bulle bot avec un texte libre (utilisé par le module voyage, dont la suite est dynamique).
async function pushBotText(text: string, nextEmotion?: SanguyEmotion) {
    messages.value.push({
        id: createMessageId('bot'),
        speaker: 'bot',
        text,
        nodeType: 'question',
    });

    if (nextEmotion) {
        sanguyEmotion.value = nextEmotion;
    }

    await scrollToLastMessage();
}

// Passe au module suivant ; quand la file est vide, affiche le verdict final.
async function advanceToNextModule(nextEmotion?: SanguyEmotion) {
    const nextModule = moduleQueue.value.shift();
    const entry = nextModule ? scenario.moduleEntries[nextModule] : undefined;

    if (entry) {
        await pushBotNode(entry, nextEmotion);
        return;
    }

    await showFinalVerdict();
}

// Écran final : inéligibilité définitive, délai (avec rappel), ou rien (rendez-vous).
async function showFinalVerdict() {
    currentNodeId.value = '';

    if (hasPermanent.value) {
        await pushBotText(
            "D'après tes réponses, le don de sang n'est malheureusement pas possible. Merci beaucoup pour ta démarche 💛",
            'alt-angry',
        );
        resultStage.value = 'done';
        return;
    }

    if (collectedDelays.value.length > 0) {
        const known = collectedDelays.value.filter((delay) => delay.months !== null) as { months: number; label: string }[];
        const hasUnknown = collectedDelays.value.some((delay) => delay.months === null);
        const worst = known.length
            ? known.reduce((max, delay) => (delay.months > max.months ? delay : max))
            : null;

        if (worst) {
            finalDelayLabel.value = worst.label;
            let text = `D'après tes réponses, tu pourras donner ton sang dans environ ${worst.label}.`;
            if (hasUnknown) {
                text += ` Un autre point reste à confirmer avec l'infirmier·ère.`;
            }
            text += ' Veux-tu qu\'on te rappelle dès que tu pourras donner ?';
            await pushBotText(text, 'alt-happy');
        } else {
            finalDelayLabel.value = '';
            await pushBotText(
                "D'après tes réponses, un délai s'applique avant de pouvoir donner — l'infirmier·ère te confirmera la durée exacte. Veux-tu qu'on te recontacte ?",
                'alt-happy',
            );
        }

        resultStage.value = 'reminder';
        return;
    }

    await pushBotNode(scenario.appointment, 'happy');
    resultStage.value = 'done';
}

function chooseReminder(wantsReminder: boolean) {
    resultStage.value = 'done';

    messages.value.push({
        id: createMessageId('user'),
        speaker: 'user',
        text: wantsReminder ? 'Oui, je veux un rappel' : 'Non merci',
    });

    isTyping.value = true;
    void scrollToLastMessage();

    window.setTimeout(async () => {
        if (wantsReminder) {
            const when = finalDelayLabel.value ? ` dans ${finalDelayLabel.value}` : '';
            await pushBotText(`Parfait, on te préviendra${when} pour donner ton sang 👍`, 'happy');
        } else {
            await pushBotText('Pas de souci, reviens quand tu veux 💛', 'happy');
        }
        isTyping.value = false;
        await scrollToLastMessage();
    }, 650);
}

async function answerQuestion(answer: SmsAnswer) {
    messages.value.push({
        id: createMessageId('user'),
        speaker: 'user',
        text: answer.label,
    });

    // Enregistre l'issue éventuelle (délai agrégé / inéligibilité définitive)
    if (answer.outcome) {
        if (answer.outcome.severity === 'permanent') {
            hasPermanent.value = true;
        } else {
            recordDelay(answer.outcome.months ?? null, answer.outcome.label ?? 'à confirmer');
        }
    }

    isTyping.value = true;
    await scrollToLastMessage();

    window.setTimeout(async () => {
        if (answer.next === SMS_NEXT_MODULE) {
            await advanceToNextModule(answer.sanguyEmotion);
        } else {
            await pushBotNode(answer.next, answer.sanguyEmotion);
        }
        isTyping.value = false;
        await scrollToLastMessage();
    }, 650);
}

// ----- Module voyage : nombre de pays → sélecteur de pays → confirmation date -----

const isTravelNode = computed(() => currentNode.value?.type === 'travel');
const showTravelCount = computed(() => isTravelNode.value && travelStage.value === 'count' && !isTyping.value);
const showCountryPicker = computed(() => isTravelNode.value && travelStage.value === 'pick' && !isTyping.value);
const showTravelConfirm = computed(() => isTravelNode.value && travelStage.value === 'confirm' && !isTyping.value);

function normalize(value: string): string {
    return value.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
}

function onCountryInput() {
    const query = normalize(countryQuery.value);

    if (!query) {
        countrySuggestions.value = [];
        showCountrySuggestions.value = false;
        return;
    }

    countrySuggestions.value = countries
        .filter((country) =>
            normalize(country.name).includes(query)
            || normalize(country.iso2).includes(query)
            || normalize(country.iso3).includes(query)
            || country.aliases.some((alias) => normalize(alias).includes(query)),
        )
        .slice(0, 6);
    showCountrySuggestions.value = true;
}

// Texte demandé pour le pays courant (selon le nombre total de pays)
function promptForCountry(index: number): string {
    if (travelTotal.value <= 1) {
        return 'Dans quel pays es-tu allé·e ?';
    }
    return index === 0
        ? `Quel est le 1ᵉʳ pays où tu es allé·e ? (1/${travelTotal.value})`
        : `Et le pays suivant ? (${index + 1}/${travelTotal.value})`;
}

// Affiche une bulle bot après un petit délai de saisie (enchaînement entre étapes)
function typeThen(action: () => Promise<void>) {
    window.setTimeout(async () => {
        isTyping.value = true;
        await scrollToLastMessage();
        window.setTimeout(async () => {
            await action();
            isTyping.value = false;
            await scrollToLastMessage();
        }, 650);
    }, 300);
}

// Pays suivant si la liste n'est pas finie, sinon on termine le module
function proceedAfterCountry() {
    travelIndex.value += 1;

    if (travelIndex.value < travelTotal.value) {
        typeThen(async () => {
            await pushBotText(promptForCountry(travelIndex.value), 'alt-happy');
            travelStage.value = 'pick';
        });
    } else {
        typeThen(async () => {
            await advanceToNextModule('happy');
        });
    }
}

function chooseCountryCount(option: { label: string; value: number }) {
    travelTotal.value = option.value;
    travelIndex.value = 0;

    messages.value.push({
        id: createMessageId('user'),
        speaker: 'user',
        text: option.label,
    });

    isTyping.value = true;
    void scrollToLastMessage();

    window.setTimeout(async () => {
        await pushBotText(promptForCountry(0), 'alt-happy');
        travelStage.value = 'pick';
        isTyping.value = false;
        await scrollToLastMessage();
    }, 650);
}

function selectTravelCountry(country: Country) {
    showCountrySuggestions.value = false;
    countryQuery.value = '';

    messages.value.push({
        id: createMessageId('user'),
        speaker: 'user',
        text: country.name,
    });

    isTyping.value = true;
    void scrollToLastMessage();

    window.setTimeout(async () => {
        if (!country.waitTime) {
            await pushBotText(`Aucun délai requis pour un séjour en ${country.name} 👍`, 'happy');
            isTyping.value = false;
            await scrollToLastMessage();
            proceedAfterCountry();
            return;
        }

        travelWaitLabel.value = country.waitTime;
        await pushBotText(
            `Pour un séjour en ${country.name}, le délai d'attente est de ${country.waitTime} après le retour. Ton retour date-t-il de moins de ${country.waitTime} ?`,
            'alt-happy',
        );
        travelStage.value = 'confirm';
        isTyping.value = false;
        await scrollToLastMessage();
    }, 650);
}

function confirmTravel(recent: boolean) {
    messages.value.push({
        id: createMessageId('user'),
        speaker: 'user',
        text: recent ? `Oui, moins de ${travelWaitLabel.value}` : 'Non, il y a plus longtemps',
    });

    isTyping.value = true;
    void scrollToLastMessage();

    window.setTimeout(async () => {
        if (recent) {
            // Délai non écoulé pour ce pays : on enregistre le délai et on continue.
            recordDelay(labelToMonths(travelWaitLabel.value), travelWaitLabel.value);
            travelStage.value = 'done';
            isTyping.value = false;
            await scrollToLastMessage();
            proceedAfterCountry();
            return;
        }

        travelStage.value = 'done';
        isTyping.value = false;
        await scrollToLastMessage();
        proceedAfterCountry();
    }, 650);
}

moduleQueue.value = [...props.modules];

if (scenario.intro) {
    pushBotNode(scenario.intro);
} else {
    advanceToNextModule();
}
</script>

<template>
    <section class="font-cooper flex min-h-0 w-full flex-1 flex-col overflow-hidden text-stone-950">
        <div ref="messagesContainer" class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
            <div class="mx-auto flex w-full max-w-5xl flex-col gap-2">
                <div
                    v-for="message in messages"
                    :key="message.id"
                    class="chat"
                    :class="[
                        message.speaker === 'bot' ? 'chat-start' : 'chat-end',
                        (message.speaker === 'bot' && message.id === activeBotMessageId) ||
                        (message.speaker === 'user' && message.id === activeUserMessageId && isTyping)
                            ? 'chat-active'
                            : '',
                    ]"
                >
                    <div v-if="message.speaker === 'bot' && message.id === activeBotMessageId" class="chat-image avatar active-sanguy">
                        <div class="flex w-24 items-center justify-center md:w-28">
                            <Transition name="sanguy-face" mode="out-in">
                                <img class="w-full object-contain" :key="sanguyEmotion" :src="currentSanguyImage" alt="Sanguy" />
                            </Transition>
                        </div>
                    </div>
                    <div
                        v-if="message.speaker === 'user' && message.id === activeUserMessageId && isTyping"
                        class="chat-image avatar avatar-placeholder"
                    >
                        <div class="w-10 rounded-full bg-razzmatazz-500 text-white">
                            <span class="text-xs font-semibold">LB</span>
                        </div>
                    </div>

                    <div v-if="message.speaker === 'bot'" class="chat-header chat-label-start font-medium text-razzmatazz-950/70">
                        <span>Sanguy</span>
                    </div>
                    <div v-else class="chat-header chat-label-end font-medium text-razzmatazz-950/70">
                        <span>Moi</span>
                    </div>

                    <div
                        class="chat-bubble message-bubble max-w-[78vw] text-base font-medium leading-relaxed md:max-w-[620px]"
                        :class="
                            message.speaker === 'bot'
                                ? message.nodeType === 'appointment'
                                    ? 'bg-razzmatazz-950 text-white'
                                    : 'bg-razzmatazz-800 text-white'
                                : 'bg-razzmatazz-500 text-white'
                        "
                    >
                        <p>{{ message.text }}</p>

                        <a
                            v-if="message.cta"
                            class="btn mt-4 border-razzmatazz-200 bg-white font-semibold text-razzmatazz-950 hover:border-white hover:bg-razzmatazz-100"
                            :href="message.cta.href"
                        >
                            <span>{{ message.cta.label }}</span>
                        </a>
                    </div>
                </div>

                <div v-if="isTyping" class="chat chat-start chat-active">
                    <div class="chat-header chat-label-start font-medium text-razzmatazz-950/70">
                        <span>Sanguy</span>
                    </div>
                    <div class="chat-bubble bg-razzmatazz-800 text-white">
                        <span class="loading loading-dots loading-sm"></span>
                    </div>
                </div>

                <div v-if="currentAnswers.length > 0" class="chat chat-end chat-active">
                    <div class="chat-image avatar avatar-placeholder">
                        <div class="w-10 rounded-full bg-razzmatazz-500 text-white">
                            <span class="text-xs font-semibold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header chat-label-end font-medium text-razzmatazz-950/70">
                        <span>Moi</span>
                    </div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <button
                            v-for="answer in currentAnswers"
                            :key="answer.id"
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-razzmatazz-50 px-4 py-2 text-left text-base font-medium leading-relaxed text-razzmatazz-950 shadow-sm hover:bg-razzmatazz-100"
                            type="button"
                            @click="answerQuestion(answer)"
                        >
                            <span class="block">{{ answer.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Module voyage : nombre de pays -->
                <div v-if="showTravelCount" class="chat chat-end chat-active">
                    <div class="chat-image avatar avatar-placeholder">
                        <div class="w-10 rounded-full bg-razzmatazz-500 text-white">
                            <span class="text-xs font-semibold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header chat-label-end font-medium text-razzmatazz-950/70">
                        <span>Moi</span>
                    </div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <button
                            v-for="option in travelCountOptions"
                            :key="option.value"
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-razzmatazz-50 px-4 py-2 text-left text-base font-medium leading-relaxed text-razzmatazz-950 shadow-sm hover:bg-razzmatazz-100"
                            type="button"
                            @click="chooseCountryCount(option)"
                        >
                            <span class="block">{{ option.label }}</span>
                        </button>
                    </div>
                </div>

                <!-- Module voyage : sélecteur de pays -->
                <div v-if="showCountryPicker" class="chat chat-end chat-active">
                    <div class="chat-image avatar avatar-placeholder">
                        <div class="w-10 rounded-full bg-razzmatazz-500 text-white">
                            <span class="text-xs font-semibold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header chat-label-end font-medium text-razzmatazz-950/70">
                        <span>Moi</span>
                    </div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <div class="answer-option relative w-72 max-w-full">
                            <input
                                v-model="countryQuery"
                                type="text"
                                placeholder="Rechercher un pays"
                                class="w-full rounded-2xl border-2 border-razzmatazz-200 bg-razzmatazz-50 px-4 py-2 text-base font-medium text-razzmatazz-950 placeholder-red-300 shadow-sm outline-none focus:border-razzmatazz-400"
                                @input="onCountryInput"
                                @focus="onCountryInput"
                            />
                            <ul
                                v-if="showCountrySuggestions && countrySuggestions.length > 0"
                                class="absolute bottom-full left-0 z-20 mb-1 w-full overflow-hidden rounded-2xl border border-razzmatazz-100 bg-white shadow-lg"
                            >
                                <li
                                    v-for="country in countrySuggestions"
                                    :key="country.iso2"
                                    class="flex cursor-pointer items-center gap-2.5 px-4 py-2 text-base font-medium text-razzmatazz-950 hover:bg-razzmatazz-50"
                                    @mousedown.prevent="selectTravelCountry(country)"
                                >
                                    <span
                                        :class="`fi fi-${country.iso2.toLowerCase()} shrink-0 rounded-sm`"
                                        style="width: 1.5em; height: 1.125em;"
                                    ></span>
                                    <span>{{ country.name }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Module voyage : confirmation de la date du retour -->
                <div v-if="showTravelConfirm" class="chat chat-end chat-active">
                    <div class="chat-image avatar avatar-placeholder">
                        <div class="w-10 rounded-full bg-razzmatazz-500 text-white">
                            <span class="text-xs font-semibold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header chat-label-end font-medium text-razzmatazz-950/70">
                        <span>Moi</span>
                    </div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <button
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-razzmatazz-50 px-4 py-2 text-left text-base font-medium leading-relaxed text-razzmatazz-950 shadow-sm hover:bg-razzmatazz-100"
                            type="button"
                            @click="confirmTravel(true)"
                        >
                            <span class="block">Oui, moins de {{ travelWaitLabel }}</span>
                        </button>
                        <button
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-razzmatazz-50 px-4 py-2 text-left text-base font-medium leading-relaxed text-razzmatazz-950 shadow-sm hover:bg-razzmatazz-100"
                            type="button"
                            @click="confirmTravel(false)"
                        >
                            <span class="block">Non, il y a plus longtemps</span>
                        </button>
                    </div>
                </div>

                <!-- Écran final : proposition de rappel (si délai) -->
                <div v-if="resultStage === 'reminder' && !isTyping" class="chat chat-end chat-active">
                    <div class="chat-image avatar avatar-placeholder">
                        <div class="w-10 rounded-full bg-razzmatazz-500 text-white">
                            <span class="text-xs font-semibold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header chat-label-end font-medium text-razzmatazz-950/70">
                        <span>Moi</span>
                    </div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <button
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-razzmatazz-50 px-4 py-2 text-left text-base font-medium leading-relaxed text-razzmatazz-950 shadow-sm hover:bg-razzmatazz-100"
                            type="button"
                            @click="chooseReminder(true)"
                        >
                            <span class="block">Oui, je veux un rappel</span>
                        </button>
                        <button
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-razzmatazz-50 px-4 py-2 text-left text-base font-medium leading-relaxed text-razzmatazz-950 shadow-sm hover:bg-razzmatazz-100"
                            type="button"
                            @click="chooseReminder(false)"
                        >
                            <span class="block">Non merci</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.sanguy-face-enter-active,
.sanguy-face-leave-active {
    transition:
        opacity 200ms ease-in-out,
        transform 200ms ease-in-out;
}

.sanguy-face-enter-from,
.sanguy-face-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(5px);
}

.active-sanguy {
    grid-row: 2;
    animation: active-sanguy-in 200ms ease-out both;
}

.chat-active .chat-bubble,
.chat-active .answer-option {
    animation: chat-active-in 200ms ease-out both;
}

.message-bubble {
    border-radius: 1.125rem;
}

.chat-start .message-bubble {
    border-end-start-radius: 0;
}

.chat-end .message-bubble {
    border-end-end-radius: 0;
}

.chat-label-start {
    padding-left: 1rem;
}

.chat-label-end {
    padding-right: 1rem;
}

@keyframes chat-active-in {
    from {
        opacity: 0;
        transform: translateY(8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes active-sanguy-in {
    from {
        opacity: 0;
        transform: translateY(8px) scale(0.96);
    }

    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
</style>
