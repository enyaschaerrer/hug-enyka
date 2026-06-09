<script setup lang="ts">
import { computed, ref } from 'vue';
import { geoNaturalEarth1, geoPath, type GeoPermissibleObjects } from 'd3-geo';
import { feature } from 'topojson-client';
import type { Topology } from 'topojson-specification';
import worldData from 'world-atlas/countries-110m.json';
import 'flag-icons/css/flag-icons.min.css';
import countriesJson from '../../../data/country-donation-rules.json';
import type { Country } from '../../../types/interactive-map';

const countries = countriesJson as Country[];
const countryById = new Map(countries.map(c => [c.numericId, c]));

const VB_W = 960;
const VB_H = 500;
const projection = geoNaturalEarth1().scale(185).translate([VB_W / 2, VB_H / 2]);
const pathGen = geoPath(projection);

const topo = worldData as unknown as Topology;
const allFeatures = (feature(topo, topo.objects.countries as any) as any)
    .features.filter((f: any) => f.id !== undefined && Number(f.id) !== 10) as Array<{ id?: string | number }>;

const mapFeatures = allFeatures.filter(f => getCountry(f.id) !== null);
const unknownFeatures = allFeatures.filter(f => getCountry(f.id) === null);

const svgRef = ref<SVGSVGElement | null>(null);

const hoveredId = ref<number | null>(null);
const tooltipPos = ref<{ x: number; y: number } | null>(null);
const selected = ref<Country | null>(null);
const searchQuery = ref('');
const suggestions = ref<Country[]>([]);
const showSuggestions = ref(false);

// Zoom / pan
const INITIAL_SCALE = (window.innerWidth < 768 ? 1.5 : 1.2)
// Décalages initiaux : pousse la carte vers le bas et vers la gauche pour que tous les pays rentrent
const INITIAL_TY_OFFSET = 50;
const INITIAL_TX_OFFSET = -40;
const scale = ref(INITIAL_SCALE);
const tx = ref(VB_W / 2 * (1 - INITIAL_SCALE) + INITIAL_TX_OFFSET);
const ty = ref(VB_H / 2 * (1 - INITIAL_SCALE) + INITIAL_TY_OFFSET);
const isPanning = ref(false);
let panStartX = 0;
let panStartY = 0;
let panInitTx = 0;
let panInitTy = 0;
// Distingue un vrai clic d'un glissement (pan) pour ne pas ouvrir la modale après un déplacement
let didPan = false;

const MIN_SCALE = 1;
const MAX_SCALE = 10;

const transform = computed(() => `translate(${tx.value} ${ty.value}) scale(${scale.value})`);

const waitTimeColor: Record<string, string> = {
    none:       'var(--color-pictonblue-200)',
    '28 jours': 'var(--color-vistablue-300)',
    '4 mois':   'var(--color-catskillwhite-500)',
    '6 mois':   'var(--color-razzmatazz-500)',
};
const waitTimeColorHover: Record<string, string> = {
    none:       'var(--color-pictonblue-300)',
    '28 jours': 'var(--color-vistablue-400)',
    '4 mois':   'var(--color-catskillwhite-700)',
    '6 mois':   'var(--color-razzmatazz-800)',
};
const legendItems = [
    { label: 'Pas de délai', color: 'var(--color-pictonblue-200)' },
    { label: '28 jours',     color: 'var(--color-vistablue-300)' },
    { label: '4 mois',       color: 'var(--color-catskillwhite-500)' },
    { label: '6 mois',       color: 'var(--color-razzmatazz-500)' },
];

const hoveredCountry = computed(() => {
    if (hoveredId.value === null) return null;
    return countryById.get(hoveredId.value) ?? null;
});
const hoveredWaitLabel = computed(() => hoveredCountry.value ? (hoveredCountry.value.waitTime ?? 'Aucun délai') : null);

function getCountry(id?: string | number): Country | null {
    if (id === undefined || id === null) return null;
    return countryById.get(Number(id)) ?? null;
}
function getFill(f: { id?: string | number }): string {
    const country = getCountry(f.id);
    if (!country) return 'var(--color-catskillwhite-100)';
    const key = country.waitTime ?? 'none';
    const isHovered = hoveredId.value === country.numericId;
    return (isHovered ? waitTimeColorHover : waitTimeColor)[key] ?? 'var(--color-catskillwhite-200)';
}
function getStrokeWidth(f: { id?: string | number }): number {
    const country = getCountry(f.id);
    if (!country) return 0.6 / scale.value;
    if (selected.value?.numericId === country.numericId) return 2.5 / scale.value;
    if (hoveredId.value === country.numericId) return 2 / scale.value;
    return 0.9 / scale.value;
}
function getPath(f: unknown): string {
    return pathGen(f as GeoPermissibleObjects) ?? '';
}

