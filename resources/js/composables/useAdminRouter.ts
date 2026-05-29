import { ref } from 'vue';

const currentPath = ref<string>(window.location.pathname);
const flashMessage = ref<string | null>(null);

function navigate(path: string): void {
    const nextUrl = new URL(path, window.location.origin);

    if (nextUrl.pathname === currentPath.value && nextUrl.search === window.location.search) {
        return;
    }

    window.history.pushState({}, '', `${nextUrl.pathname}${nextUrl.search}`);
    currentPath.value = nextUrl.pathname;
}

window.addEventListener('popstate', () => {
    currentPath.value = window.location.pathname;
});

export function useAdminRouter() {
    return { currentPath, navigate, flashMessage };
}
