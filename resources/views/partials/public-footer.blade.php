<footer class="z-40 bg-martinique-300 px-6 py-3 text-caption text-martinique-900 lg:fixed lg:bottom-0 lg:left-0 lg:right-0 lg:backdrop-blur">
    <div class="mx-auto flex max-w-6xl flex-col gap-1 text-center sm:flex-row sm:items-center sm:justify-between sm:gap-4 sm:text-left">
        <p>
            Vous naviguez sur un projet réalisé dans le cadre d'un cours étudiant à la HEIG-VD. Aucune donnée n'est officielle.
        </p>
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:gap-4">
            <a href="mailto:laurent.berthelot@heig-vd.ch" class="underline hover:text-martinique-700">
                laurent.berthelot@heig-vd.ch
            </a>
            <a href="https://www.hug.ch/don-du-sang" target="_blank" rel="noopener" class="underline hover:text-martinique-700">
                Site officiel du CTS
            </a>
        </div>
    </div>
</footer>

{{-- Popup d'avertissement mobile (premier chargement uniquement, lg:hidden) --}}
<dialog
    id="mobile-disclaimer-modal"
    class="m-auto max-w-xs rounded-2xl border border-martinique-200 bg-white p-6 text-martinique-900 backdrop:bg-black/50 lg:hidden"
>
    <div class="flex flex-col gap-4">
        <h2 class="text-heading-t3 font-semibold text-martinique-950">Projet d'étudiants</h2>
        <p class="text-body">
            Vous naviguez sur un projet réalisé dans le cadre d'un cours étudiant à la HEIG-VD.
            Aucune donnée n'est officielle.
        </p>
        <div class="flex flex-col gap-1 text-caption">
            <a href="mailto:laurent.berthelot@heig-vd.ch" class="underline hover:text-martinique-700">
                laurent.berthelot@heig-vd.ch
            </a>
            <a href="https://www.hug.ch/don-du-sang" target="_blank" rel="noopener" class="underline hover:text-martinique-700">
                Site officiel du CTS
            </a>
        </div>
        <button
            type="button"
            class="mt-2 rounded-full bg-martinique-700 px-5 py-2 text-body font-semibold text-white transition hover:bg-martinique-800"
            onclick="document.getElementById('mobile-disclaimer-modal').close(); localStorage.setItem('mobile-disclaimer-seen', '1');"
        >
            J'ai compris
        </button>
    </div>
</dialog>

<script>
    (function () {
        if (window.matchMedia('(min-width: 1024px)').matches) return;
        if (localStorage.getItem('mobile-disclaimer-seen') === '1') return;
        const modal = document.getElementById('mobile-disclaimer-modal');
        if (modal) modal.showModal();
    })();
</script>
