<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { geoNaturalEarth1, geoPath, type GeoPermissibleObjects } from 'd3-geo';
import { feature } from 'topojson-client';
import type { Topology } from 'topojson-specification';
import worldData from 'world-atlas/countries-110m.json';
import 'flag-icons/css/flag-icons.min.css';

import countriesJson from '../../data/country-donation-rules.json';
import type { Country } from '../../types/interactive-map';

const countries = countriesJson as Country[];
const countryById = new Map(countries.map(c => [c.numericId, c]));

const width = 960;
const height = 500;

const projection = geoNaturalEarth1().scale(185).translate([width / 2, height / 2]);
const pathGen = geoPath(projection);

// Extraction des features depuis le world atlas, sans l'Antarctique (id 10)
const topo = worldData as unknown as Topology;
const allFeatures = (feature(topo, topo.objects.countries as any) as any)
    .features.filter((f: any) => f.id !== undefined && Number(f.id) !== 10) as Array<{ id?: string | number }>;

// Séparation : pays connus (dans le JSON) vs territoires sans données
const mapFeatures = allFeatures.filter(f => getCountry(f.id) !== null);
const unknownFeatures = allFeatures.filter(f => getCountry(f.id) === null);

// Etat
const svgRef = ref<SVGSVGElement | null>(null);
const sectionRef = ref<HTMLElement | null>(null);
const selected = ref<Country | null>(null);
const tooltipPos = ref<{ x: number; y: number } | null>(null);
const hoveredId = ref<number | null>(null);
const searchQuery = ref('');
const suggestions = ref<Country[]>([]);
const showSuggestions = ref(false);
const searchFocused = ref(false);

function getCountry(id?: string | number): Country | null {
    if (id === undefined || id === null) return null;
    return countryById.get(Number(id)) ?? null;
}

// Heatmap : riskScore 0 = blanc, 100 = rouge saturé
function riskColor(score: number, darken = 0): string {
    const base = 255 - darken;
    const v = Math.round(base * (1 - score / 100));
    return `rgb(${base},${v},${v})`;
}

function getFill(f: { id?: string | number }): string {
    const country = getCountry(f.id);
    if (!country) return '#e5e7eb';

    // Un pays safe (waitTime null) est toujours blanc sur la heatmap.
    const score = country.waitTime === null ? 0 : country.riskScore;

    const isSelected = selected.value?.numericId === country.numericId;
    const isHovered = !isSelected && hoveredId.value === country.numericId;

    if (isSelected) return riskColor(score, 50);
    if (isHovered) return riskColor(score, 20);
    return riskColor(score);
}

function getStrokeWidth(f: { id?: string | number }): number {
    return selected.value?.numericId === getCountry(f.id)?.numericId ? 1.5 : 0.3;
}

function getPath(f: unknown): string {
    return pathGen(f as GeoPermissibleObjects) ?? '';
}

// Convertit les coordonnées SVG (viewBox) en coordonnées relatives à la section
function computeTooltipPos(f: unknown) {
    const [cx, cy] = pathGen.centroid(f as GeoPermissibleObjects);
    if (isNaN(cx) || isNaN(cy) || !svgRef.value || !sectionRef.value) return;

    const svgRect = svgRef.value.getBoundingClientRect();
    const sectionRect = sectionRef.value.getBoundingClientRect();
    const scale = Math.min(svgRect.width / width, svgRect.height / height);
    const offsetX = (svgRect.width - width * scale) / 2;
    const offsetY = (svgRect.height - height * scale) / 2;

    tooltipPos.value = {
        x: (svgRect.left - sectionRect.left) + offsetX + cx * scale,
        y: (svgRect.top - sectionRect.top) + offsetY + cy * scale,
    };
}

function handleClick(f: { id?: string | number }) {
    const country = getCountry(f.id);
    if (!country) return;
    selected.value = country;
    computeTooltipPos(f);
}

function deselect() {
    selected.value = null;
    tooltipPos.value = null;
}

let observer: IntersectionObserver | null = null;

onMounted(() => {
    if (!sectionRef.value) return;
    observer = new IntersectionObserver(
        ([entry]) => {
            if (!entry.isIntersecting) deselect();
        },
        { threshold: 0.3 },
    );
    observer.observe(sectionRef.value);
});

onUnmounted(() => {
    observer?.disconnect();
});

// Style positionnel : position absolute dans la section, s'adapte au quadrant
const tooltipStyle = computed(() => {
    if (!tooltipPos.value || !sectionRef.value) return { display: 'none' };
    const { x, y } = tooltipPos.value;
    const w = sectionRef.value.offsetWidth;
    const h = sectionRef.value.offsetHeight;
    const toRight = x < w * 0.6;
    const toBottom = y < h * 0.55;
    return {
        position: 'absolute' as const,
        left: toRight ? `${x + 12}px` : 'auto',
        right: toRight ? 'auto' : `${w - x + 12}px`,
        top: toBottom ? `${y + 12}px` : 'auto',
        bottom: toBottom ? 'auto' : `${h - y + 12}px`,
        zIndex: 20,
    };
});

// Recherche avec normalisation des accents
function normalize(s: string): string {
    return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
}

function onInput() {
    const q = normalize(searchQuery.value);
    if (!q) {
        suggestions.value = [];
        showSuggestions.value = false;
        return;
    }
    const matches = countries.filter(
        c =>
            normalize(c.name).includes(q) ||
            normalize(c.iso2).includes(q) ||
            normalize(c.iso3).includes(q) ||
            c.aliases.some(a => normalize(a).includes(q)),
    );
    suggestions.value = matches.slice(0, 5);
    showSuggestions.value = true;
}

