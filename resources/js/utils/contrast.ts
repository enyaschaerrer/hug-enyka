const DARK_TEXT = '#111827';
const LIGHT_TEXT = '#ffffff';

type Rgb = {
    r: number;
    g: number;
    b: number;
};

function normalizeHex(hex: string): string | null {
    const value = hex.trim();

    if (/^#[0-9a-fA-F]{6}$/.test(value)) {
        return value;
    }

    if (/^#[0-9a-fA-F]{3}$/.test(value)) {
        return `#${value[1]}${value[1]}${value[2]}${value[2]}${value[3]}${value[3]}`;
    }

    return null;
}

function hexToRgb(hex: string): Rgb | null {
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

function srgbToLinear(value: number): number {
    const channel = value / 255;

    return channel <= 0.03928
        ? channel / 12.92
        : ((channel + 0.055) / 1.055) ** 2.4;
}

function relativeLuminance(hex: string): number | null {
    const rgb = hexToRgb(hex);

    if (!rgb) {
        return null;
    }

    return 0.2126 * srgbToLinear(rgb.r)
        + 0.7152 * srgbToLinear(rgb.g)
        + 0.0722 * srgbToLinear(rgb.b);
}

export function contrastRatio(firstColor: string, secondColor: string): number | null {
    const firstLuminance = relativeLuminance(firstColor);
    const secondLuminance = relativeLuminance(secondColor);

    if (firstLuminance === null || secondLuminance === null) {
        return null;
    }

    const lighter = Math.max(firstLuminance, secondLuminance);
    const darker = Math.min(firstLuminance, secondLuminance);

    return (lighter + 0.05) / (darker + 0.05);
}

export function readableTextColor(backgroundColor: string | null | undefined): string {
    if (!backgroundColor) {
        return LIGHT_TEXT;
    }

    const lightContrast = contrastRatio(backgroundColor, LIGHT_TEXT);
    const darkContrast = contrastRatio(backgroundColor, DARK_TEXT);

    if (lightContrast === null || darkContrast === null) {
        return LIGHT_TEXT;
    }

    return darkContrast > lightContrast ? DARK_TEXT : LIGHT_TEXT;
}
