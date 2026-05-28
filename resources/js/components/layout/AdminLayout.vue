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

function navLinkClasses(active: boolean): string {
    return [
        'cooper-text-baseline flex min-h-16 items-center px-8 text-[1.35rem] font-medium transition-colors',
        active
            ? 'bg-[#F8E8EF] text-[#9B2F5C]'
            : 'text-[#2F2F36] hover:bg-[#FAF8F2] hover:text-[#9B2F5C]',
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
    <div data-theme="light" class="font-cooper flex h-screen overflow-hidden bg-[#FAF8F2] text-[#2F2F36]">
        <!-- Sidebar -->
        <aside class="flex h-screen w-72 shrink-0 flex-col border-r border-[#EFE8DD] bg-white">
            <div class="flex min-h-24 items-center justify-between px-8">
                <p class="cooper-text-baseline text-lg font-semibold text-[#5A002A]">Admin CTS</p>
                <a
                    href="/"
                    title="Retour au site"
                    class="inline-flex h-9 w-9 items-center justify-center text-[#5A002A]/65 transition-colors hover:bg-[#FAF8F2] hover:text-[#5A002A]"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z" />
                        <polyline points="9 21 9 12 15 12 15 21" />
                    </svg>
                </a>
            </div>

            <nav class="-mt-px flex-1">
                <ul class="m-0 p-0">
                    <li class="m-0 p-0">
                        <a
                            href="/admin"
                            :class="navLinkClasses(currentPath === '/admin')"
                            @click="goTo('/admin', $event)"
                        >
                            Dashboard
                        </a>
                    </li>
                    <li class="m-0 p-0">
                        <a
                            href="/admin/campagnes"
                            :class="navLinkClasses(currentPath === '/admin/campagnes' || currentPath.startsWith('/admin/companies'))"
                            @click="goTo('/admin/campagnes', $event)"
                        >
                            Campagnes
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="border-t border-[#EFE8DD] px-8 pb-8 pt-5">
                <div>
                    <p class="cooper-text-baseline truncate text-sm font-semibold text-[#2F2F36]">{{ user?.name }}</p>
                    <p class="cooper-text-baseline mt-1 truncate text-xs text-[#2F2F36]/45">{{ user?.email }}</p>
                    <button
                        type="button"
                        class="cooper-text-baseline mt-4 inline-flex w-full items-center justify-center border border-[#5A002A]/15 px-3 py-2 text-sm font-medium text-[#5A002A]/75 transition-colors hover:bg-[#F8E8EF] hover:text-[#5A002A]"
                        @click="logout"
                    >
                        Déconnexion
                    </button>
                </div>
            </div>
        </aside>

        <!-- Content -->
        <main class="min-w-0 flex-1 overflow-y-auto bg-[#FAF8F2] p-8">
            <slot />
        </main>
    </div>
</template>
