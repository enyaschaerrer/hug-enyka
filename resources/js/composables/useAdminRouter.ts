import { ref } from 'vue';

const currentPath = ref<string>(window.location.pathname);
const flashMessage = ref<string | null>(null);
const navigationBlocker = ref<((path: string) => boolean) | null>(null);

function navigate(path: string, options?: { bypassBlocker?: boolean }): void {
    const nextUrl = new URL(path, window.location.origin);
    const nextLocation = `${nextUrl.pathname}${nextUrl.search}${nextUrl.hash}`;

    if (
        nextUrl.pathname === currentPath.value
        && nextUrl.search === window.location.search
        && nextUrl.hash === window.location.hash
    ) {
        return;
    }

    if (!options?.bypassBlocker && navigationBlocker.value?.(nextLocation)) {
        return;
    }

    window.history.pushState({}, '', nextLocation);
    currentPath.value = nextUrl.pathname;
}

function forceNavigate(path: string): void {
    navigate(path, { bypassBlocker: true });
}

function setNavigationBlocker(blocker: ((path: string) => boolean) | null): void {
    navigationBlocker.value = blocker;
}

window.addEventListener('popstate', () => {
    currentPath.value = window.location.pathname;
});

export function useAdminRouter() {
    return { currentPath, navigate, forceNavigate, setNavigationBlocker, flashMessage };
}
