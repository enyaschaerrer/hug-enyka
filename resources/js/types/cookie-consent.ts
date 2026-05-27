export type CookieConsentPreferences = {
    version: 1;
    necessary: true;
    analytics: boolean;
    updatedAt: string;
};

export type CookieConsentCategory = {
    id: 'necessary' | 'analytics';
    title: string;
    description: string;
    required: boolean;
};
