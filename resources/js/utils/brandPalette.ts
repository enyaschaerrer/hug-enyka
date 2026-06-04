type Hsl = {
    h: number;
    s: number;
    l: number;
};

type PaletteRole = 'primary' | 'secondary' | 'third';

type BrandPalette = {
    primaryColor: string;
    secondaryColor: string;
    thirdColor: string;
    sourceRole: PaletteRole;
};

const ROLE_TARGETS: Record<PaletteRole, { l: number }> = {
    primary: { l: 0.46 },
    secondary: { l: 0.72 },
    third: { l: 0.97 },
};

function clamp(value: number, min: number, max: number): number {
    return Math.min(Math.max(value, min), max);
}

function normalizeHex(hex: string): string | null {
    const value = hex.trim();

    if (/^#[0-9a-fA-F]{6}$/.test(value)) {
        return value.toLowerCase();
    }

    if (/^#[0-9a-fA-F]{3}$/.test(value)) {
        return `#${value[1]}${value[1]}${value[2]}${value[2]}${value[3]}${value[3]}`.toLowerCase();
    }

    return null;
}

function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
    const normalized = normalizeHex(hex);

    if (!normalized) {
        return null;
    }

    return {
        r: Number.parseInt(normalized.slice(1, 3), 16),
        g: Number.parseInt(normalized.slice(3, 5), 16),
        b: Number.parseInt(normalized.slice(5, 7), 16),
    };
}

function rgbToHex(red: number, green: number, blue: number): string {
    return `#${[red, green, blue]
        .map((channel) => clamp(Math.round(channel), 0, 255).toString(16).padStart(2, '0'))
        .join('')}`;
}

function rgbToHsl(red: number, green: number, blue: number): Hsl {
    const r = red / 255;
    const g = green / 255;
    const b = blue / 255;
    const max = Math.max(r, g, b);
    const min = Math.min(r, g, b);
    const delta = max - min;
    const lightness = (max + min) / 2;

    if (delta === 0) {
        return { h: 0, s: 0, l: lightness };
    }

    const saturation = delta / (1 - Math.abs(2 * lightness - 1));
    let hue = 0;

    switch (max) {
        case r:
            hue = ((g - b) / delta) % 6;
            break;
        case g:
            hue = (b - r) / delta + 2;
            break;
        default:
            hue = (r - g) / delta + 4;
            break;
    }

    hue *= 60;

    if (hue < 0) {
        hue += 360;
    }

    return { h: hue, s: saturation, l: lightness };
}

function hslToRgb({ h, s, l }: Hsl): { r: number; g: number; b: number } {
    const chroma = (1 - Math.abs(2 * l - 1)) * s;
    const huePrime = h / 60;
    const x = chroma * (1 - Math.abs((huePrime % 2) - 1));
    let r1 = 0;
    let g1 = 0;
    let b1 = 0;

    if (huePrime >= 0 && huePrime < 1) {
        r1 = chroma;
        g1 = x;
    } else if (huePrime < 2) {
        r1 = x;
        g1 = chroma;
    } else if (huePrime < 3) {
        g1 = chroma;
        b1 = x;
    } else if (huePrime < 4) {
        g1 = x;
        b1 = chroma;
    } else if (huePrime < 5) {
        r1 = x;
        b1 = chroma;
    } else {
        r1 = chroma;
        b1 = x;
    }

    const match = l - chroma / 2;

    return {
        r: (r1 + match) * 255,
        g: (g1 + match) * 255,
        b: (b1 + match) * 255,
    };
}

function hslToHex(hsl: Hsl): string {
    const rgb = hslToRgb(hsl);
    return rgbToHex(rgb.r, rgb.g, rgb.b);
}

function inferSourceRole(lightness: number): PaletteRole {
    const entries = Object.entries(ROLE_TARGETS) as Array<[PaletteRole, { l: number }]>;

    return entries.reduce((closest, current) => {
        const [closestRole, closestTarget] = closest;
        const [currentRole, currentTarget] = current;

        return Math.abs(currentTarget.l - lightness) < Math.abs(closestTarget.l - lightness)
            ? [currentRole, currentTarget]
            : [closestRole, closestTarget];
    })[0];
}

function buildNeutralPalette(lightness: number): BrandPalette {
    const sourceRole = inferSourceRole(lightness);
    const palette: Record<PaletteRole, string> = {
        primary: hslToHex({ h: 0, s: 0, l: 0.46 }),
        secondary: hslToHex({ h: 0, s: 0, l: 0.72 }),
        third: hslToHex({ h: 0, s: 0, l: 0.97 }),
    };

    palette[sourceRole] = hslToHex({ h: 0, s: 0, l: lightness });

    return {
        primaryColor: palette.primary,
        secondaryColor: palette.secondary,
        thirdColor: palette.third,
        sourceRole,
    };
}

function buildColorfulPalette(source: Hsl): BrandPalette {
    const sourceRole = inferSourceRole(source.l);
    const strongSaturation = clamp(Math.max(source.s, 0.58), 0.52, 0.9);
    const mediumSaturation = clamp(Math.max(source.s * 0.62, 0.22), 0.18, 0.5);
    const softSaturation = clamp(Math.max(source.s * 0.28, 0.08), 0.06, 0.24);

    const palette: Record<PaletteRole, Hsl> = {
        primary: { h: source.h, s: strongSaturation, l: 0.46 },
        secondary: { h: source.h, s: mediumSaturation, l: 0.72 },
        third: { h: source.h, s: softSaturation, l: 0.97 },
    };

    palette[sourceRole] = source;

    return {
        primaryColor: hslToHex(palette.primary),
        secondaryColor: hslToHex(palette.secondary),
        thirdColor: hslToHex(palette.third),
        sourceRole,
    };
}

export function buildBrandPalette(sourceHex: string): BrandPalette {
    const rgb = hexToRgb(sourceHex);

    if (!rgb) {
        return {
            primaryColor: '#c81e1e',
            secondaryColor: '#fecaca',
            thirdColor: '#fff5f5',
            sourceRole: 'primary',
        };
    }

    const hsl = rgbToHsl(rgb.r, rgb.g, rgb.b);

    if (hsl.s < 0.08 || hsl.l <= 0.03 || hsl.l >= 0.97) {
        return buildNeutralPalette(hsl.l);
    }

    return buildColorfulPalette(hsl);
}
