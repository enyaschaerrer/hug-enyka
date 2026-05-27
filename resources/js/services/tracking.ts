import { hasAnalyticsConsent } from './cookieConsent';

export type TrackingEventPayload = {
    eventType: string;
    metadata?: Record<string, unknown>;
};

export function canTrackAnalytics(): boolean {
    return hasAnalyticsConsent();
}

export function trackAnalyticsEvent(payload: TrackingEventPayload): boolean {
    if (!canTrackAnalytics()) {
        return false;
    }

    window.dispatchEvent(new CustomEvent<TrackingEventPayload>('tracking:event', {
        detail: payload,
    }));

    return true;
}
