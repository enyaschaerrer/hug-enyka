import { useCoBrandedCollecte } from './useCoBrandedCollecte';

export type QuizStep = 'quiz' | 'chat' | 'done';

/**
 * Envoi « fire-and-forget » des événements de suivi du parcours co-brandé :
 * progression dans le questionnaire (quiz_step) et clic sur le lien OneDoc.
 * Les erreurs sont silencieuses : le tracking ne doit jamais bloquer l'utilisateur.
 */
export function useCoBrandedTracking() {
    const { csrfToken, tracking } = useCoBrandedCollecte();

    function post(url: string, payload?: Record<string, unknown>) {
        if (!url) return;

        void fetch(url, {
            method: 'POST',
            keepalive: true,
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload ?? {}),
        }).catch(() => {
            /* tracking best-effort : on ignore les erreurs réseau */
        });
    }

    function trackQuizStep(step: QuizStep) {
        post(tracking.quizStepUrl, { step });
    }

    function trackOnedocClick() {
        post(tracking.onedocUrl);
    }

    return {
        trackQuizStep,
        trackOnedocClick,
    };
}
