@extends('layouts.public')

@section('title', 'Accueil — Coeur d\'Honneur')
@section('banner', '/img/banners/banni_re_home.webp')
@section('banner_content')
    <h1 class="text-[1rem] font-semibold leading-none text-white lg:text-[2.5rem] lg:leading-[1.1]">
        Donnez, sauvez des vies.
    </h1>
    <p class="mt-2 max-w-xl text-[0.5rem] leading-[1.12] text-white hidden lg:block lg:mt-4 lg:max-w-none lg:text-[1.13rem] lg:font-semibold lg:leading-snug lg:whitespace-nowrap">
        Le Prix du Cœur récompense les entreprises engagées dans le don du sang.
    </p>
@endsection

@section('content')
    @include('partials.public-side-cta')

    {{-- Section 1 : Le Prix du Coeur --}}
    <section class="px-6 py-8 lg:px-12 lg:py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex flex-col items-center gap-8 lg:flex-row lg:items-start lg:justify-between lg:gap-12">
                <div class="max-w-2xl">
                    <h2 class="text-[1.5rem] font-semibold text-martinique-950 lg:text-display">Le Prix du Cœur</h2>
                    <p class="mt-10 text-body text-martinique-900 text-justify">
                        Chaque année, le Prix du Cœur met à l'honneur les entreprises de la région
                        qui s'engagent activement dans le don du sang aux côtés du CTS (Centre de Transfusion
                        Sanguine) des HUG. À travers ces collectes, les salariés et employeurs se rassemblent autour d'un même geste : sauver des vies.<br><br>
                        Trois récompenses sont décernées par un jury composé par les membres de l'HUG afin de saluer la mobilisation et la fidélité
                        des entreprises participantes.
                    </p>
                </div>
                <img
                    src="/img/mascots/blutly_sanguy_hey.webp"
                    alt="Mascottes Blutly et Sanguy"
                    class="h-70 w-70 shrink-0 object-contain"
                />
            </div>

            {{-- Sous-section : Le jury --}}
            <div class="mt-20">
                <h3 class="text-heading-t1 text-martinique-950">Le jury</h3>
                <div class="mt-10 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($jury as $member)
                        <article class="flex items-start gap-4">
                            <img
                                src="{{ $member['photo'] }}"
                                alt="{{ $member['name'] }}"
                                class="h-24 w-24 shrink-0 rounded-md object-cover object-top"
                            />
                            <div>
                                <div class="text-heading-t4 font-semibold text-martinique-950 lg:text-heading-t2">{{ $member['name'] }}</div>
                                <p class="mt-1 text-body">{{ $member['bio'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2 : Mettre en lumière les entreprises engagées --}}
    <section class="px-6 py-8 lg:px-12 lg:py-16">
        <div class="mx-auto max-w-6xl lg:grid lg:grid-cols-2 lg:gap-x-8 lg:gap-y-8">
            {{-- Bloc intro (top-left sur lg) --}}
            <div class="lg:col-start-1 lg:row-start-1">
                    <h2 class="text-[1.5rem] font-semibold text-martinique-950 lg:text-display">Mettre en lumière les entreprises engagées</h2>
                    <p class="mt-6 text-body text-martinique-950">
                        Le Prix du Cœur célèbre trois engagements distincts. Chaque récompense honore une
                        manière différente de contribuer au don du sang : la générosité des dons, l'effort de
                        mobilisation, et l'impact humain de la démarche.
                    </p>
                </div>

                {{-- Cartes : scroll horizontal sur mobile (avec chevrons), intégrées au grid 2×2 sur lg via lg:contents --}}
                <div class="relative -mx-6 mt-8 lg:m-0 lg:contents">
                    <button
                        type="button"
                        class="absolute left-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white text-martinique-950 shadow-md hover:bg-martinique-100 lg:hidden"
                        aria-label="Carte précédente"
                        onclick="document.getElementById('prize-cards-scroll').scrollBy({ left: -288, behavior: 'smooth' })"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_left</span>
                    </button>
                    <button
                        type="button"
                        class="absolute right-2 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white text-martinique-950 shadow-md hover:bg-martinique-100 lg:hidden"
                        aria-label="Carte suivante"
                        onclick="document.getElementById('prize-cards-scroll').scrollBy({ left: 288, behavior: 'smooth' })"
                    >
                        <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                    </button>
                    <div id="prize-cards-scroll" class="flex snap-x snap-mandatory gap-4 overflow-x-auto px-[calc(50vw-9rem)] pb-4 lg:contents">

                {{-- Carte : Meilleur ambassadeur (top-right sur lg) --}}
                <article class="flex w-72 shrink-0 snap-center flex-col gap-3 rounded-2xl bg-white p-4 lg:col-start-2 lg:row-start-1 lg:mt-0 lg:w-auto lg:shrink lg:snap-align-none lg:grid lg:grid-cols-[1fr_auto] lg:grid-rows-[auto_1fr] lg:gap-x-4 lg:gap-y-3">
                    <div class="w-fit rounded-full bg-razzmatazz-900 px-4 py-1 text-body font-medium text-white lg:col-start-1 lg:row-start-1">
                        Meilleur ambassadeur
                    </div>
                    <div class="relative shrink-0 lg:col-start-2 lg:row-span-2 lg:self-center">
                        <button
                            type="button"
                            class="block overflow-hidden rounded-xl transition hover:opacity-80"
                            onclick="openPrizeModal('/img/prizes/salt_meilleur_ambassadeur.png', 'Prix Meilleur ambassadeur — Salt')"
                            aria-label="Agrandir l'image"
                        >
                            <img
                                src="/img/prizes/salt_meilleur_ambassadeur.png"
                                alt="Prix Meilleur ambassadeur — Salt"
                                class="h-48 w-full object-cover lg:h-28 lg:w-40"
                            />
                        </button>
                        <span class="pointer-events-none absolute -right-2 -top-2 flex h-9 w-9 items-center justify-center rounded-full bg-razzmatazz-700 text-white shadow-md">
                            <span class="material-symbols-outlined" style="font-size: 20px; font-variation-settings: 'FILL' 1;" aria-hidden="true">star</span>
                        </span>
                    </div>
                    <p class="text-body text-martinique-950 lg:col-start-1 lg:row-start-2">
                        Distingue l'entreprise qui s'est le plus mobilisée pour faire connaître la cause
                        et encourager d'autres entreprises à mettre en place des collectes.
                    </p>
                </article>

                {{-- Carte : Coup de cœur du jury (bottom-left sur lg) --}}
                <article class="flex w-72 shrink-0 snap-center flex-col gap-3 rounded-2xl bg-white p-4 lg:col-start-1 lg:row-start-2 lg:mt-0 lg:w-auto lg:shrink lg:snap-align-none lg:grid lg:grid-cols-[1fr_auto] lg:grid-rows-[auto_1fr] lg:gap-x-4 lg:gap-y-3">
                    <div class="w-fit rounded-full bg-razzmatazz-900 px-4 py-1 text-body font-medium text-white lg:col-start-1 lg:row-start-1">
                        Coup de cœur du jury
                    </div>
                    <div class="relative shrink-0 lg:col-start-2 lg:row-span-2 lg:self-center">
                        <button
                            type="button"
                            class="block overflow-hidden rounded-xl transition hover:opacity-80"
                            onclick="openPrizeModal('/img/prizes/migros_prix_du_jury.png', 'Coup de cœur du jury — Migros')"
                            aria-label="Agrandir l'image"
                        >
                            <img
                                src="/img/prizes/migros_prix_du_jury.png"
                                alt="Coup de cœur du jury — Migros"
                                class="h-48 w-full object-cover lg:h-28 lg:w-40"
                            />
                        </button>
                        <span class="pointer-events-none absolute -right-2 -top-2 flex h-9 w-9 items-center justify-center rounded-full bg-razzmatazz-700 text-white shadow-md">
                            <span class="material-symbols-outlined" style="font-size: 20px; font-variation-settings: 'FILL' 1;" aria-hidden="true">favorite</span>
                        </span>
                    </div>
                    <p class="text-body text-martinique-950 lg:col-start-1 lg:row-start-2">
                        Salue une initiative remarquable, originale ou particulièrement touchante choisie
                        par le jury indépendamment des chiffres.
                    </p>
                </article>

                {{-- Carte : Meilleur donneur (bottom-right sur lg) --}}
                <article class="flex w-72 shrink-0 snap-center flex-col gap-3 rounded-2xl bg-white p-4 lg:col-start-2 lg:row-start-2 lg:mt-0 lg:w-auto lg:shrink lg:snap-align-none lg:grid lg:grid-cols-[1fr_auto] lg:grid-rows-[auto_1fr] lg:gap-x-4 lg:gap-y-3">
                    <div class="w-fit rounded-full bg-razzmatazz-900 px-4 py-1 text-body font-medium text-white lg:col-start-1 lg:row-start-1">
                        Meilleur donneur
                    </div>
                    <div class="relative shrink-0 lg:col-start-2 lg:row-span-2 lg:self-center">
                        <button
                            type="button"
                            class="block overflow-hidden rounded-xl transition hover:opacity-80"
                            onclick="openPrizeModal('/img/prizes/pictet_meilleur_donneur.png', 'Prix Meilleur donneur — Pictet')"
                            aria-label="Agrandir l'image"
                        >
                            <img
                                src="/img/prizes/pictet_meilleur_donneur.png"
                                alt="Prix Meilleur donneur — Pictet"
                                class="h-48 w-full object-cover lg:h-28 lg:w-40"
                            />
                        </button>
                        <span class="pointer-events-none absolute -right-2 -top-2 flex h-9 w-9 items-center justify-center rounded-full bg-razzmatazz-700 text-white shadow-md">
                            <span class="material-symbols-outlined" style="font-size: 20px; font-variation-settings: 'FILL' 1;" aria-hidden="true">bloodtype</span>
                        </span>
                    </div>
                    <p class="text-body text-martinique-950 lg:col-start-1 lg:row-start-2">
                        Récompense l'entreprise dont les collaborateurs ont effectué le plus de dons au
                        cours de l'année, donc pour un engagement collectif fort et constant.
                    </p>
                </article>

                    </div>
                </div>
        </div>
    </section>

    {{-- Section 3 : Podium (îlot Vue) --}}
    <div id="podium" data-podiums='@json($podiums)'></div>
    
    {{-- Modale d'agrandissement des photos de prix --}}
    <dialog
        id="prize-modal"
        class="m-auto max-w-4xl rounded-2xl bg-transparent p-0 backdrop:bg-black/70"
        onclick="event.target === this && this.close()"
    >
        <div class="relative">
            <button
                type="button"
                class="absolute right-3 top-3 flex h-10 w-10 items-center justify-center rounded-full bg-white text-martinique-950 shadow-lg hover:bg-martinique-100"
                aria-label="Fermer"
                onclick="document.getElementById('prize-modal').close()"
            >
                <span class="material-symbols-outlined" aria-hidden="true">close</span>
            </button>
            <img id="prize-modal-img" src="" alt="" class="block max-h-[85vh] w-full rounded-2xl object-contain" />
        </div>
    </dialog>

    <script>
        function openPrizeModal(src, alt) {
            const modal = document.getElementById('prize-modal');
            const img = document.getElementById('prize-modal-img');
            img.src = src;
            img.alt = alt;
            modal.showModal();
        }
    </script>

    {{-- Section 4 : Entreprises participantes (îlot Vue) --}}
    <div id="companies" data-companies='@json($companies)'></div>
@endsection
