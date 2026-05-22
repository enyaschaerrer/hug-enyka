<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import scenarioData from '../../data/sms-scenarios.json';
import type { SmsAnswer, SmsMessage, SmsScenario } from '../../types/sms-chat';

const scenario = scenarioData as SmsScenario;
const messagesContainer = ref<HTMLElement | null>(null);
const currentNodeId = ref(scenario.start);
const messages = ref<SmsMessage[]>([]);
const isTyping = ref(false);

const currentNode = computed(() => scenario.nodes[currentNodeId.value]);
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
                    :class="message.speaker === 'bot' ? 'chat-start' : 'chat-end'"
                >
                    <div class="chat-image avatar avatar-placeholder" :class="message.speaker === 'user' ? 'avatar-online' : ''">
                        <div
                            class="w-10 rounded-full"
                            :class="message.speaker === 'bot' ? 'bg-red-950 text-white' : 'bg-red-500 text-white'"
                        >
                            <span class="text-xs font-bold">{{ message.speaker === 'bot' ? 'HUG' : 'LB' }}</span>
                        </div>
                    </div>

                    <div v-if="message.speaker === 'bot'" class="chat-header text-red-950/70">HUG</div>
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

                <div v-if="isTyping" class="chat chat-start">
                    <div class="chat-image avatar avatar-placeholder">
                        <div class="w-10 rounded-full bg-red-950 text-white">
                            <span class="text-xs font-bold">HUG</span>
                        </div>
                    </div>
                    <div class="chat-header text-red-950/70">HUG</div>
                    <div class="chat-bubble bg-red-800 text-white">
                        <span class="loading loading-dots loading-sm"></span>
                    </div>
                </div>

                <div v-if="currentAnswers.length > 0" class="chat chat-end">
                    <div class="chat-image avatar avatar-online avatar-placeholder">
                        <div class="w-10 rounded-full bg-red-500 text-white">
                            <span class="text-xs font-bold">LB</span>
                        </div>
                    </div>
                    <div class="chat-header text-red-950/70">Choisir une reponse</div>
                    <div class="flex max-w-[78vw] flex-col items-end gap-2 md:max-w-[620px]">
                        <button
                            v-for="answer in currentAnswers"
                            :key="answer.id"
                            class="w-fit max-w-full cursor-pointer rounded-2xl bg-red-50 px-4 py-2 text-left text-base leading-relaxed text-red-950 shadow-sm hover:bg-red-100"
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
