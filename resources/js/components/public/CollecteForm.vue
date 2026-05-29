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
    <main class="min-h-screen bg-[#FAF8F2] text-stone-950">

        <section class="mx-auto max-w-2xl px-4 py-16">
            <div v-if="!submitted">

                <form @submit.prevent="handleSubmit">
                    <div class="space-y-6 rounded-2xl bg-white p-8">
                        <!-- Nom de l'entreprise -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-stone-700"
                            >
                                Nom de l'entreprise
                            </label>
                            <input
                                v-model="form.companyName"
                                type="text"
                                required
                                placeholder="Entreprise SA"
                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-stone-700"
                            >
                                Adresse e-mail professionnelle
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                placeholder="contact@entreprise.ch"
                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                            />
                        </div>

                        <!-- Téléphone -->
                        <div>
                            <label
                                class="mb-1.5 block text-sm font-medium text-stone-700"
                            >
                                Téléphone
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="+41 79 000 00 00"
                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                            />
                        </div>

                        <!-- NPA / Localité -->
                        <div class="grid grid-cols-[120px_1fr] gap-4">
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-stone-700"
                                    >NPA</label
                                >
                                <input
                                    v-model="form.npa"
                                    type="text"
                                    placeholder="1400"
                                    maxlength="4"
                                    class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                                />
                            </div>
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-stone-700"
                                    >Localité</label
                                >
                                <input
                                    v-model="form.locality"
                                    type="text"
                                    placeholder="Yverdon-les-Bains"
                                    class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                                />
                            </div>
                        </div>

                        <!-- Message -->
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-stone-700">
                                Message
                            </label>
                            <textarea
                                v-model="form.message"
                                rows="4"
                                placeholder="Décrivez votre projet de collecte, vos besoins..."
                                class="w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder-stone-300 outline-none transition focus:border-rose-300 focus:ring-2 focus:ring-rose-100"
                            ></textarea>
                        </div>

                        <!-- Prix du Cœur -->
                        <label
                            class="flex cursor-pointer items-start gap-4 rounded-2xl border border-stone-200 bg-white p-5 transition hover:border-rose-200 hover:bg-rose-50/50"
                        >
                            <div class="relative mt-0.5 flex-shrink-0">
                                <input
                                    v-model="form.participatePrixCoeur"
                                    type="checkbox"
                                    class="peer sr-only"
                                />
                                <div
                                    class="h-5 w-5 rounded-md border-2 transition peer-checked:border-rose-500 peer-checked:bg-rose-500"
                                    :class="
                                        form.participatePrixCoeur
                                            ? 'border-rose-500 bg-rose-500'
                                            : 'border-stone-300 bg-white'
                                    "
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
                                <p class="font-semibold text-stone-900">
                                    Participer au Prix du Cœur
                                </p>
                                <p class="mt-0.5 text-sm text-stone-500">
                                    Cochez cette case si vous souhaitez
                                    candidater au Prix du Cœur cette année.
                                </p>
                            </div>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="mt-4 flex justify-end">
                        <button
                            type="submit"
                            :disabled="loading"
                            class="rounded-xl bg-rose-500 px-6 py-3.5 font-semibold text-white transition hover:bg-rose-600 active:scale-[0.98] disabled:opacity-60"
                        >
                            <span v-if="loading">Envoi en cours…</span>
                            <span v-else>Envoyer la demande</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- État de succès -->
            <div v-else class="py-16 text-center">
                <div
                    class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl bg-rose-100"
                >
                    <svg
                        class="h-8 w-8 text-rose-500"
                        viewBox="0 0 24 24"
                        fill="none"
                    >
                        <path
                            d="M5 13l4 4L19 7"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <h2 class="text-2xl font-black text-stone-900">
                    Demande envoyée !
                </h2>
                <p class="mt-3 text-stone-500">
                    Nous avons bien reçu votre inscription et reviendrons vers
                    vous rapidement.
                </p>
            </div>
        </section>
    </main>
</template>
