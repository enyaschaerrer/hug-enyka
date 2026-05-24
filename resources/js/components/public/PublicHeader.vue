<script setup lang="ts">
const navItems = [
    { label: 'Home', href: '/' },
    { label: 'Collecte', href: '/collecte' },
    { label: 'Trophee', href: '/trophee' },
    { label: 'Label', href: '/label' },
    { label: 'Contact', href: '/contact' },
];

const currentPath = window.location.pathname;

function isActivePath(href: string) {
    return currentPath === href;
}
</script>

<template>
    <header class="sticky top-0 z-40 border-b border-red-100 bg-white/90 px-4 py-3 shadow-sm backdrop-blur">
        <nav class="navbar mx-auto min-h-0 w-full max-w-6xl p-0">
            <div class="navbar-start">
                <a class="inline-flex items-center gap-3" href="/" aria-label="Accueil">
                    <img class="h-9 w-auto object-contain" :src="'/img/logo_HUG.png'" alt="HUG" />
                    <span class="text-sm font-black uppercase text-stone-400">X</span>
                    <img class="h-11 w-auto object-contain" :src="'/img/logo_heig-vd.png'" alt="HEIG-VD" />
                </a>
            </div>

            <div class="navbar-center hidden lg:flex">
                <ul class="menu menu-horizontal gap-1 rounded-full border border-red-100 bg-rose-50 px-2 py-1">
                    <li v-for="item in navItems" :key="item.href">
                        <a
                            class="rounded-full text-sm font-semibold text-red-950 hover:bg-white"
                            :class="isActivePath(item.href) ? 'bg-white shadow-sm' : ''"
                            :href="item.href"
                        >
                            {{ item.label }}
                        </a>
                    </li>
                </ul>
            </div>

            <div class="navbar-end gap-2">
                <div class="dropdown dropdown-end lg:hidden">
                    <button class="btn btn-circle btn-ghost text-red-950" type="button" aria-label="Ouvrir le menu" tabindex="0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>
                    <ul class="menu dropdown-content z-50 mt-3 w-52 rounded-2xl border border-red-100 bg-white p-2 shadow-xl" tabindex="0">
                        <li v-for="item in navItems" :key="item.href">
                            <a :class="isActivePath(item.href) ? 'bg-rose-50 font-bold text-red-700' : ''" :href="item.href">
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </div>

                <a class="btn btn-sm rounded-full border-red-200 bg-white text-red-700 hover:border-red-300 hover:bg-red-50" href="/admin">
                    <svg xmlns="http://www.w3.org/2000/svg" class="lucide lucide-user-round-cog-icon lucide-user-round-cog h-4 w-4" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m14.305 19.53.923-.382"/>
                        <path d="m15.228 16.852-.923-.383"/>
                        <path d="m16.852 15.228-.383-.923"/>
                        <path d="m16.852 20.772-.383.924"/>
                        <path d="m19.148 15.228.383-.923"/>
                        <path d="m19.53 21.696-.382-.924"/>
                        <path d="M2 21a8 8 0 0 1 10.434-7.62"/>
                        <path d="m20.772 16.852.924-.383"/>
                        <path d="m20.772 19.148.924.383"/>
                        <circle cx="10" cy="8" r="5"/>
                        <circle cx="18" cy="18" r="3"/>
                    </svg>
                    Admin
                </a>
            </div>
        </nav>
    </header>
</template>
