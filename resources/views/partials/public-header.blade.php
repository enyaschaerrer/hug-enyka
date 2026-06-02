@php
    $navItems = [
        ['label' => 'Accueil', 'href' => '/'],
        ['label' => 'Label CDH', 'href' => '/label'],
        ['label' => 'Collecte', 'href' => '/collecte'],
        ['label' => 'Participation au Prix du Coeur', 'href' => '/prix'],
    ];
    $currentPath = '/' . trim(request()->path(), '/');
    $currentPath = $currentPath === '/' ? '/' : rtrim($currentPath, '/');
@endphp

{{-- Bandeau supérieur (image par page) — défile normalement --}}
@hasSection('banner')
    <img src="@yield('banner')" alt="" class="block w-full" />
@else
    <div class="h-24 bg-geraldine-500"></div>
@endif

{{-- Barre de navigation — sticky au top une fois atteinte --}}
<nav class="sticky top-0 z-40 bg-martinique-700 px-6 py-3 text-white lg:px-12">
    <div class="mx-auto flex max-w-6xl items-center justify-between">
        {{-- Liens nav (desktop uniquement) --}}
        <ul class="hidden items-center gap-2 lg:flex">
            @foreach ($navItems as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'block rounded-full px-5 py-2 text-body font-medium transition',
                            'bg-white text-martinique-700' => $currentPath === $item['href'],
                            'hover:bg-white/10' => $currentPath !== $item['href'],
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Bouton hamburger (mobile uniquement) --}}
        <button
            type="button"
            class="inline-flex items-center justify-center rounded-full p-1 text-white transition hover:bg-white/10 lg:hidden"
            aria-label="Ouvrir le menu"
            onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
        >
            <span class="material-symbols-outlined" style="font-size: 32px;" aria-hidden="true">menu</span>
        </button>

        {{-- Lien admin (toujours visible) --}}
        <a
            href="/admin"
            @class([
                'inline-flex items-center justify-center rounded-full transition',
                'bg-white text-martinique-700' => str_starts_with($currentPath, '/admin'),
                'text-white hover:text-white/80' => ! str_starts_with($currentPath, '/admin'),
            ])
            aria-label="Espace admin"
        >
            <span class="material-symbols-outlined" style="font-size: 34px;" aria-hidden="true">account_circle</span>
        </a>
    </div>

    {{-- Menu mobile (caché par défaut, toggle par le hamburger) --}}
    <div id="mobile-menu" class="mx-auto mt-3 hidden max-w-6xl lg:hidden">
        <ul class="flex flex-col gap-1 border-t border-white/20 pt-3">
            @foreach ($navItems as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'block rounded-lg px-4 py-2 text-body font-medium transition',
                            'bg-white text-martinique-700' => $currentPath === $item['href'],
                            'hover:bg-white/10' => $currentPath !== $item['href'],
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</nav>
