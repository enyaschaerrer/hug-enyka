<script setup lang="ts">
import { ref } from 'vue';
import CollecteForm from './CollecteForm.vue';


const step = ref<1 | 2 | 3>(1);
const loading = ref(false);
const submitted = ref(false);
const showCollecte = ref(false);

const appState = (window as unknown as { __APP__?: { csrfToken: string } }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

const form = ref({
    labelled: null as boolean | null,
    name: '',
    email: '',
    message: '',
});

function nextStep() {
    if (form.value.labelled === false) {
        showCollecte.value = true; 
        return;
    }
    step.value = 2;
}

function goBack() {
    step.value = 1;
}

async function handleSubmit() {
    loading.value = true;

    try {
        const res = await fetch('/prize/inscription', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                labelled: form.value.labelled,
                name:     form.value.name,
                email:    form.value.email,
                message:  form.value.message,
            }),
        });

        if (res.ok) {
            submitted.value = true;
        } else if (res.status === 422) {
            const data = await res.json();
            console.error('Erreurs:', data.errors);
        }
    } catch {
        console.error('Erreur réseau.');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <CollecteForm v-if="showCollecte" />

    <div v-else class="mx-auto max-w-xl px-4 py-16">

        <!-- Succès -->
        <div v-if="submitted" class="py-16 text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-100">
                <svg class="h-8 w-8 text-rose-500" viewBox="0 0 24 24" fill="none">
                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h2 class="text-2xl font-black text-stone-900">Demande envoyée !</h2>
            <p class="mt-3 text-stone-500">Nous avons bien reçu votre candidature et reviendrons vers vous rapidement.</p>
        </div>

        <template v-else>
            <h2 class="mb-10 text-2xl font-black text-center text-stone-900">Participez au Prix Du Coeur</h2>
            <!-- Indicateur d'étapes -->
            <div class="mb-10 flex items-center gap-3">
                <div
                    class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                    :class="step >= 1 ? 'bg-rose-500 text-white' : 'bg-stone-200 text-stone-500'"
                >1</div>
                <div class="h-px flex-1" :class="step >= 2 ? 'bg-rose-500' : 'bg-stone-200'"></div>
                <div
                    class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold"
                    :class="step >= 2 ? 'bg-rose-500 text-white' : 'bg-stone-200 text-stone-500'"
                >2</div>
            </div>

            <!-- Étape 1 -->
            <div v-if="step === 1" class="bg-white p-8 rounded-2xl">
                <p class="mb-8 text-l font-black text-stone-900">Êtes-vous une entreprise déjà labellisée "Cœur D'Honneur" ?</p>

                <div class="space-y-4">
                    <label
                        class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 p-5 transition"
                        :class="form.labelled === true ? 'border-rose-400 bg-rose-50' : 'border-stone-200 bg-white hover:border-rose-200'"
                    >
                        <input
                            type="radio"
                            class="sr-only"
                            :value="true"
                            v-model="form.labelled"
                        />
                        <div
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                            :class="form.labelled === true ? 'border-rose-500 bg-rose-500' : 'border-stone-300'"
                        >
                            <div v-if="form.labelled === true" class="h-2 w-2 rounded-full bg-white"></div>
                        </div>
                        <span class="font-semibold text-stone-900">Oui, nous sommes labellisés</span>
                    </label>

                    <label
                        class="flex cursor-pointer items-center gap-4 rounded-2xl border-2 p-5 transition"
                        :class="form.labelled === false ? 'border-rose-400 bg-rose-50' : 'border-stone-200 bg-white hover:border-rose-200'"
                    >
                        <input
                            type="radio"
                            class="sr-only"
                            :value="false"
                            v-model="form.labelled"
                        />
                        <div
                            class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2"
                            :class="form.labelled === false ? 'border-rose-500 bg-rose-500' : 'border-stone-300'"
                        >
                            <div v-if="form.labelled === false" class="h-2 w-2 rounded-full bg-white"></div>
                        </div>
                        <span class="font-semibold text-stone-900">Non, nous ne sommes pas labellisés</span>
                    </label>
                </div>

                <div class="mt-8 flex justify-end">
                    <button
                        :disabled="form.labelled === null"
                        class="rounded-xl bg-rose-500 px-6 py-3 font-semibold text-white transition hover:bg-rose-600 disabled:opacity-40"
                        @click="nextStep"
                    >
                        Continuer →
                    </button>
                </div>
            </div>

            <!-- Étape 2 -->
            <div v-if="step === 2">

                <div class="space-y-5 rounded-2xl bg-white p-8">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-stone-700">Nom de l'entreprise</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="Entreprise SA"
                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-stone-700">Adresse e-mail</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="contact@entreprise.ch"
                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-stone-700">Message</label>
                        <textarea
                            v-model="form.message"
                            rows="4"
                            placeholder="Décrivez votre candidature..."
                            class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                        ></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <button
                        class="rounded-xl border border-stone-200 px-6 py-3 font-semibold text-stone-600 transition hover:bg-stone-50"
                        @click="goBack"
                    >
                        ← Retour
                    </button>
                    <button
                        :disabled="!form.name || !form.email || loading"
                        class="rounded-xl bg-rose-500 px-6 py-3 font-semibold text-white transition hover:bg-rose-600 disabled:opacity-40"
                        @click="handleSubmit"
                    >
                        <span v-if="loading">Envoi en cours…</span>
                        <span v-else>Envoyer la candidature</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>