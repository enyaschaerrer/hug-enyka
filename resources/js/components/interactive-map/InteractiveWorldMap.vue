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

    const isSelected = selected.value?.numericId === country.numericId;
    const isHovered = !isSelected && hoveredId.value === country.numericId;

    if (isSelected) return riskColor(country.riskScore, 50);
    if (isHovered) return riskColor(country.riskScore, 20);
    return riskColor(country.riskScore);
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

function onBlur() {
    // Délai pour laisser le clic sur une suggestion se déclencher avant la fermeture
    setTimeout(() => {
        showSuggestions.value = false;
    }, 150);
}
</script>

<template>
    <section class="relative w-screen h-svh bg-white flex flex-col overflow-hidden">
        <!-- Recherche -->
        <div class="relative px-4 pt-4 pb-2 z-10 w-full max-w-sm">
            <input
                v-model="searchQuery"
                type="text"
                placeholder="Rechercher un pays..."
                class="input input-bordered input-sm w-full"
                @input="onInput"
                @focus="onInput"
                @blur="onBlur"
            />
            <ul
                v-if="showSuggestions"
                class="absolute left-4 mt-1 bg-base-100 rounded-box shadow-lg z-20 w-[calc(100%-2rem)]"
            >
                <li v-if="suggestions.length === 0" class="px-4 py-2 text-sm text-base-content/50">
                    0 résultat
                </li>
                <li
                    v-for="c in suggestions"
                    :key="c.iso2"
                    class="flex items-center justify-between px-4 py-2 hover:bg-base-200 cursor-pointer text-sm"
                    @mousedown.prevent="selectFromSearch(c)"
                >
                    <span>{{ c.name }}</span>
                    <span :class="`fi fi-${c.iso2.toLowerCase()}`"></span>
                </li>
            </ul>
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
