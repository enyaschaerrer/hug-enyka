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
            <h1 class="cooper-text-baseline text-2xl font-semibold">Comptes</h1>
        </div>

        <div v-if="flashMessage" class="alert alert-success mb-6">
            <span class="cooper-baseline">{{ flashMessage }}</span>
        </div>
        <div v-if="firstError('account')" class="alert alert-error mb-6">
            <span class="cooper-baseline">{{ firstError('account') }}</span>
        </div>

        <section class="mb-6 rounded-box border border-base-300 bg-base-100 p-5">
            <h2 class="cooper-text-baseline mb-4 text-lg font-semibold">Créer un compte</h2>
            <form class="grid gap-4 md:grid-cols-[1fr_220px_160px_auto]" @submit.prevent="createAccount">
                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline label-text">Email</span>
                    <input
                        v-model="form.email"
                        type="email"
                        class="cooper-input-baseline input input-bordered w-full"
                        required
                    />
                    <span v-if="firstError('email') && editingId === null" class="cooper-text-baseline text-sm text-error">{{ firstError('email') }}</span>
                </label>

                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline label-text">Mot de passe</span>
                    <input
                        v-model="form.password"
                        type="password"
                        class="cooper-input-baseline input input-bordered w-full"
                        required
                    />
                    <span v-if="firstError('password') && editingId === null" class="cooper-text-baseline text-sm text-error">{{ firstError('password') }}</span>
                </label>

                <label class="flex flex-col gap-2">
                    <span class="cooper-baseline label-text">Rôle</span>
                    <select v-model="form.role" class="select select-bordered w-full">
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                    <span v-if="firstError('role') && editingId === null" class="cooper-text-baseline text-sm text-error">{{ firstError('role') }}</span>
                </label>

                <div class="flex items-end">
                    <button type="submit" class="btn btn-primary w-full font-cooper" :disabled="submitting">
                        <span class="cooper-baseline">{{ submitting ? '...' : 'Créer' }}</span>
                    </button>
                </div>
            </form>
        </section>

        <div v-if="loading" class="cooper-text-baseline text-sm text-base-content/50">Chargement...</div>
        <div v-else-if="loadError" class="alert alert-error"><span class="cooper-baseline">{{ loadError }}</span></div>
        <p v-else-if="accounts.length === 0" class="cooper-text-baseline text-sm text-base-content/50">Aucun compte admin.</p>

        <section v-else class="rounded-box border border-base-300 bg-base-100">
            <div class="grid grid-cols-[1fr_160px_130px_220px] border-b border-base-300 px-5 py-3 text-sm font-semibold text-base-content/50">
                <span class="cooper-baseline">Email</span>
                <span class="cooper-baseline">Rôle</span>
                <span class="cooper-baseline">Créé le</span>
                <span class="cooper-baseline text-right">Actions</span>
            </div>

            <div
                v-for="account in accounts"
                :key="account.id"
                class="border-b border-base-200 px-5 py-4 last:border-b-0"
            >
                <form
                    v-if="editingId === account.id"
                    class="grid grid-cols-[1fr_160px_190px_220px] items-start gap-3"
                    @submit.prevent="updateAccount(account)"
                >
                    <div>
                        <input v-model="editForm.email" type="email" class="cooper-input-baseline input input-bordered w-full" required />
                        <span v-if="firstError('email')" class="cooper-text-baseline mt-1 block text-sm text-error">{{ firstError('email') }}</span>
                    </div>
                    <div>
                        <select v-model="editForm.role" class="select select-bordered w-full">
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                        <span v-if="firstError('role')" class="cooper-text-baseline mt-1 block text-sm text-error">{{ firstError('role') }}</span>
                    </div>
                    <div>
                        <input
                            v-model="editForm.password"
                            type="password"
                            class="cooper-input-baseline input input-bordered w-full"
                            placeholder="Nouveau mot de passe"
                        />
                        <span v-if="firstError('password')" class="cooper-text-baseline mt-1 block text-sm text-error">{{ firstError('password') }}</span>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-sm font-cooper" @click="cancelEdit">
                            <span class="cooper-baseline">Annuler</span>
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm font-cooper" :disabled="submitting">
                            <span class="cooper-baseline">Enregistrer</span>
                        </button>
                    </div>
                </form>

                <div v-else class="grid grid-cols-[1fr_160px_130px_220px] items-center gap-3">
                    <p class="cooper-text-baseline font-semibold">{{ account.email }}</p>
                    <p class="cooper-text-baseline text-sm">{{ roleLabel(account.role) }}</p>
                    <p class="cooper-text-baseline text-sm text-base-content/50">{{ formatDate(account.created_at) }}</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="btn btn-ghost btn-sm font-cooper" @click="startEdit(account)">
                            <span class="cooper-baseline">Modifier</span>
                        </button>
                        <button
                            type="button"
                            class="btn btn-outline btn-sm border-red-600 font-cooper text-red-700 hover:border-red-700 hover:bg-red-700 hover:text-white"
                            :disabled="deletingId === account.id || currentUserId === account.id"
                            @click="deleteAccount(account)"
                        >
                            <span class="cooper-baseline">{{ deletingId === account.id ? '...' : 'Supprimer' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
