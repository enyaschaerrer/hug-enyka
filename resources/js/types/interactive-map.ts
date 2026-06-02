export type WaitTimeDetailed = {
    outdoor: string;
    other: string;
    labelOutdoor?: string;
    labelOther?: string;
};

export type Country = {
    name: string;
    iso2: string;
    iso3: string;
    numericId: number;
    aliases: string[];
    waitTime: string | null;
    waitTimeDetailed: WaitTimeDetailed | null;
    isEligible: boolean;
    description: string | null;
};