function onMouseEnter(f: { id?: string | number }) {
    if (f.id === undefined) return;
    hoveredId.value = Number(f.id);
    const [cx, cy] = pathGen.centroid(f as GeoPermissibleObjects);
    if (!isNaN(cx) && !isNaN(cy)) tooltipPos.value = { x: cx, y: cy };
}
function onMouseLeave() {
    hoveredId.value = null;
    tooltipPos.value = null;
}

// ----- Zoom / pan -----

function screenToSvg(clientX: number, clientY: number): { x: number; y: number } {
    const el = svgRef.value;
    if (!el) return { x: 0, y: 0 };
    const rect = el.getBoundingClientRect();
    return {
        x: ((clientX - rect.left) / rect.width) * VB_W,
        y: ((clientY - rect.top) / rect.height) * VB_H,
    };
}

function zoomAt(factor: number, svgX: number, svgY: number) {
    const newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, scale.value * factor));
    const ratio = newScale / scale.value;
    if (ratio === 1) return;
    tx.value = svgX - (svgX - tx.value) * ratio;
    ty.value = svgY - (svgY - ty.value) * ratio;
    scale.value = newScale;
    clampTranslate();
}

function clampTranslate() {
    const overflow = 100;
    const minTx = VB_W - VB_W * scale.value - overflow;
    const maxTx = overflow;
    const minTy = VB_H - VB_H * scale.value - overflow;
    const maxTy = overflow;
    tx.value = Math.max(minTx, Math.min(maxTx, tx.value));
    ty.value = Math.max(minTy, Math.min(maxTy, ty.value));
}

function zoomIn() {
    zoomAt(1.3, VB_W / 2, VB_H / 2);
}
function zoomOut() {
    zoomAt(1 / 1.3, VB_W / 2, VB_H / 2);
}

function onWheel(e: WheelEvent) {
    e.preventDefault();
    const { x, y } = screenToSvg(e.clientX, e.clientY);
    const factor = e.deltaY < 0 ? 1.15 : 1 / 1.15;
    zoomAt(factor, x, y);
}

function startPan(e: MouseEvent) {
    if (e.button !== 0) return;
    isPanning.value = true;
    didPan = false;
    panStartX = e.clientX;
    panStartY = e.clientY;
    panInitTx = tx.value;
    panInitTy = ty.value;
}
function onPan(e: MouseEvent) {
    if (!isPanning.value || !svgRef.value) return;
    // Au-delà de quelques pixels, on considère que c'est un glissement et non un clic
    if (Math.hypot(e.clientX - panStartX, e.clientY - panStartY) > 4) didPan = true;
    const rect = svgRef.value.getBoundingClientRect();
    const scaleX = VB_W / rect.width;
    const scaleY = VB_H / rect.height;
    tx.value = panInitTx + (e.clientX - panStartX) * scaleX;
    ty.value = panInitTy + (e.clientY - panStartY) * scaleY;
    clampTranslate();
}
function endPan() {
    isPanning.value = false;
}

// ----- Touch (mobile) : pinch zoom + drag pan -----

let touchInitDist = 0;
let touchInitScale = 1;
let touchInitTx = 0;
let touchInitTy = 0;
let touchInitSvgX = 0;
let touchInitSvgY = 0;

function touchDist(touches: TouchList): number {
    const dx = touches[0].clientX - touches[1].clientX;
    const dy = touches[0].clientY - touches[1].clientY;
    return Math.hypot(dx, dy);
}
function touchMid(touches: TouchList): { x: number; y: number } {
    return {
        x: (touches[0].clientX + touches[1].clientX) / 2,
        y: (touches[0].clientY + touches[1].clientY) / 2,
    };
}

