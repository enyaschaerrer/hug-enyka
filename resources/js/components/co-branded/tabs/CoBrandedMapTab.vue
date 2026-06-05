<script setup lang="ts">
import { computed, ref } from 'vue';
import { geoNaturalEarth1, geoPath, type GeoPermissibleObjects } from 'd3-geo';
import { feature } from 'topojson-client';
import type { Topology } from 'topojson-specification';
import worldData from 'world-atlas/countries-110m.json';
import countriesJson from '../../../data/country-donation-rules.json';
import type { Country } from '../../../types/interactive-map';

const countries = countriesJson as Country[];
const countryById = new Map(countries.map(c => [c.numericId, c]));

const width = 960;
const height = 500;
const projection = geoNaturalEarth1().scale(185).translate([width / 2, height / 2]);
const pathGen = geoPath(projection);

const topo = worldData as unknown as Topology;
const allFeatures = (feature(topo, topo.objects.countries as any) as any)
    .features.filter((f: any) => f.id !== undefined && Number(f.id) !== 10) as Array<{ id?: string | number }>;

const mapFeatures = allFeatures.filter(f => getCountry(f.id) !== null);
const unknownFeatures = allFeatures.filter(f => getCountry(f.id) === null);

const hoveredId = ref<number | null>(null);
const tooltipPos = ref<{ x: number; y: number } | null>(null);
const selected = ref<Country | null>(null);
const searchQuery = ref('');
const suggestions = ref<Country[]>([]);
const showSuggestions = ref(false);

// Mapping waitTime → couleur (palette HUG)
const waitTimeColor: Record<string, string> = {
    none:       'var(--color-vistablue-300)',  // Pas de délai
    '28 jours': 'var(--color-pictonblue-200)',
    '4 mois':   'var(--color-catskillwhite-500)',
    '6 mois':   'var(--color-razzmatazz-500)',
};

// Quand hover : couleur plus contrastée
const waitTimeColorHover: Record<string, string> = {
    none:       'var(--color-vistablue-600)',
    '28 jours': 'var(--color-pictonblue-300)',
    '4 mois':   'var(--color-catskillwhite-700)',
    '6 mois':   'var(--color-razzmatazz-800)',
};

const legendItems = [
    { label: 'Pas de délai', color: 'var(--color-vistablue-300)' },
    { label: '28 jours',     color: 'var(--color-pictonblue-200)' },
    { label: '4 mois',       color: 'var(--color-catskillwhite-500)' },
    { label: '6 mois',       color: 'var(--color-razzmatazz-500)' },
];

const hoveredCountry = computed(() => {
    if (hoveredId.value === null) return null;
    return countryById.get(hoveredId.value) ?? null;
});

const hoveredWaitLabel = computed(() => {
    if (!hoveredCountry.value) return null;
    return hoveredCountry.value.waitTime ?? 'Aucun délai';
});

function getCountry(id?: string | number): Country | null {
    if (id === undefined || id === null) return null;
    return countryById.get(Number(id)) ?? null;
}

function getFill(f: { id?: string | number }): string {
    const country = getCountry(f.id);
    if (!country) return 'var(--color-catskillwhite-100)';
    const key = country.waitTime ?? 'none';
    const isHovered = hoveredId.value === country.numericId;
    const palette = isHovered ? waitTimeColorHover : waitTimeColor;
    return palette[key] ?? 'var(--color-catskillwhite-200)';
}

function getStrokeWidth(f: { id?: string | number }): number {
    const country = getCountry(f.id);
    if (!country) return 0.3;
    if (selected.value?.numericId === country.numericId) return 2;
    if (hoveredId.value === country.numericId) return 1.6;
    return 0.4;
}

function getPath(f: unknown): string {
    return pathGen(f as GeoPermissibleObjects) ?? '';
}

function onMouseEnter(f: { id?: string | number }) {
    if (f.id === undefined) return;
    hoveredId.value = Number(f.id);
    // Positionne le tooltip au-dessus du centroïde du pays (coords viewBox)
    const [cx, cy] = pathGen.centroid(f as GeoPermissibleObjects);
    if (!isNaN(cx) && !isNaN(cy)) {
        tooltipPos.value = { x: cx, y: cy };
    }
}

function onMouseLeave() {
    hoveredId.value = null;
    tooltipPos.value = null;
}

function normalize(s: string): string {
    return s.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
}

function onSearchInput() {
    const q = normalize(searchQuery.value);
    if (!q) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }
    suggestions.value = countries.filter(
        c =>
            normalize(c.name).includes(q) ||
            normalize(c.iso2).includes(q) ||
            normalize(c.iso3).includes(q) ||
            c.aliases.some(a => normalize(a).includes(q)),
    ).slice(0, 6);
    showSuggestions.value = true;
}

function selectFromSearch(c: Country) {
    selected.value = c;
    searchQuery.value = c.name;
    showSuggestions.value = false;
}

