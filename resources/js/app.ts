import { createApp } from 'vue';
import App from './App.vue';
import Companies from './components/public/Companies.vue';
import Podium from './components/public/Podium.vue';
import CollecteForm from './components/public/CollecteForm.vue';
import CookieConsentModal from './components/modals/CookieConsentModal.vue';
import PrizeForm from './components/public/PrizeForm.vue'

const prizeForm = document.getElementById('prize-form')
if (prizeForm) createApp(PrizeForm).mount(prizeForm)

const el = document.getElementById('collecte-form')
if (el) createApp(CollecteForm).mount(el)

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
    const props: Record<string, unknown> = { initialCompanies };
    if (companiesRoot.dataset.title) props.title = companiesRoot.dataset.title;
    if (companiesRoot.dataset.description) props.description = companiesRoot.dataset.description;
    if (companiesRoot.dataset.showTrophies !== undefined) {
        props.showTrophies = companiesRoot.dataset.showTrophies !== 'false';
    }
    createApp(Companies, props).mount(companiesRoot);
}

const cookieConsentRoot = document.getElementById('cookie-consent-root');
if (cookieConsentRoot) {
    createApp(CookieConsentModal).mount(cookieConsentRoot);
}
