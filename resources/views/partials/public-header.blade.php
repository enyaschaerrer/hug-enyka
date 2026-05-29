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
    <div class="h-24 bg-pink-600"></div>
@endif

{{-- Barre de navigation — sticky au top une fois atteinte --}}
<nav class="sticky top-0 z-40 flex items-center justify-between bg-[#5A579E] px-12 py-3 text-white">
        <ul class="flex items-center gap-2">
            @foreach ($navItems as $item)
                <li>
                    <a
                        href="{{ $item['href'] }}"
                        @class([
                            'block rounded-full px-5 py-2 text-sm font-medium transition',
                            'bg-white text-[#5A579E]' => $currentPath === $item['href'],
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
            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-[#5A579E] transition hover:bg-white"
            aria-label="Espace admin"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="8" r="5" />
                <path d="M3 21a9 9 0 0 1 18 0" />
            </svg>
        </a>
    </nav>
