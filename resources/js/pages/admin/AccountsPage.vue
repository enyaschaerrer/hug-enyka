<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue';
import AdminLayout from '../../components/layout/AdminLayout.vue';

type AccountRole = 'superadmin' | 'admin';

type AccountRow = {
    id: number;
    email: string;
    role: AccountRole;
    created_at: string | null;
};

type AppState = {
    csrfToken: string;
    auth: { user: { id: number; role: string } | null };
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const currentUserId = appState?.auth.user?.id ?? null;

const accounts = ref<AccountRow[]>([]);
const loading = ref(true);
const submitting = ref(false);
const editingId = ref<number | null>(null);
const deletingId = ref<number | null>(null);
const loadError = ref<string | null>(null);
const flashMessage = ref<string | null>(null);
const errors = ref<Record<string, string[]>>({});
const showCreatePassword = ref(false);
const showEditPassword = ref(false);

const form = reactive({
    email: '',
    password: '',
    role: 'admin' as AccountRole,
});

const editForm = reactive({
    email: '',
    password: '',
    role: 'admin' as AccountRole,
});

function firstError(field: string): string | null {
    return errors.value[field]?.[0] ?? null;
}

function formatDate(iso: string | null): string {
    if (!iso) {
        return '-';
    }

    return new Date(iso).toLocaleDateString('fr-CH', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function roleLabel(role: AccountRole): string {
    return role === 'superadmin' ? 'Superadmin' : 'Admin';
}

function generatePassword(): string {
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    const bytes = new Uint8Array(12);
    window.crypto.getRandomValues(bytes);

    return Array.from(bytes, (byte) => alphabet[byte % alphabet.length]).join('');
}

function generateCreatePassword() {
    form.password = generatePassword();
    showCreatePassword.value = true;
}

function generateEditPassword() {
    editForm.password = generatePassword();
    showEditPassword.value = true;
}

async function fetchAccounts() {
    loading.value = true;
    loadError.value = null;

    try {
        const res = await fetch('/admin/api/accounts', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });

        if (res.ok) {
            accounts.value = await res.json();
            return;
        }

        loadError.value = res.status === 403
            ? 'Accès réservé aux superadmins.'
            : 'Erreur lors du chargement des comptes.';
    } catch {
        loadError.value = 'Erreur réseau.';
    } finally {
        loading.value = false;
    }
}

async function createAccount() {
    submitting.value = true;
    errors.value = {};
    flashMessage.value = null;

    try {
        const res = await fetch('/admin/accounts', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(form),
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            accounts.value = [data.account, ...accounts.value].sort((a, b) => a.email.localeCompare(b.email));
            form.email = '';
            form.password = '';
            form.role = 'admin';
            showCreatePassword.value = false;
            flashMessage.value = data.message ?? 'Compte créé.';
            return;
        }

        errors.value = res.status === 422 ? data.errors ?? {} : { email: ['Erreur serveur.'] };
    } catch {
        errors.value = { email: ['Erreur réseau.'] };
    } finally {
        submitting.value = false;
    }
}

function startEdit(account: AccountRow) {
    editingId.value = account.id;
    errors.value = {};
    flashMessage.value = null;
    editForm.email = account.email;
    editForm.password = '';
    editForm.role = account.role;
    showEditPassword.value = false;
}

function cancelEdit() {
    editingId.value = null;
    errors.value = {};
}

async function updateAccount(account: AccountRow) {
    submitting.value = true;
    errors.value = {};
    flashMessage.value = null;

    try {
        const res = await fetch(`/admin/accounts/${account.id}`, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(editForm),
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            accounts.value = accounts.value
                .map((item) => item.id === account.id ? data.account : item)
                .sort((a, b) => a.email.localeCompare(b.email));
            editingId.value = null;
            flashMessage.value = data.message ?? 'Compte mis à jour.';
            return;
        }

        errors.value = res.status === 422 ? data.errors ?? {} : { email: ['Erreur serveur.'] };
    } catch {
        errors.value = { email: ['Erreur réseau.'] };
    } finally {
        submitting.value = false;
    }
}

async function deleteAccount(account: AccountRow) {
    if (!window.confirm(`Supprimer le compte "${account.email}" ?`)) {
        return;
    }

    deletingId.value = account.id;
    errors.value = {};
    flashMessage.value = null;

    try {
        const res = await fetch(`/admin/accounts/${account.id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await res.json().catch(() => ({}));

        if (res.ok) {
            accounts.value = accounts.value.filter((item) => item.id !== account.id);
            flashMessage.value = data.message ?? 'Compte supprimé.';
            return;
        }

        errors.value = res.status === 422 ? data.errors ?? {} : { account: ['Erreur serveur.'] };
    } catch {
        errors.value = { account: ['Erreur réseau.'] };
    } finally {
        deletingId.value = null;
    }
}

onMounted(fetchAccounts);
</script>

<template>
    <AdminLayout>
        <div class="mb-6">
            <h1 class="text-3xl font-semibold">Gestion des comptes</h1>
        </div>

        <div v-if="flashMessage" class="alert alert-success mb-6">
            <span>{{ flashMessage }}</span>
        </div>
        <div v-if="firstError('account')" class="alert alert-error mb-6">
            <span>{{ firstError('account') }}</span>
        </div>

            <section class="mb-6 rounded-box border border-base-300 bg-base-100 p-5">
            <h2 class="mb-4 text-lg font-semibold">Créer un compte</h2>
            <form class="grid gap-4 md:grid-cols-[minmax(260px,1fr)_360px_160px_auto]" autocomplete="new-password" @submit.prevent="createAccount">
                <label class="flex flex-col gap-2">
                    <span class="label-text">Email</span>
                    <input
                        v-model="form.email"
                        type="text"
                        class="input input-bordered w-full"
                        autocomplete="new-password"
                        autocapitalize="none"
                        autocorrect="off"
                        spellcheck="false"
                        inputmode="email"
                        name="account_identifier_create"
                        required
                    />
                    <span v-if="firstError('email') && editingId === null" class="text-sm text-error">{{ firstError('email') }}</span>
                </label>

                <label class="flex flex-col gap-2">
                    <span class="label-text">Mot de passe</span>
                    <div class="relative">
                        <input
                            v-model="form.password"
                            type="text"
                            class="input input-bordered w-full pr-24"
                            :style="`-webkit-text-security: ${showCreatePassword ? 'none' : 'disc'}`"
                            autocomplete="off"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            data-1p-ignore="true"
                            data-lpignore="true"
                            name="generated_access_secret_create"
                            required
                        />
                        <button
                            type="button"
                            class="absolute right-11 top-1/2 -translate-y-1/2 text-xs font-semibold text-base-content/50 transition-colors hover:text-base-content"
                            title="Générer"
                            aria-label="Générer un mot de passe"
                            @click="generateCreatePassword"
                        >
                            <span>Générer</span>
                        </button>
                        <button
                            type="button"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/45 transition-colors hover:text-base-content"
                            :title="showCreatePassword ? 'Masquer' : 'Afficher'"
                            :aria-label="showCreatePassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                            @click="showCreatePassword = !showCreatePassword"
                        >
                            <svg v-if="showCreatePassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C7 20 2.73 16.89 1 12a21.8 21.8 0 0 1 5.06-6.94" />
                                <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58" />
                                <path d="M9.9 4.24A10.7 10.7 0 0 1 12 4c5 0 9.27 3.11 11 8a21.8 21.8 0 0 1-2.16 3.19" />
                                <path d="m1 1 22 22" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.73 7.11 7.98 4 12 4s8.27 3.11 9.94 7.65a1 1 0 0 1 0 .7C20.27 16.89 16.02 20 12 20s-8.27-3.11-9.94-7.65Z" />
                                <circle cx="12" cy="12" r="3" />
                            </svg>
                        </button>
                    </div>
                    <span v-if="firstError('password') && editingId === null" class="text-sm text-error">{{ firstError('password') }}</span>
                </label>

                <label class="flex flex-col gap-2">
                    <span class="label-text">Rôle</span>
                    <div class="relative">
                        <select v-model="form.role" class="select select-bordered w-full bg-none pr-10">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                        <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--color-pampas-950)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </div>
                    <span v-if="firstError('role') && editingId === null" class="text-sm text-error">{{ firstError('role') }}</span>
                </label>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="btn w-full border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] font-cooper text-white hover:border-[var(--color-razzmatazz-800)] hover:bg-[var(--color-razzmatazz-800)]"
                        :disabled="submitting"
                    >
                        <span>{{ submitting ? '...' : 'Créer' }}</span>
                    </button>
                </div>
            </form>
        </section>

        <div v-if="loading" class="text-sm text-base-content/50">Chargement...</div>
        <div v-else-if="loadError" class="alert alert-error"><span>{{ loadError }}</span></div>
        <p v-else-if="accounts.length === 0" class="text-sm text-base-content/50">Aucun compte admin.</p>

            <section v-else class="rounded-box border border-base-300 bg-base-100">
            <div class="grid grid-cols-[minmax(260px,1fr)_160px_130px_220px] border-b border-base-300 bg-[var(--color-razzmatazz-100)] px-5 py-3 text-xs font-semibold uppercase tracking-wide text-[var(--color-pampas-950)]">
                <span>Email</span>
                <span>Rôle</span>
                <span>Créé le</span>
                <span class="text-right">Actions</span>
            </div>

            <div
                v-for="account in accounts"
                :key="account.id"
                class="border-b border-base-200 px-5 py-4 hover:bg-[color:color-mix(in_srgb,var(--color-razzmatazz-50)_25%,transparent)] last:border-b-0"
            >
                <form
                    v-if="editingId === account.id"
                    class="grid grid-cols-[minmax(260px,1fr)_160px_310px_220px] items-start gap-3"
                    autocomplete="new-password"
                    @submit.prevent="updateAccount(account)"
                >
                    <div>
                        <input
                            v-model="editForm.email"
                            type="text"
                            class="input input-bordered w-full"
                            autocomplete="new-password"
                            autocapitalize="none"
                            autocorrect="off"
                            spellcheck="false"
                            inputmode="email"
                            :name="`account_identifier_${account.id}`"
                            required
                        />
                        <span v-if="firstError('email')" class="mt-1 block text-sm text-error">{{ firstError('email') }}</span>
                    </div>
                    <div>
                        <div class="relative">
                            <select v-model="editForm.role" class="select select-bordered w-full bg-none pr-10">
                                <option value="admin">Admin</option>
                                <option value="superadmin">Superadmin</option>
                            </select>
                            <svg xmlns="http://www.w3.org/2000/svg" class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--color-pampas-950)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </div>
                        <span v-if="firstError('role')" class="mt-1 block text-sm text-error">{{ firstError('role') }}</span>
                    </div>
                    <div>
                        <div class="relative">
                            <input
                                v-model="editForm.password"
                                type="text"
                                class="input input-bordered w-full pr-24"
                                :style="`-webkit-text-security: ${showEditPassword ? 'none' : 'disc'}`"
                                placeholder="Nouveau mot de passe"
                                autocomplete="off"
                                autocapitalize="none"
                                autocorrect="off"
                                spellcheck="false"
                                data-1p-ignore="true"
                                data-lpignore="true"
                                :name="`generated_access_secret_${account.id}`"
                            />
                            <button
                                type="button"
                                class="absolute right-11 top-1/2 -translate-y-1/2 text-xs font-semibold text-base-content/50 transition-colors hover:text-base-content"
                                title="Générer"
                                aria-label="Générer un mot de passe"
                                @click="generateEditPassword"
                            >
                                <span>Générer</span>
                            </button>
                            <button
                                type="button"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/45 transition-colors hover:text-base-content"
                                :title="showEditPassword ? 'Masquer' : 'Afficher'"
                                :aria-label="showEditPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                                @click="showEditPassword = !showEditPassword"
                            >
                                <svg v-if="showEditPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17.94 17.94A10.94 10.94 0 0 1 12 20C7 20 2.73 16.89 1 12a21.8 21.8 0 0 1 5.06-6.94" />
                                    <path d="M10.58 10.58A2 2 0 0 0 12 14a2 2 0 0 0 1.42-.58" />
                                    <path d="M9.9 4.24A10.7 10.7 0 0 1 12 4c5 0 9.27 3.11 11 8a21.8 21.8 0 0 1-2.16 3.19" />
                                    <path d="m1 1 22 22" />
                                </svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M2.06 12.35a1 1 0 0 1 0-.7C3.73 7.11 7.98 4 12 4s8.27 3.11 9.94 7.65a1 1 0 0 1 0 .7C20.27 16.89 16.02 20 12 20s-8.27-3.11-9.94-7.65Z" />
                                    <circle cx="12" cy="12" r="3" />
                                </svg>
                            </button>
                        </div>
                        <span v-if="firstError('password')" class="mt-1 block text-sm text-error">{{ firstError('password') }}</span>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-sm font-cooper" @click="cancelEdit">
                            <span>Annuler</span>
                        </button>
                        <button
                            type="submit"
                            class="btn btn-sm border-[var(--color-razzmatazz-700)] bg-[var(--color-razzmatazz-700)] font-cooper text-white hover:border-[var(--color-razzmatazz-800)] hover:bg-[var(--color-razzmatazz-800)]"
                            :disabled="submitting"
                        >
                            <span>Enregistrer</span>
                        </button>
                    </div>
                </form>

                <div v-else class="grid grid-cols-[minmax(260px,1fr)_160px_130px_220px] items-center gap-3">
                    <p class="font-semibold">{{ account.email }}</p>
                    <p class="text-sm">{{ roleLabel(account.role) }}</p>
                    <p class="text-sm text-base-content/50">{{ formatDate(account.created_at) }}</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-sm font-cooper" @click="startEdit(account)">
                            <span>Modifier</span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm border-red-600 font-cooper text-red-700 hover:border-red-700 hover:bg-red-700 hover:text-white disabled:pointer-events-auto disabled:cursor-not-allowed disabled:border-red-600 disabled:bg-transparent disabled:text-red-700 disabled:opacity-35"
                            :disabled="deletingId === account.id || currentUserId === account.id"
                            @click="deleteAccount(account)"
                        >
                            <span>{{ deletingId === account.id ? '...' : 'Supprimer' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
