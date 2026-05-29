@php
    $navItems = [
        ['label' => 'Accueil', 'href' => '/'],
        ['label' => 'Label CDH', 'href' => '/label'],
        ['label' => 'Collecte', 'href' => '/collecte'],
        ['label' => 'Participation au Prix du Coeur', 'href' => '/prize'],
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
<nav class="sticky top-0 z-40 flex items-center justify-between bg-martinique-700 px-12 py-3 text-white">
        <ul class="flex items-center gap-2">
            @foreach ($navItems as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'block rounded-full px-5 py-2 text-body transition',
                            'bg-white text-martinique-700' => $currentPath === $item['href'],
                            'hover:bg-white/10' => $currentPath !== $item['href'],
                        ])
                    >
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>

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
    </nav>
