export type CoBrandedCollecte = {
    company: {
        name: string;
        logo: string | null;
        shortDescription: string | null;
        slug: string | null;
        address: string | null;
        zipCode: string | null;
        locality: string | null;
        colors: {
            primary: string | null;
            secondary: string | null;
            third: string | null;
        };
    };
    collection: {
        start: string | null;
        end: string | null;
        appointmentUrl: string | null;
        publicUrl: string;
        eligibilityUrl: string;
    };
    auth: {
        canAccess: boolean;
        emailPlaceholder: string;
        accessCodeUrl: string;
        loginUrl: string;
        logoutUrl: string;
    };
};

type AppState = {
    csrfToken: string;
    coBrandedCollecte?: CoBrandedCollecte | null;
};

export function useCoBrandedCollecte() {
    const appState = (window as unknown as { __APP__?: AppState }).__APP__;
    const csrfToken = appState?.csrfToken ?? '';
    const coBrandedCollecte = appState?.coBrandedCollecte;

    const company = coBrandedCollecte?.company ?? {
        name: 'Entreprise',
        logo: null,
        shortDescription: null,
        slug: null,
        address: null,
        zipCode: null,
        locality: null,
        colors: { primary: null, secondary: null, third: null },
    };

    const collection = coBrandedCollecte?.collection ?? {
        start: null,
        end: null,
        appointmentUrl: null,
        publicUrl: '',
        eligibilityUrl: '',
    };

    const auth = coBrandedCollecte?.auth ?? {
        canAccess: false,
        emailPlaceholder: 'exemple@entreprise.ch',
        accessCodeUrl: '',
        loginUrl: '',
        logoutUrl: '',
    };

    return {
        csrfToken,
        company,
        collection,
        auth,
    };
}