function onTouchStart(e: TouchEvent) {
    if (e.touches.length === 2) {
        e.preventDefault();
        touchInitDist = touchDist(e.touches);
        const mid = touchMid(e.touches);
        const svgMid = screenToSvg(mid.x, mid.y);
        touchInitSvgX = svgMid.x;
        touchInitSvgY = svgMid.y;
        touchInitScale = scale.value;
        touchInitTx = tx.value;
        touchInitTy = ty.value;
        isPanning.value = false;
    } else if (e.touches.length === 1) {
        isPanning.value = true;
        panStartX = e.touches[0].clientX;
        panStartY = e.touches[0].clientY;
        panInitTx = tx.value;
        panInitTy = ty.value;
    }
}
function onTouchMove(e: TouchEvent) {
    if (e.touches.length === 2) {
        e.preventDefault();
        const newDist = touchDist(e.touches);
        if (touchInitDist === 0) return;
        const factor = newDist / touchInitDist;
        const newScale = Math.max(MIN_SCALE, Math.min(MAX_SCALE, touchInitScale * factor));
        const ratio = newScale / touchInitScale;
        // Zoom centré sur le midpoint du début du pinch (en coords SVG)
        tx.value = touchInitSvgX - (touchInitSvgX - touchInitTx) * ratio;
        ty.value = touchInitSvgY - (touchInitSvgY - touchInitTy) * ratio;
        scale.value = newScale;
        clampTranslate();
    } else if (e.touches.length === 1 && isPanning.value && svgRef.value) {
        const rect = svgRef.value.getBoundingClientRect();
        const scaleX = VB_W / rect.width;
        const scaleY = VB_H / rect.height;
        tx.value = panInitTx + (e.touches[0].clientX - panStartX) * scaleX;
        ty.value = panInitTy + (e.touches[0].clientY - panStartY) * scaleY;
        clampTranslate();
    }
}
function onTouchEnd() {
    isPanning.value = false;
    touchInitDist = 0;
}

// ----- Recherche -----

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
    searchQuery.value = c.name;
    showSuggestions.value = false;
    selected.value = c;
}

function handleClick(f: { id?: string | number }) {
    // Ignore le clic qui suit un glissement de la carte
    if (didPan) return;
    const country = getCountry(f.id);
    if (!country) return;
    selected.value = country;
}

function closePopup() {
    selected.value = null;
}
</script>

