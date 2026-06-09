export type SmsNodeId = string;

export type SmsNodeType = 'question' | 'appointment' | 'travel';

export type SmsSpeaker = 'bot' | 'user';

export type SanguyEmotion = 'happy' | 'angry' | 'alt-happy' | 'alt-angry';

export type SmsAnswer = {
    id: string;
    label: string;
    next: SmsNodeId;
    sanguyEmotion?: SanguyEmotion;
    /** Issue enregistrée quand cette réponse est choisie (délai agrégé / inéligibilité définitive). */
    outcome?: SmsOutcome;
};

export type SmsCta = {
    label: string;
    href: string;
};

/**
 * Issue d'un module : un délai temporaire (la conversation continue, le délai est agrégé)
 * ou une inéligibilité définitive (la conversation s'arrête).
 */
export type SmsOutcome = {
    severity: 'delay' | 'permanent';
    /** Durée du délai en mois (pour comparer/agréger). Absent si « à confirmer ». */
    months?: number;
    /** Libellé affiché, ex. « 4 mois ». */
    label?: string;
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

/**
 * Nœud « voyage » : affiche un sélecteur de pays (basé sur country-donation-rules).
 * La suite (affichage du délai, question de date) est gérée dynamiquement par le composant.
 */
export type SmsTravelNode = {
    id: SmsNodeId;
    type: 'travel';
    speaker: 'bot';
    text: string;
};

export type SmsScenarioNode = SmsQuestionNode | SmsAppointmentNode | SmsTravelNode;

export type SmsModuleId = string;

export type SmsScenario = {
    /** Nœud d'introduction affiché une fois au début (optionnel). */
    intro?: SmsNodeId;
    /** Nœud final affiché quand tous les modules enregistrés sont passés sans blocage. */
    appointment: SmsNodeId;
    /** Point d'entrée (id de nœud) de chaque module, indexé par id de module. */
    moduleEntries: Record<SmsModuleId, SmsNodeId>;
    nodes: Record<SmsNodeId, SmsScenarioNode>;
};

/** Valeur de `next` signifiant « ce module est terminé, passer au module enregistré suivant ». */
export const SMS_NEXT_MODULE = '__next__';

export type SmsMessage = {
    id: string;
    speaker: SmsSpeaker;
    text: string;
    nodeType?: SmsNodeType;
    cta?: SmsCta;
};
