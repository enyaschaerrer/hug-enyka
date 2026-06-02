<script setup lang="ts">
import { ref } from 'vue';

const step = ref<1 | 2>(1);
const loading = ref(false);
const submitted = ref(false);

const appState = (window as unknown as { __APP__?: { csrfToken: string } }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

const form = ref({
    labelled: null as boolean | null,
    name: '',
    email: '',
});

function selectLabelled(value: boolean) {
    form.value.labelled = value;
    if (value === true) {
        step.value = 2;
    }
    // Si Non : on reste sur l'étape 1 et on affiche le bloc d'explication via v-if sur form.labelled
}

function goBack() {
    step.value = 1;
}

async function handleSubmit() {
    loading.value = true;

    try {
        const res = await fetch('/prix/inscription', {
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
    <section class="mx-auto max-w-2xl px-6 py-8 lg:py-16">

        <!-- État de succès -->
        <div v-if="submitted" class="text-center">
            <h2 class="text-display text-martinique-950">Merci pour votre inscription !</h2>
            <img
                :src="'/img/mascots/blutly_sanguy_love.webp'"
                alt=""
                class="mx-auto my-8 h-48 w-auto object-contain"
            />
            <p class="text-body text-martinique-700">
                Nous avons bien reçu votre candidature et reviendrons vers vous rapidement.
            </p>
        </div>

        <template v-else>
            <h2 class="text-center text-display text-martinique-950">Participez au Prix du Coeur</h2>

            <!-- Étape 1 -->
            <template v-if="step === 1">
                <div class="mt-10 rounded-2xl bg-martinique-100 p-8">
                    <p class="text-center text-body text-martinique-800">
                        Êtes-vous une entreprise déjà labellisée "Cœur d'Honneur" ? *
                    </p>

                    <div class="mt-6 flex justify-center gap-4">
                        <button
                            type="button"
                            :class="form.labelled === true ? 'border-martinique-800 bg-martinique-400 text-white' : 'border-martinique-300 bg-white text-martinique-800 hover:bg-martinique-50'"
                            class="rounded-xl border-2 px-10 py-3 text-body transition"
                            @click="selectLabelled(true)"
                        >
                            Oui
                        </button>
                        <button
                            type="button"
                            :class="form.labelled === false ? 'border-martinique-800 bg-martinique-400 text-white' : 'border-martinique-300 bg-white text-martinique-800 hover:bg-martinique-50'"
                            class="rounded-xl border-2 px-10 py-3 text-body transition"
                            @click="selectLabelled(false)"
                        >
                            Non
                        </button>
                    </div>
                </div>

                <!-- Bloc d'explication si "Non" sélectionné -->
                <div
                    v-if="form.labelled === false"
                    class="mt-8 rounded-2xl border-2 border-martinique-300 p-8 text-center"
                >
                    <p class="text-body text-martinique-800">
                        Pour pouvoir participer au Prix du Coeur, il faut être labellisé "Cœur d'Honneur".
                    </p>
                    <p class="mt-2 text-heading-t3 font-semibold text-martinique-950">
                        Mettez en place une collecte et obtenez notre label !
                    </p>
                    <a
                        href="/collecte#formulaire"
                        class="mt-6 inline-block rounded-full bg-fuzzywuzzybrown-800 px-6 py-3 text-body text-white transition hover:bg-fuzzywuzzybrown-600"
                    >
                        Formulaire de contact
                    </a>
                </div>
            </template>

            <!-- Étape 2 -->
            <div v-if="step === 2">
                <div class="mt-10 space-y-6 rounded-2xl bg-martinique-100 p-8">
                    <div>
                        <label class="mb-1.5 block text-body text-martinique-800">Nom de l'entreprise *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            maxlength="255"
                            placeholder="Entreprise SA"
                            class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-body text-martinique-800">Adresse e-mail *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            maxlength="255"
                            placeholder="contact@entreprise.ch"
                            class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                        />
                    </div>
                </div>

                <div class="mt-4 flex justify-between">
                    <button
                        type="button"
                        class="rounded-full border border-martinique-300 px-6 py-3 text-body text-martinique-800 transition hover:bg-martinique-100"
                        @click="goBack"
                    >
                        ← Retour
                    </button>
                    <button
                        type="button"
                        :disabled="!form.name || !form.email || loading"
                        class="rounded-full bg-fuzzywuzzybrown-800 px-6 py-3 text-body text-white transition hover:bg-fuzzywuzzybrown-600 disabled:opacity-40"
                        @click="handleSubmit"
                    >
                        <span v-if="loading">Envoi en cours…</span>
                        <span v-else>Envoyer la candidature</span>
                    </button>
                </div>
            </div>
        </template>
    </section>
</template>
