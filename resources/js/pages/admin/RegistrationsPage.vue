<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type Registration = {
    id: number;
    name: string;
    email: string;
    created_at: string;
};

const registrations = ref<Registration[]>([]);
const loading = ref(true);
const loadError = ref<string | null>(null);
const lastCount = ref(0);
const hasNew = ref(false);
let pollTimer: number | undefined;

const appState = (window as unknown as { __APP__?: { csrfToken: string } }).__APP__;
const csrfToken = appState?.csrfToken ?? '';

async function fetchRegistrations() {
    try {
        const res = await fetch('/admin/api/registrations', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
            const data = await res.json();
            if (lastCount.value > 0 && data.length > lastCount.value) hasNew.value = true;
            lastCount.value = data.length;
            registrations.value = data;
        } else if (res.status === 401) {
            window.location.href = '/admin/login';
        } else {
            loadError.value = 'Erreur lors du chargement.';
        }
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loading.value = false;
    }
}

async function approve(id: number) {
    await fetch(`/admin/companies/${id}/approve`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    registrations.value = registrations.value.filter(r => r.id !== id);
}

async function reject(id: number) {
    await fetch(`/admin/companies/${id}/reject`, {
        method: 'PATCH',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    registrations.value = registrations.value.filter(r => r.id !== id);
}

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleString('fr-CH', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
}

onMounted(() => {
    fetchRegistrations();
    pollTimer = window.setInterval(fetchRegistrations, 5000);
});

onUnmounted(() => {
    if (pollTimer) window.clearInterval(pollTimer);
});
</script>

<template>
    <AdminLayout>
        <section class="min-h-full rounded-sm bg-[#FAF8F2] p-1 pr-4 text-[#1f1f22]">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="cooper-text-baseline text-3xl font-semibold">Inscriptions</h1>
                    <p class="cooper-text-baseline mt-1 text-lg text-base-content/60">
                        Nouvelles demandes reçues via le formulaire
                    </p>
                </div>
                <div
                    v-if="hasNew"
                    class="flex cursor-pointer items-center gap-2 rounded-full bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700"
                    @click="hasNew = false"
                >
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
                    <span class="cooper-baseline">Nouvelles inscriptions</span>
                </div>
            </div>

            <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
            <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>
            <div v-else-if="registrations.length === 0" class="cooper-text-baseline text-sm text-base-content/50">
                Aucune inscription pour le moment.
            </div>

            <div v-else class="border border-base-300 bg-white">
                <div class="flex border-b border-base-300 bg-[#f8e7ee] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-[#5a002a]">
                    <div class="w-1/4"><span class="cooper-baseline">Entreprise</span></div>
                    <div class="w-1/4"><span class="cooper-baseline">Email</span></div>
                    <div class="w-1/6"><span class="cooper-baseline">Date</span></div>
                </div>

                <div
                    v-for="reg in registrations"
                    :key="reg.id"
                    class="flex items-center border-b border-base-200 px-5 py-3 hover:bg-rose-50/40"
                >
                    <div class="w-1/4 truncate font-medium"><span class="cooper-baseline">{{ reg.name }}</span></div>
                    <div class="w-1/4 truncate text-base-content/70"><span class="cooper-baseline">{{ reg.email }}</span></div>
                    <div class="w-1/6 truncate text-base-content/50"><span class="cooper-baseline">{{ formatDate(reg.created_at) }}</span></div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
