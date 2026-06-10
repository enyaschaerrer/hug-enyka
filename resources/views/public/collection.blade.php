@extends('layouts.public')

@section('title', config('page_titles.public.collecte'))
@section('banner', '/img/banners/banni_re_collecte.webp')
@section('banner_content')
    <h1 class="text-[1rem] font-semibold leading-none text-white lg:text-[2.5rem] lg:leading-[1.1]">
        Organisez votre collecte
    </h1>
    <p class="hidden lg:block lg:mt-4 lg:max-w-none lg:text-[1.13rem] lg:leading-snug lg:whitespace-nowrap">
        Le CTS vous accompagne pour mettre en place une collecte de sang dans votre entreprise.
    </p>
@endsection

@push('scripts')
<script>
    window.__APP__ = {
        csrfToken: @json(csrf_token()),
    };
</script>
@endpush

@section('content')
    @include('partials.public-side-cta')

    <section class="relative overflow-hidden px-6 py-8 lg:px-12 lg:py-16">
        <div class="mx-auto max-w-6xl">
            <h1 class="text-[1.5rem] font-semibold text-left text-martinique-950 lg:text-display">
                Comment organiser une collecte ?
            </h1>

            <div class="mt-8 grid grid-cols-1 items-start gap-8 lg:grid-cols-2 lg:mt-16">
                <img
                    src="/img/mascots/sanguy_hips.webp"
                    alt=""
                    class="pointer-events-none -my-4 h-48  mx-auto w-auto object-contain lg:hidden"
                />

                {{-- Card 1 --}}
                <article class="rounded-2xl bg-razzmatazz-900 px-8 pb-9 pt-5 text-white shadow-sm">
                    <div class="mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                        Prise de contact
                    </div>
                    <p class="text-body">
                        Échangez avec le CTS afin de définir votre projet de collecte, vos besoins et
                        le lieu le plus adapté, que ce soit dans votre entreprise ou au CTS. Cela permet de
                        poser les bases de la collaboration.
                    </p>
                </article>

                {{-- Colonne droite : mascotte + Card 2 --}}
                <div class="flex flex-col items-center gap-8">
                    <img
                        src="/img/mascots/sanguy_hips.webp"
                        alt=""
                        class="pointer-events-none -my-4 h-48 w-auto object-contain hidden lg:block"
                    />
                    <article class="w-full rounded-2xl bg-razzmatazz-900 px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                            Préparation
                        </div>
                        <p class="text-body">
                            Le planning, les aspects logistiques et les modalités de la collecte sont
                            définis conjointement. L'objectif : assurer une organisation claire,
                            réaliste et adaptée à votre environnement.
                        </p>
                    </article>
                </div>

            </div>

            {{-- Ligne 2 : Col gauche = Card 3 + Blutly + Card 5 / Col droite = Card 4 centrée --}}
            <div class="mt-8 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">

                {{-- Colonne gauche : Communication + Blutly + Jour-J --}}
                <div class="flex flex-col items-center gap-8">
                    <article class="w-full rounded-2xl bg-razzmatazz-900 px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                            Communication
                        </div>
                        <p class="text-body">
                            Mobilisez vos équipes grâce à des supports prêts à l'emploi : emails,
                            affiches, visuels et contenus adaptables à votre entreprise. Tout est pensé
                            pour faciliter votre communication interne.
                        </p>
                    </article>

                    <img
                        src="/img/mascots/blutly_hero.webp"
                        alt=""
                        class="pointer-events-none -my-4 h-48 w-auto object-contain hidden lg:block"
                    />

                    <article class="w-full rounded-2xl bg-razzmatazz-900 px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                            Jour J
                        </div>
                        <p class="text-body">
                            Votre collecte prend vie avec l'accompagnement des équipes CTS. Les
                            installations, l'accueil des donneur.euses et le déroulement de la journée sont
                            coordonnés pour offrir une expérience fluide et rassurante.
                        </p>
                    </article>
                </div>

                {{-- Colonne droite : Inscriptions, centrée verticalement --}}
                <article class="rounded-2xl bg-razzmatazz-900 px-8 pb-9 pt-5 text-white shadow-sm lg:self-center">
                    <div class="mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                        Inscriptions
                    </div>
                    <p class="text-body">
                        Les collaborateur.rices accèdent à un lien dédié à votre entreprise afin de
                        réserver leur créneau. Ce système facilite l'organisation et le suivi de
                        la collecte.
                    </p>
                </article>

                <img
                    src="/img/mascots/blutly_hero.webp"
                    alt=""
                    class="pointer-events-none -my-4 h-48 mx-auto w-auto object-contain lg:hidden"
                />

            </div>
        </div>
    </section>

    <div class="mx-auto max-w-2xl px-6 mt-10">
        <h3 class="text-[1.5rem] font-semibold text-left text-martinique-950 lg:text-display">
            Envie d'organiser une collecte ?
        </h3>
    </div>
    <div id="formulaire"></div>
@endsection