function selectFromSearch(c: Country) {
    selected.value = c;
    searchQuery.value = '';
    showSuggestions.value = false;
    const f = mapFeatures.find(f => Number(f.id) === c.numericId);
    if (f) {
        computeTooltipPos(f);
    } else {
        // Pays trop petit pour apparaître sur la carte : tooltip au centre de la section
        if (sectionRef.value) {
            tooltipPos.value = {
                x: sectionRef.value.offsetWidth / 2,
                y: sectionRef.value.offsetHeight / 2,
            };
        }
    }
}

function onBlur() {
    searchFocused.value = false;
    setTimeout(() => {
        showSuggestions.value = false;
    }, 150);
}
</script>

<template>
    <section ref="sectionRef" class="font-cooper relative w-screen h-svh bg-rose-50 flex flex-col overflow-hidden">
        <div class="mx-auto w-full max-w-3xl px-4 pt-5 text-center z-10">
            <h2 class="cooper-text-baseline text-2xl font-bold text-gray-950">Delais de don dans le monde</h2>
            <p class="cooper-text-baseline mt-1 text-sm font-normal text-gray-500">
                Recherchez un pays ou explorez la carte pour connaitre les delais avant de pouvoir donner son sang.
            </p>
        </div>

        <!-- Recherche -->
        <div class="flex justify-center px-4 pt-4 pb-2 z-10">
            <div class="relative w-full max-w-sm">
                <span
                    class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none transition-colors"
                    :class="searchFocused ? 'text-gray-600' : 'text-gray-300'"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7" />
                        <line x1="16.5" y1="16.5" x2="22" y2="22" stroke-linecap="round" />
                    </svg>
                </span>
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher un pays..."
                    class="cooper-input-baseline w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm font-medium text-gray-900 placeholder-gray-400 shadow-md outline-none transition focus:border-gray-400 focus:shadow-lg"
                    @input="onInput"
                    @focus="searchFocused = true; onInput()"
                    @blur="onBlur"
                />
                <ul
                    v-if="showSuggestions"
                    class="absolute left-0 top-full mt-1 w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg z-20"
                >
                    <li v-if="suggestions.length === 0" class="px-4 py-2.5 text-sm font-medium text-gray-400">
                        0 résultat
                    </li>
                    <li
                        v-for="c in suggestions"
                        :key="c.iso2"
                        class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm font-medium text-gray-900"
                        @mousedown.prevent="selectFromSearch(c)"
                    >
                        <span class="cooper-baseline">{{ c.name }}</span>
                        <span :class="`fi fi-${c.iso2.toLowerCase()} rounded overflow-hidden`" style="width: 1.4em; height: 1.05em;"></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Carte SVG -->
        <div class="flex-1 overflow-hidden">
            <svg
                ref="svgRef"
                :viewBox="`0 0 ${width} ${height}`"
                preserveAspectRatio="xMidYMid meet"
                class="mx-auto block h-full w-[98%]"
                @click="deselect"
            >
                <!-- Territoires sans données : fond neutre, pas de bordure ni d'interaction -->
                <path
                    v-for="f in unknownFeatures"
                    :key="`u-${String(f.id)}`"
                    :d="getPath(f)"
                    fill="#e5e7eb"
                    stroke="none"
                />
                <!-- Pays connus : interactifs et colorés -->
                <path
                    v-for="f in mapFeatures"
                    :key="String(f.id)"
                    :d="getPath(f)"
                    :fill="getFill(f)"
                    :stroke-width="getStrokeWidth(f)"
                    stroke="#444"
                    class="cursor-pointer"
                    @click.stop="handleClick(f)"
                    @mouseenter="hoveredId = f.id !== undefined ? Number(f.id) : null"
                    @mouseleave="hoveredId = null"
                />
            </svg>
        </div>

        <!-- Tooltip pays sélectionné, positionnée près du centroïde -->
        <transition name="fade">
            <div v-if="selected && tooltipPos" :key="selected.iso2" :style="tooltipStyle" class="w-52 rounded-2xl border border-gray-100 bg-white p-4 shadow-xl">
                <div class="flex items-center gap-2 mb-2">
                    <span
                        :class="`fi fi-${selected.iso2.toLowerCase()} rounded overflow-hidden`"
                        style="width: 1.5em; height: 1.1em;"
                    ></span>
                    <span class="cooper-baseline font-semibold text-sm text-gray-800">{{ selected.name }}</span>
                </div>
                <div v-if="selected.waitTime" class="text-sm font-medium text-red-500">
                    <span class="cooper-baseline">Attendre {{ selected.waitTime }}</span>
                </div>
                <div v-else class="text-sm font-medium text-emerald-500">
                    <span class="cooper-baseline">Aucun délai</span>
                </div>
                <p v-if="selected.description" class="cooper-text-baseline mt-1.5 text-xs font-normal text-gray-400 leading-snug">
                    {{ selected.description }}
                </p>
            </div>
        </transition>

        <!-- Légende heatmap -->
        <div class="absolute bottom-4 right-4 flex items-center gap-2 text-xs font-medium bg-white/90 px-2 py-1 rounded shadow">
            <span class="cooper-baseline text-gray-500">Aucun délai</span>
            <div
                class="w-16 h-2 rounded"
                style="background: linear-gradient(to right, rgb(255,255,255), rgb(255,0,0)); border: 1px solid #e5e7eb;"
            ></div>
            <span class="cooper-baseline text-gray-500">12 mois</span>
        </div>
    </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease-in-out, transform 0.2s ease-in-out;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