function handleClick(f: { id?: string | number }) {
    const country = getCountry(f.id);
    if (country) selected.value = country;
}
</script>

<template>
    <section class="mx-auto max-w-6xl px-6 py-10">
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <!-- Carte -->
            <div class="rounded-2xl border-2 border-catskillwhite-300 bg-white p-4">
                <svg
                    :viewBox="`0 0 ${width} ${height}`"
                    preserveAspectRatio="xMidYMid meet"
                    class="block h-auto w-full overflow-visible"
                >
                    <!-- Territoires sans données : neutres -->
                    <path
                        v-for="f in unknownFeatures"
                        :key="`u-${String(f.id)}`"
                        :d="getPath(f)"
                        fill="var(--color-catskillwhite-100)"
                        stroke="white"
                        stroke-width="0.3"
                    />
                    <!-- Pays connus : interactifs et colorés -->
                    <path
                        v-for="f in mapFeatures"
                        :key="String(f.id)"
                        :d="getPath(f)"
                        :fill="getFill(f)"
                        :stroke-width="getStrokeWidth(f)"
                        stroke="var(--color-catskillwhite-800)"
                        class="cursor-pointer transition-all"
                        @mouseenter="onMouseEnter(f)"
                        @mouseleave="onMouseLeave"
                        @click="handleClick(f)"
                    />

                    <!-- Tooltip SVG : nom + délai juste au-dessus du pays -->
                    <g
                        v-if="hoveredCountry && tooltipPos"
                        :transform="`translate(${tooltipPos.x}, ${tooltipPos.y - 20})`"
                        class="pointer-events-none"
                    >
                        <rect
                            x="-90"
                            y="-36"
                            width="180"
                            height="48"
                            rx="8"
                            fill="var(--color-catskillwhite-900)"
                        />
                        <text
                            x="0"
                            y="-18"
                            text-anchor="middle"
                            fill="white"
                            class="font-cooper text-caption font-semibold"
                        >{{ hoveredCountry.name }}</text>
                        <text
                            x="0"
                            y="0"
                            text-anchor="middle"
                            fill="var(--color-catskillwhite-200)"
                            class="font-cooper text-caption"
                        >{{ hoveredWaitLabel }}</text>
                    </g>
                </svg>
            </div>

            <!-- Panneau droite : recherche + légende -->
            <div class="flex flex-col gap-6">
                <!-- Recherche -->
                <div class="relative">
                    <span
                        class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-catskillwhite-700"
                        style="font-size: 20px;"
                        aria-hidden="true"
                    >search</span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher"
                        class="w-full rounded-full border-2 border-catskillwhite-800 bg-white py-2.5 pl-12 pr-4 text-body text-catskillwhite-900 placeholder-catskillwhite-600 outline-none focus:border-razzmatazz-700"
                        @input="onSearchInput"
                        @focus="onSearchInput"
                    />
                    <ul
                        v-if="showSuggestions && suggestions.length > 0"
                        class="absolute left-0 top-full z-20 mt-1 w-full overflow-hidden rounded-xl border border-catskillwhite-300 bg-white shadow-lg"
                    >
                        <li
                            v-for="c in suggestions"
                            :key="c.iso2"
                            class="cursor-pointer px-4 py-2 text-body text-catskillwhite-900 hover:bg-catskillwhite-100"
                            @mousedown.prevent="selectFromSearch(c)"
                        >
                            {{ c.name }}
                        </li>
                    </ul>
                </div>

                <!-- Temps d'attente : légende -->
                <div class="rounded-2xl border-2 border-catskillwhite-300 bg-white p-5">
                    <h3 class="text-heading-t2 text-catskillwhite-900">Temps d'attente</h3>
                    <p class="mt-2 text-caption text-catskillwhite-700">
                        Les zones épidémiques (Zika, Dengue, Ebola selon période) peuvent entraîner une exclusion variable. À vérifier au cas par cas.
                    </p>

                    <div class="mt-4 space-y-2">
                        <div
                            v-for="item in legendItems"
                            :key="item.label"
                            class="flex items-center gap-3"
                        >
                            <div
                                class="h-5 w-5 rounded border border-catskillwhite-800"
                                :style="{ backgroundColor: item.color }"
                            ></div>
                            <span class="text-body text-catskillwhite-900">{{ item.label }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pays sélectionné (carte info simple) -->
                <div
                    v-if="selected"
                    class="rounded-2xl border-2 border-catskillwhite-300 bg-white p-5"
                >
                    <h4 class="text-heading-t3 text-catskillwhite-900">{{ selected.name }}</h4>
                    <p v-if="selected.waitTime" class="mt-1 text-body text-catskillwhite-800">
                        Délai d'attente : <strong>{{ selected.waitTime }}</strong>
                    </p>
                    <p v-else class="mt-1 text-body text-catskillwhite-800">
                        Aucun délai
                    </p>
                    <p v-if="selected.description" class="mt-2 text-caption text-catskillwhite-700">
                        {{ selected.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
