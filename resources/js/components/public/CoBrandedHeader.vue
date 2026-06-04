<script setup lang="ts">
type CoBrandedCompany = {
    name: string;
    logo: string | null;
    colors: {
        primary: string | null;
        secondary: string | null;
        third: string | null;
    };
};

const props = defineProps<{
    company: CoBrandedCompany;
    csrfToken: string;
    logoutUrl: string;
}>();

async function logout() {
    try {
        const res = await fetch(props.logoutUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (res.ok) {
            window.location.reload();
            return;
        }
    } catch {
        // Fall through to reload the page and let the server state decide.
    }

    window.location.reload();
}
</script>

<template>
    <header class="font-cooper sticky top-0 z-40 bg-white/90 shadow-sm backdrop-blur">
        <div class="flex">
            <span class="h-[10px] flex-1" :style="{ backgroundColor: company.colors.primary ?? '#e5e7eb' }"></span>
            <span class="h-[10px] flex-1" :style="{ backgroundColor: company.colors.secondary ?? '#e5e7eb' }"></span>
            <span class="h-[10px] flex-1" :style="{ backgroundColor: company.colors.third ?? '#e5e7eb' }"></span>
        </div>
        <nav class="navbar mx-auto min-h-0 w-full max-w-6xl px-4 py-3">
            <div class="navbar-start">
                <a class="inline-flex items-center gap-3" href="/" aria-label="Accueil">
                    <img class="h-9 w-auto object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
                    <span class="text-xs font-semibold leading-tight uppercase" :style="{ color: company.colors.primary ?? '#a8a29e' }">
                        <span>X</span>
                    </span>
                    <img v-if="company.logo" class="h-11 w-auto object-contain" :src="company.logo" :alt="company.name" />
                    <span v-else class="text-sm font-bold leading-tight text-red-950">
                        <span>{{ company.name }}</span>
                    </span>
                </a>
            </div>

            <div class="navbar-end">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-slate-600 transition-colors duration-200 ease-in-out hover:bg-slate-100"
                    title="Sortir"
                    aria-label="Sortir"
                    :style="{ color: company.colors.primary ?? '#575656' }"
                    @click="logout"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                </button>
            </div>
        </nav>
    </header>
</template>
