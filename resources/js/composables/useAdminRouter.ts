import { ref } from 'vue';

const currentPath = ref<string>(window.location.pathname);
const flashMessage = ref<string | null>(null);

function navigate(path: string): void {
    if (path === currentPath.value) {
        return;
    }
    window.history.pushState({}, '', path);
    currentPath.value = path;
}

window.addEventListener('popstate', () => {
    currentPath.value = window.location.pathname;
});

export function useAdminRouter() {
    return { currentPath, navigate, flashMessage };
}