<template>
    <section class="mx-auto max-w-5xl px-5 py-5 lg:py-10">
        <!-- Disposition : carte à gauche, panneau (note / recherche / délais) à droite.
             Sur mobile : empilé, panneau au-dessus de la carte. -->
        <div class="flex flex-col gap-6 lg:flex-row h-[600px] lg:h-[372px]">
            <!-- Carte (gauche) -->
            <div
                class="relative flex-1 overflow-hidden rounded-2xl border-2 border-catskillwhite-300 bg-white"
            >
                <svg
                    ref="svgRef"
                    :viewBox="`0 0 ${VB_W} ${VB_H}`"
                    preserveAspectRatio="xMidYMid meet"
                    :class="['block h-full w-full touch-none', isPanning ? 'cursor-grabbing' : 'cursor-grab']"
                    @wheel="onWheel"
                    @mousedown="startPan"
                    @mousemove="onPan"
                    @mouseup="endPan"
                    @mouseleave="endPan"
                    @touchstart="onTouchStart"
                    @touchmove="onTouchMove"
                    @touchend="onTouchEnd"
                    @touchcancel="onTouchEnd"
                >
                    <g :transform="transform">
                        <path
                            v-for="f in unknownFeatures"
                            :key="`u-${String(f.id)}`"
                            :d="getPath(f)"
                            fill="var(--color-catskillwhite-100)"
                            stroke="var(--color-catskillwhite-400)"
                            :stroke-width="0.5 / scale"
                        />
                        <path
                            v-for="f in mapFeatures"
                            :key="String(f.id)"
                            :d="getPath(f)"
                            :fill="getFill(f)"
                            :stroke-width="getStrokeWidth(f)"
                            stroke="var(--color-catskillwhite-800)"
                            class="cursor-pointer transition-all"
                            @mouseenter.stop="onMouseEnter(f)"
                            @mouseleave.stop="onMouseLeave"
                            @click.stop="handleClick(f)"
                        />
                    </g>

                    <!-- Tooltip hover : hors du <g> zoomé pour garder une taille constante. Caché sur mobile. -->
                    <g
                        v-if="hoveredCountry && tooltipPos"
                        :transform="`translate(${tx + tooltipPos.x * scale}, ${ty + tooltipPos.y * scale - 24})`"
                        class="pointer-events-none hidden sm:inline"
                    >
                        <!-- foreignObject : permet d'utiliser le drapeau (classe CSS .fi) en HTML dans le SVG -->
                        <foreignObject x="-118" y="-54" width="200" height="64">
                            <div
                                xmlns="http://www.w3.org/1999/xhtml"
                                class="flex h-full w-full items-center justify-center gap-3 rounded-[10px] bg-catskillwhite-900 px-2 font-cooper text-white"
                            >
                                <span
                                    :class="`fi fi-${hoveredCountry.iso2.toLowerCase()} shrink-0 rounded-sm`"
                                    style="width: 26px; height: 19px;"
                                ></span>
                                <div class="min-w-0">
                                    <div class="truncate font-semibold" style="font-size: 16px; line-height: 1.2;">{{ hoveredCountry.name }}</div>
                                    <div class="truncate text-catskillwhite-200" style="font-size: 15px; line-height: 1.2;">{{ hoveredWaitLabel }}</div>
                                </div>
                            </div>
                        </foreignObject>
                    </g>
                </svg>

                <!-- Boutons zoom (bas-droite, dans la section carte) -->
                <div class="absolute bottom-3 right-3 flex flex-col gap-2">
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border-2 border-catskillwhite-800 bg-white text-catskillwhite-900 shadow transition hover:bg-catskillwhite-100 disabled:opacity-30"
                        aria-label="Zoom avant"
                        :disabled="scale >= MAX_SCALE"
                        @click="zoomIn"
                    >
                        <span class="material-symbols-outlined" style="font-size: 20px;" aria-hidden="true">add</span>
                    </button>
                    <button
                        type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border-2 border-catskillwhite-800 bg-white text-catskillwhite-900 shadow transition hover:bg-catskillwhite-100 disabled:opacity-30"
                        aria-label="Zoom arrière"
                        :disabled="scale <= MIN_SCALE"
                        @click="zoomOut"
                    >
                        <span class="material-symbols-outlined" style="font-size: 20px;" aria-hidden="true">remove</span>
                    </button>
                </div>
            </div>

            <!-- Panneau (droite) -->
            <div class="flex w-full shrink-0 flex-col gap-4 lg:w-72">
                <!-- Recherche -->
                <div class="relative w-full order-1">
                    <span
                        class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-catskillwhite-700"
                        style="font-size: 20px;"
                        aria-hidden="true"
                    >search</span>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Rechercher un pays"
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
                            class="flex cursor-pointer items-center gap-2.5 px-4 py-2 text-body text-catskillwhite-900 hover:bg-catskillwhite-100"
                            @mousedown.prevent="selectFromSearch(c)"
                        >
                            <span :class="`fi fi-${c.iso2.toLowerCase()} shrink-0 rounded-sm`" style="width: 1.5em; height: 1.125em;"></span>
                            <span>{{ c.name }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Note « à vérifier » -->
                <div class="order-4 rounded-xl border border-catskillwhite-300 bg-white/90 p-4 text-body text-catskillwhite-800">
                    Les zones épidémiques (Zika, Dengue, Ebola selon période) peuvent entraîner une exclusion variable. À vérifier au cas par cas.
                </div>

                <!-- Délais (légende) -->
                <div class="order-3 rounded-xl border border-catskillwhite-300 bg-white/95 p-4 text-body shadow-sm">
                    <p class="mb-2 font-medium text-catskillwhite-900">Délais</p>
                    <div class="grid grid-cols-2 gap-2 lg:grid-cols-1 lg:space-y-1.5">
                        <div
                            v-for="item in legendItems"
                            :key="item.label"
                            class="flex items-center gap-2"
                        >
                            <div
                                class="h-3 w-3 shrink-0 rounded border border-catskillwhite-800"
                                :style="{ backgroundColor: item.color }"
                            ></div>
                            <span class="text-catskillwhite-900">{{ item.label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popup pays (modal centrée + backdrop cliquable) -->
        <div
            v-if="selected"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closePopup"
        >
            <div class="relative w-full max-w-md rounded-2xl border-2 border-montecarlo-700 bg-white p-6 shadow-xl">
                <button
                    type="button"
                    class="absolute right-3 top-3 inline-flex h-8 w-8 items-center justify-center rounded-full text-catskillwhite-700 transition hover:bg-catskillwhite-100"
                    aria-label="Fermer"
                    @click="closePopup"
                >
                    <span class="material-symbols-outlined" style="font-size: 20px;">close</span>
                </button>

                <h3 class="flex items-center gap-3 text-heading-t2 text-catskillwhite-900">
                    <span :class="`fi fi-${selected.iso2.toLowerCase()} shrink-0 rounded-sm`" style="width: 1.6em; height: 1.2em;"></span>
                    <span>{{ selected.name }}</span>
                </h3>
                <p v-if="selected.waitTime" class="mt-3 text-body text-catskillwhite-800">
                    Délai d'attente : <strong>{{ selected.waitTime }}</strong>
                </p>
                <p v-else class="mt-3 text-body text-catskillwhite-800">
                    Aucun délai
                </p>
                <p v-if="selected.description" class="mt-3 text-caption text-catskillwhite-700">
                    {{ selected.description }}
                </p>
            </div>
        </div>
    </section>
</template>
