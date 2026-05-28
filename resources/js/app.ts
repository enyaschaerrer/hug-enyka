import { createApp } from 'vue';
import App from './App.vue';
import Companies from './components/public/Companies.vue';
import Podium from './components/public/Podium.vue';

const spaRoot = document.getElementById('app');
if (spaRoot) {
    createApp(App).mount(spaRoot);
}

const podiumRoot = document.getElementById('podium');
if (podiumRoot) {
    const initialPodiums = JSON.parse(podiumRoot.dataset.podiums ?? '[]');
    createApp(Podium, { initialPodiums }).mount(podiumRoot);
}

const companiesRoot = document.getElementById('companies');
if (companiesRoot) {
    const initialCompanies = JSON.parse(companiesRoot.dataset.companies ?? '[]');
    createApp(Companies, { initialCompanies }).mount(companiesRoot);
}
