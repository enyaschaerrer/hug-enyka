<script setup lang="ts">
import { ref } from "vue";

const form = ref({
    companyName: '',
    email: '',
    phone: '',
    npa: '',
    locality: '',
    referredBy: '',
    message: '',
    participatePrixCoeur: false,
});

const submitted = ref(false);
const loading = ref(false);
const appState = (window as unknown as { __APP__?: { csrfToken: string } }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

async function handleSubmit() {
    loading.value = true;

    try {
        const res = await fetch('/collecte/inscription', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                name:    form.value.companyName,
                email:   form.value.email,
                phone:   form.value.phone,
                address: `${form.value.npa} ${form.value.locality}`.trim(),
                message: form.value.message,
                trophy:  form.value.participatePrixCoeur,
            }),
        });

        if (res.ok) {
            submitted.value = true;
            return;
        }

        if (res.status === 422) {
            const data = await res.json();
            console.error('Erreurs de validation:', JSON.stringify(data.errors, null, 2));
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
        <div v-if="!submitted">

            <form @submit.prevent="handleSubmit">
                <div class="space-y-6 rounded-2xl bg-martinique-100 p-8">
                    <!-- Nom de l'entreprise -->
                    <div>
                        <label class="mb-1.5 block text-body text-martinique-800">
                            Nom de l'entreprise
                        </label>
                        <input
                            v-model="form.companyName"
                            type="text"
                            required
                            placeholder="Entreprise SA"
                            class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                        />
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="mb-1.5 block text-body text-martinique-800">
                            Adresse e-mail professionnelle
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="contact@entreprise.ch"
                            class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                        />
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label class="mb-1.5 block text-body text-martinique-800">
                            Téléphone
                        </label>
                        <input
                            v-model="form.phone"
                            type="tel"
                            placeholder="+41 79 000 00 00"
                            class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                        />
                    </div>

                    <!-- NPA / Localité -->
                    <div class="grid grid-cols-[120px_1fr] gap-4">
                        <div>
                            <label class="mb-1.5 block text-body text-martinique-800">NPA</label>
                            <input
                                v-model="form.npa"
                                type="text"
                                placeholder="1400"
                                maxlength="4"
                                class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                            />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-body text-martinique-800">Localité</label>
                            <input
                                v-model="form.locality"
                                type="text"
                                placeholder="Yverdon-les-Bains"
                                class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                            />
                        </div>
                    </div>

                    <!-- Message -->
                    <div>
                        <label class="mb-1.5 block text-body text-martinique-800">
                            Message
                        </label>
                        <textarea
                            v-model="form.message"
                            rows="4"
                            placeholder="Décrivez votre projet de collecte, vos besoins..."
                            class="w-full rounded-xl border-2 border-martinique-300 bg-white px-4 py-3 text-body text-martinique-950 placeholder-martinique-400 outline-none transition focus:border-martinique-500 focus:ring-2 focus:ring-martinique-200"
                        ></textarea>
                    </div>

                    <!-- Prix du Cœur -->
                    <label class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 border-martinique-300 bg-white p-5 transition hover:border-martinique-400 hover:bg-martinique-50">
                        <div class="relative mt-0.5 flex-shrink-0">
                            <input
                                v-model="form.participatePrixCoeur"
                                type="checkbox"
                                class="peer sr-only"
                            />
                            <div
                                class="h-5 w-5 rounded-md border-2 transition"
                                :class="form.participatePrixCoeur ? 'border-fuzzywuzzybrown-700 bg-fuzzywuzzybrown-700' : 'border-martinique-300 bg-white'"
                            >
                                <svg
                                    v-if="form.participatePrixCoeur"
                                    class="h-full w-full p-0.5 text-white"
                                    viewBox="0 0 12 12"
                                    fill="none"
                                >
                                    <path
                                        d="M2 6l3 3 5-5"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <p class="text-body font-semibold text-martinique-950">
                                Participer au Prix du Cœur
                            </p>
                            <p class="mt-0.5 text-caption text-martinique-700">
                                Cochez cette case si vous souhaitez candidater au Prix du Cœur cette année.
                            </p>
                        </div>
                    </label>

                    <!-- Submit (centré dans le formulaire) -->
                    <div class="flex justify-center pt-2">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="rounded-full bg-fuzzywuzzybrown-800 px-6 py-3.5 text-body text-white transition hover:bg-fuzzywuzzybrown-600 active:scale-[0.98] disabled:opacity-60"
                        >
                            <span v-if="loading">Envoi en cours…</span>
                            <span v-else>Envoyer la demande</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- État de succès -->
        <div v-else class="text-center">
            <h2 class="text-heading-t1 text-martinique-950">Merci pour votre demande !</h2>
            <img
                :src="'/img/mascots/blutly_sanguy_love.webp'"
                alt=""
                class="mx-auto my-8 h-48 w-auto object-contain"
            />
            <p class="text-body text-martinique-700">
                Nous avons bien reçu votre message et reviendrons vers vous rapidement.
            </p>
        </div>
    </section>
</template>
