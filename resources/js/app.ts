import { createApp } from 'vue';
import App from './App.vue';
import Companies from './components/public/Companies.vue';
import Podium from './components/public/Podium.vue';
import CollecteForm from './components/public/CollecteForm.vue'

const el = document.getElementById('collecte-form')
if (el) createApp(CollecteForm).mount(el)

const spaRoot = document.getElementById('app');
if (spaRoot) {
    createApp(App).mount(spaRoot);
}

const podiumRoot = document.getElementById('podium');
if (podiumRoot) {
    createApp(Podium).mount(podiumRoot);
}

const companiesRoot = document.getElementById('companies');
if (companiesRoot) {
    createApp(Companies).mount(companiesRoot);
}
