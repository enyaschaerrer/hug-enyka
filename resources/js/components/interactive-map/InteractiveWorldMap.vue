<script setup lang="ts">
import { ref } from 'vue';
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

const projection = geoNaturalEarth1().scale(153).translate([width / 2, height / 2]);
const pathGen = geoPath(projection);

// Extraction des features depuis le world atlas, sans l'Antarctique (id 10)
const topo = worldData as unknown as Topology;
const mapFeatures = (feature(topo, topo.objects.countries as any) as any)
    .features.filter((f: any) => Number(f.id) !== 10) as Array<{ id?: string | number }>;

// Etat
const selected = ref<Country | null>(null);
const hoveredId = ref<number | null>(null);
const searchQuery = ref('');
const suggestions = ref<Country[]>([]);
const showSuggestions = ref(false);

function getCountry(id?: string | number): Country | null {
    if (id === undefined || id === null) return null;
    return countryById.get(Number(id)) ?? null;
}

// Heatmap : riskScore 0 = blanc, 100 = rouge saturé
// Le paramètre darken assombrit la couleur pour le hover et la sélection
function riskColor(score: number, darken = 0): string {
    const base = 255 - darken;
    const v = Math.round(base * (1 - score / 100));
    return `rgb(${base},${v},${v})`;
}

function getFill(f: { id?: string | number }): string {
    const country = getCountry(f.id);
    if (!country) return '#d1d5db';

    // On utilise le riskScore uniquement si le pays a un délai d'attente.
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

function handleClick(f: { id?: string | number }) {
    const country = getCountry(f.id);
    if (country) selected.value = country;
}

// Couleur de la bordure de la carte info : gris si safe, rouge sinon
function cardBorderColor(score: number): string {
    return score < 5 ? '#d1d5db' : riskColor(score);
}

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
}

const searchFocused = ref(false);

function onBlur() {
    searchFocused.value = false;
    // Délai pour laisser le clic sur une suggestion se déclencher avant la fermeture
    setTimeout(() => {
        showSuggestions.value = false;
    }, 150);
}
</script>

<template>
    <section class="relative w-screen h-svh bg-gray-50 flex flex-col overflow-hidden">
        <!-- Recherche -->
        <div class="flex justify-center px-4 pt-5 pb-2 z-10">
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
                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-md outline-none transition focus:border-gray-400 focus:shadow-lg"
                    @input="onInput"
                    @focus="searchFocused = true; onInput()"
                    @blur="onBlur"
                />
                <ul
                    v-if="showSuggestions"
                    class="absolute left-0 top-full mt-1 w-full overflow-hidden rounded-xl border border-gray-100 bg-white shadow-lg z-20"
                >
                    <li v-if="suggestions.length === 0" class="px-4 py-2.5 text-sm text-gray-400">
                        0 résultat
                    </li>
                    <li
                        v-for="c in suggestions"
                        :key="c.iso2"
                        class="flex items-center justify-between px-4 py-2.5 hover:bg-gray-50 cursor-pointer text-sm text-gray-900"
                        @mousedown.prevent="selectFromSearch(c)"
                    >
                        <span>{{ c.name }}</span>
                        <span :class="`fi fi-${c.iso2.toLowerCase()} rounded overflow-hidden`" style="width: 1.4em; height: 1.05em;"></span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Carte SVG -->
        <div class="flex-1 overflow-hidden">
            <svg
                :viewBox="`0 0 ${width} ${height}`"
                preserveAspectRatio="xMidYMid meet"
                class="w-full h-full"
            >
                <path
                    v-for="f in mapFeatures"
                    :key="String(f.id)"
                    :d="getPath(f)"
                    :fill="getFill(f)"
                    :stroke-width="getStrokeWidth(f)"
                    stroke="#444"
                    class="cursor-pointer"
                    @click="handleClick(f)"
                    @mouseenter="hoveredId = f.id !== undefined ? Number(f.id) : null"
                    @mouseleave="hoveredId = null"
                />
            </svg>
        </div>

        <!-- Carte info du pays sélectionné -->
        <transition name="fade">
            <div
                v-if="selected"
                class="card bg-base-100 shadow-xl absolute bottom-12 right-4 p-4 min-w-48 max-w-60 z-10"
                :style="{ borderLeft: `4px solid ${cardBorderColor(selected.riskScore)}` }"
            >
                <div class="flex items-center gap-2 mb-1">
                    <span :class="`fi fi-${selected.iso2.toLowerCase()} text-xl`"></span>
                    <span class="font-semibold text-sm">{{ selected.name }}</span>
                </div>
                <div v-if="selected.waitTime" class="text-sm font-medium text-error">
                    Attendre {{ selected.waitTime }}
                </div>
                <div v-else class="text-sm font-medium text-success">Safe</div>
                <p v-if="selected.description" class="text-xs text-base-content/60 mt-1">
                    {{ selected.description }}
                </p>
            </div>
        </transition>

        <!-- Légende heatmap -->
        <div
            class="absolute bottom-4 right-4 flex items-center gap-2 text-xs bg-white/90 px-2 py-1 rounded shadow"
        >
            <span class="text-base-content/50">0 contrainte</span>
            <div
                class="w-16 h-2 rounded"
                style="
                    background: linear-gradient(to right, rgb(255, 255, 255), rgb(255, 0, 0));
                    border: 1px solid #e5e7eb;
                "
            ></div>
            <span class="text-base-content/50">contrainte forte</span>
        </div>
    </section>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
