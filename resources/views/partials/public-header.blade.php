@php
    $navItems = [
        ['label' => 'Accueil', 'href' => '/'],
        ['label' => 'Label CDH', 'href' => '/label'],
        ['label' => 'Collecte', 'href' => '/collecte'],
        ['label' => 'Participation au Prix Du Coeur', 'href' => '/trophee'],
    ];
    $currentPath = '/' . trim(request()->path(), '/');
    $currentPath = $currentPath === '/' ? '/' : rtrim($currentPath, '/');
@endphp

<header>
    {{-- Bandeau supérieur --}}
    <div class="flex items-start justify-between bg-pink-600 px-12 py-10 text-white">
        <div class="text-3xl font-bold leading-tight">
            Donner son sang,<br>c'est classe.
        </div>
        <div class="text-right">
            <div class="text-xl font-bold uppercase tracking-wide">@yield('page-title')</div>
            <div class="mt-1 text-sm opacity-90">@yield('page-description')</div>
        </div>
    </div>

    {{-- Barre de navigation --}}
    <nav class="flex items-center justify-between bg-[#5A579E] px-12 py-3 text-white">
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
</header>
