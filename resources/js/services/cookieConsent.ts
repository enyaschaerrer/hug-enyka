import type { CookieConsentPreferences } from '../types/cookie-consent';

const STORAGE_KEY = 'hug-enyka-cookie-consent-v1';

function isValidConsentPreferences(value: unknown): value is CookieConsentPreferences {
    if (!value || typeof value !== 'object') {
        return false;
    }

    const preferences = value as Partial<CookieConsentPreferences>;

    return preferences.version === 1
        && preferences.necessary === true
        && typeof preferences.analytics === 'boolean'
        && typeof preferences.updatedAt === 'string';
}

export function getCookieConsentPreferences(): CookieConsentPreferences | null {
    const storedPreferences = window.localStorage.getItem(STORAGE_KEY);

    if (!storedPreferences) {
        return null;
    }

    try {
        const parsedPreferences: unknown = JSON.parse(storedPreferences);

        if (isValidConsentPreferences(parsedPreferences)) {
            return parsedPreferences;
        }
    } catch {
        return null;
    }

    return null;
}

export function saveCookieConsentPreferences(analytics: boolean): CookieConsentPreferences {
    const preferences: CookieConsentPreferences = {
        version: 1,
        necessary: true,
        analytics,
        updatedAt: new Date().toISOString(),
    };

    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(preferences));
    window.dispatchEvent(new CustomEvent<CookieConsentPreferences>('cookie-consent:changed', {
        detail: preferences,
    }));

    return preferences;
}

export function hasCookieConsentDecision(): boolean {
    return getCookieConsentPreferences() !== null;
}

export function hasAnalyticsConsent(): boolean {
    return getCookieConsentPreferences()?.analytics === true;
}
