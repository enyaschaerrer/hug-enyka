export type Country = {
    name: string;
    iso2: string;
    iso3: string;
    numericId: number;
    aliases: string[];
    riskScore: number;
    waitTime: string | null;
    description: string | null;
};
