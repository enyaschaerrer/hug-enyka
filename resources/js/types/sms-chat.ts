export type SmsNodeId = string;

export type SmsNodeType = 'question' | 'appointment';

export type SmsSpeaker = 'bot' | 'user';

export type SanguyEmotion = 'happy' | 'angry' | 'alt-happy' | 'alt-angry';

export type SmsAnswer = {
    id: string;
    label: string;
    next: SmsNodeId;
    sanguyEmotion?: SanguyEmotion;
};

export type SmsCta = {
    label: string;
    href: string;
};

export type SmsQuestionNode = {
    id: SmsNodeId;
    type: 'question';
    speaker: 'bot';
    text: string;
    answers: SmsAnswer[];
};

export type SmsAppointmentNode = {
    id: SmsNodeId;
    type: 'appointment';
    speaker: 'bot';
    text: string;
    cta: SmsCta;
};

export type SmsScenarioNode = SmsQuestionNode | SmsAppointmentNode;

export type SmsScenario = {
    start: SmsNodeId;
    nodes: Record<SmsNodeId, SmsScenarioNode>;
};

export type SmsMessage = {
    id: string;
    speaker: SmsSpeaker;
    text: string;
    nodeType?: SmsNodeType;
    cta?: SmsCta;
};
