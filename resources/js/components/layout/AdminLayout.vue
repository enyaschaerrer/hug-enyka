<script setup lang="ts">
import { useAdminRouter } from '../../composables/useAdminRouter';

type AppState = {
    auth: { user: { name: string; email: string } | null };
    csrfToken: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const user = appState?.auth.user ?? null;
const { currentPath, navigate } = useAdminRouter();

function goTo(path: string, event: Event) {
    event.preventDefault();
    navigate(path);
}

async function logout() {
    try {
        const res = await fetch('/admin/logout', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = res.ok ? await res.json() : null;
        window.location.href = data?.redirect ?? '/admin/login';
    } catch {
        window.location.href = '/admin/login';
    }
}
</script>

<template>
    <div data-theme="light" class="font-cooper flex min-h-screen bg-base-200 text-base-content">
        <!-- Sidebar -->
        <aside class="flex w-56 shrink-0 flex-col border-r border-base-300 bg-base-100">
            <div class="border-b border-base-300 px-4 py-5">
                <p class="cooper-text-baseline text-sm font-semibold text-base-content">Admin CTS</p>
            </div>

            <nav class="flex-1 px-2 py-4">
                <ul class="menu menu-sm p-0 gap-1">
                    <li>
                        <a
                            href="/admin"
                            :class="{ active: currentPath === '/admin' }"
                            @click="goTo('/admin', $event)"
                        >
                            <span class="cooper-baseline">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="/admin/campagnes"
                            :class="{
                                active:
                                    currentPath === '/admin/campagnes' ||
                                    currentPath.startsWith('/admin/companies'),
                            }"
                            @click="goTo('/admin/campagnes', $event)"
                        >
                            <span class="cooper-baseline">Campagnes</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-base-300 px-4 py-4">
                <p class="cooper-text-baseline text-sm font-medium truncate">{{ user?.name }}</p>
                <p class="cooper-text-baseline text-xs text-base-content/50 truncate">{{ user?.email }}</p>
                <button
                    type="button"
                    class="btn btn-ghost btn-xs mt-3 w-full justify-start px-2"
                    @click="logout"
                >
                    <span class="cooper-baseline">Déconnexion</span>
                </button>
            </div>
        </aside>

        <!-- Content -->
        <main class="flex-1 p-8">
            <slot />
        </main>
    </div>
</template>
