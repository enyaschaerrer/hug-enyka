<script setup lang="ts">
type AppState = {
    csrfToken: string;
    errors: Record<string, string[]>;
    old: {
        email: string | null;
    };
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const emailErrors = appState?.errors.email ?? [];
</script>

<template>
    <main class="min-h-screen bg-base-200 px-4 py-8 text-base-content">
        <div class="mx-auto w-full max-w-sm rounded bg-base-100 p-6 shadow">
            <h1 class="mb-6 text-2xl font-semibold">Admin</h1>

            <form method="post" action="/admin/login" class="space-y-4">
                <input type="hidden" name="_token" :value="appState?.csrfToken" />

                <label class="form-control w-full">
                    <span class="label-text">Email</span>
                    <input
                        name="email"
                        type="email"
                        autocomplete="email"
                        class="input input-bordered w-full"
                        :value="appState?.old.email ?? ''"
                        required
                    />
                </label>

                <label class="form-control w-full">
                    <span class="label-text">Password</span>
                    <input
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        class="input input-bordered w-full"
                        required
                    />
                </label>

                <p v-if="emailErrors.length > 0" class="text-sm text-error">
                    {{ emailErrors[0] }}
                </p>

                <button type="submit" class="btn btn-primary w-full">Login</button>
            </form>
        </div>
    </main>
</template>
