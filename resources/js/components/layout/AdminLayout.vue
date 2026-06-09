<script setup lang="ts">
import { useAdminRouter } from '../../composables/useAdminRouter';

type AppState = {
    auth: { user: { name: string; email: string; role: string } | null };
    csrfToken: string;
};

const appState = (window as unknown as { __APP__?: AppState }).__APP__;
const csrfToken = appState?.csrfToken ?? '';
const user = appState?.auth.user ?? null;
const isSuperAdmin = user?.role === 'superadmin';
const { currentPath, navigate } = useAdminRouter();

function navLinkClasses(active: boolean): string {
    return [
        'flex min-h-16 items-center px-8 text-[1.35rem] font-medium transition-colors',
        active
            ? 'bg-[var(--color-razzmatazz-50)] text-[var(--color-razzmatazz-700)]'
            : 'text-[#2F2F36] hover:bg-[var(--color-pampas-50)] hover:text-[var(--color-razzmatazz-700)]',
    ].join(' ');
}

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
    <div data-theme="light" class="font-cooper flex h-screen overflow-hidden bg-[var(--color-pampas-50)] text-[#2F2F36]">
        <!-- Sidebar -->
        <aside class="flex h-screen w-72 shrink-0 flex-col border-r border-[#EFE8DD] bg-white">
            <div class="flex min-h-24 items-center px-8">
                <p class="text-lg font-semibold text-[var(--color-pampas-950)]">Administration CTS</p>
            </div>

            <nav class="-mt-px flex-1">
                <ul class="m-0 p-0">
                    <li class="m-0 p-0">
                        <a
                            href="/admin"
                            :class="navLinkClasses(currentPath === '/admin')"
                            @click="goTo('/admin', $event)"
                        >
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="m-0 p-0">
                        <a
                            href="/admin/registrations"
                            :class="navLinkClasses(currentPath === '/admin/registrations')"
                            @click="goTo('/admin/registrations', $event)"
                        >
                            <span>Inscriptions</span>
                        </a>
                    </li>
                    <li class="m-0 p-0">
                        <a
                            href="/admin/campagnes"
                            :class="navLinkClasses(currentPath === '/admin/campagnes' || currentPath.startsWith('/admin/companies'))"
                            @click="goTo('/admin/campagnes', $event)"
                        >
                            <span>Campagnes</span>
                        </a>
                    </li>
                    <li class="m-0 p-0">
                        <a
                            href="/admin/trophee"
                            :class="navLinkClasses(currentPath === '/admin/trophee')"
                            @click="goTo('/admin/trophee', $event)"
                        >
                            <span>Trophée</span>
                        </a>
                    </li>
                    <li v-if="isSuperAdmin" class="m-0 p-0">
                        <a
                            href="/admin/comptes"
                            :class="navLinkClasses(currentPath === '/admin/comptes')"
                            @click="goTo('/admin/comptes', $event)"
                        >
                            <span>Comptes</span>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-[#EFE8DD] px-8 pb-8 pt-5">
                <div>
                    <p class="truncate text-sm font-semibold text-[#2F2F36]">{{ user?.name }}</p>
                    <p class="mt-1 truncate text-xs text-[#2F2F36]/45">{{ user?.email }}</p>
                    <div class="mt-4 flex items-center gap-[6px]">
                        <a
                            href="/"
                            title="Retour au site"
                            class="inline-flex h-11 w-11 shrink-0 items-center justify-center border border-[var(--color-razzmatazz-200)] p-0 leading-none text-[var(--color-razzmatazz-700)] transition-colors hover:bg-[var(--color-razzmatazz-50)] hover:text-[var(--color-razzmatazz-800)]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" />
                                <polyline points="9 21 9 12 15 12 15 21" />
                            </svg>
                        </a>
                        <button
                            type="button"
                            class="inline-flex h-11 flex-1 items-center justify-center border border-[var(--color-razzmatazz-200)] px-3 text-sm font-medium text-[var(--color-razzmatazz-700)] transition-colors hover:bg-[var(--color-razzmatazz-50)] hover:text-[var(--color-razzmatazz-800)]"
                            @click="logout"
                        >
                            <span>Déconnexion</span>
                        </button>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Content -->
        <main class="min-w-0 flex-1 overflow-y-auto bg-[var(--color-pampas-50)] p-8">
            <slot />
        </main>
    </div>
</template>
