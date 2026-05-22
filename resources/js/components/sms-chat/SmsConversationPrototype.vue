<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import scenarioData from '../../data/sms-scenarios.json';
import type { SanguyEmotion, SmsAnswer, SmsMessage, SmsScenario } from '../../types/sms-chat';

const scenario = scenarioData as SmsScenario;
const sanguyImages: Record<SanguyEmotion, string> = {
    happy: '/img/sanguy/sanguy_happy.png',
    angry: '/img/sanguy/sanguy-angry.png',
    'alt-happy': '/img/sanguy/sanguy-alt-happy.png',
    'alt-angry': '/img/sanguy/sanguy-alt-angry.png',
};

const messagesContainer = ref<HTMLElement | null>(null);
const currentNodeId = ref(scenario.start);
const messages = ref<SmsMessage[]>([]);
const isTyping = ref(false);
const sanguyEmotion = ref<SanguyEmotion>('happy');

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

async function pushBotNode(nodeId: string) {
    const node = scenario.nodes[nodeId];

    if (!node) {
        return;
    }

    currentNodeId.value = nodeId;
    messages.value.push({
        id: createMessageId('bot'),
        speaker: 'bot',
        text: node.text,
        nodeType: node.type,
        cta: node.type === 'appointment' ? node.cta : undefined,
    });

    await scrollToLastMessage();
}

async function answerQuestion(answer: SmsAnswer) {
    messages.value.push({
        id: createMessageId('user'),
        speaker: 'user',
        text: answer.label,
    });

    if (answer.sanguyEmotion) {
        sanguyEmotion.value = answer.sanguyEmotion;
    }

    isTyping.value = true;
    await scrollToLastMessage();

    window.setTimeout(async () => {
        await pushBotNode(answer.next);
        isTyping.value = false;
    }, 650);
}

pushBotNode(scenario.start);
</script>

<template>
    <section class="flex min-h-[100svh] w-screen flex-col overflow-hidden bg-rose-50 text-stone-950">
        <div ref="messagesContainer" class="min-h-0 flex-1 overflow-y-auto px-4 py-6">
            <div class="mx-auto flex w-full max-w-5xl flex-col gap-2">
                <div
                    v-for="message in messages"
                    :key="message.id"
                    class="chat"
                    :class="[
                        message.speaker === 'bot' ? 'chat-start' : 'chat-end',
                        (message.speaker === 'bot' && message.id === activeBotMessageId && !isTyping) ||
                        (message.speaker === 'user' && message.id === activeUserMessageId && isTyping)
                            ? 'chat-active'
                            : '',
                    ]"
                >
                    <div v-if="message.speaker === 'bot' && message.id === activeBotMessageId && !isTyping" class="chat-image avatar">
                        <div class="flex w-16 items-center justify-center">
                            <Transition name="sanguy-face" mode="out-in">
                                <img class="w-full object-contain" :key="sanguyEmotion" :src="currentSanguyImage" alt="Sanguy" />
                            </Transition>
                        </div>
                    </div>
                    <div
                        v-if="message.speaker === 'user' && message.id === activeUserMessageId && isTyping"
                        class="chat-image avatar avatar-online avatar-placeholder"
                    >
                        <div class="w-10 rounded-full bg-red-500 text-white">
                            <span class="text-xs font-bold">LB</span>
                        </div>
                    </div>

                    <div v-if="message.speaker === 'bot'" class="chat-header text-red-950/70">Sanguy</div>
                    <div v-else class="chat-header text-red-950/70">Moi</div>

                    <div
                        class="chat-bubble max-w-[78vw] text-base leading-relaxed md:max-w-[620px]"
                        :class="
                            message.speaker === 'bot'
                                ? message.nodeType === 'appointment'
                                    ? 'bg-red-950 text-white'
                                    : 'bg-red-800 text-white'
                                : 'bg-red-500 text-white'
                        "
                    >
                        <p>{{ message.text }}</p>

                        <a
                            v-if="message.cta"
                            class="btn mt-4 border-red-200 bg-white text-red-950 hover:border-white hover:bg-red-100"
                            :href="message.cta.href"
                        >
                            {{ message.cta.label }}
                        </a>
                    </div>
                </div>

                <div v-if="isTyping" class="chat chat-start chat-active">
                    <div class="chat-image avatar">
                        <div class="flex w-16 items-center justify-center">
                            <Transition name="sanguy-face" mode="out-in">
                                <img class="w-full object-contain" :key="sanguyEmotion" :src="currentSanguyImage" alt="Sanguy" />
                            </Transition>
                        </div>
                    </div>
                    <div class="chat-header text-red-950/70">Sanguy</div>
                    <div class="chat-bubble bg-red-800 text-white">
                        <span class="loading loading-dots loading-sm"></span>
                    </div>
                </div>

                <div v-if="currentAnswers.length > 0" class="chat chat-end chat-active">
                    <div class="chat-image avatar avatar-online avatar-placeholder">
                        <div class="w-10 rounded-full bg-red-500 text-white">
                            <span class="text-xs font-bold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header text-red-950/70">Moi</div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <button
                            v-for="answer in currentAnswers"
                            :key="answer.id"
                            class="answer-option w-fit max-w-full cursor-pointer rounded-2xl bg-red-50 px-4 py-2 text-left text-base leading-relaxed text-red-950 shadow-sm hover:bg-red-100"
                            type="button"
                            @click="answerQuestion(answer)"
                        >
                            {{ answer.label }}
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
        opacity 240ms ease,
        transform 240ms ease;
}

.sanguy-face-enter-from,
.sanguy-face-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(4px);
}

.chat-active .chat-bubble,
.chat-active .answer-option {
    animation: chat-active-in 220ms ease both;
}

@keyframes chat-active-in {
    from {
        opacity: 0;
        transform: translateY(6px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
