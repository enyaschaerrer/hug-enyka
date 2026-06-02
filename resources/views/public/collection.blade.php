@extends('layouts.public')

@section('title', 'Collecte')
@section('banner', '/img/banners/banner_collection.webp')

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
            <h1 class="text-left text-display text-martinique-950">
                Comment organiser une collecte ?
            </h1>

            <div class="mt-16 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">

                {{-- Card 1 --}}
                <article class="rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-white shadow-sm">
                    <div class="mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-medium text-fuzzywuzzybrown-900">
                        Prise de contact
                    </div>
                    <p class="text-body">
                        Échangez avec le CTS afin de définir votre projet de collecte, vos besoins et
                        le format le plus adapté à votre entreprise. Cette première étape permet de
                        poser les bases de la collaboration.
                    </p>
                </article>

                {{-- Colonne droite : mascotte + Card 2 --}}
                <div class="flex flex-col items-center gap-8">
                    <img
                        src="/img/mascots/sanguy_satisfied.webp"
                        alt=""
                        class="pointer-events-none -my-4 h-48 w-auto object-contain"
                    />
                    <article class="w-full rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-medium text-fuzzywuzzybrown-900">
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
                    <article class="w-full rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-medium text-fuzzywuzzybrown-900">
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
                        class="pointer-events-none -my-4 h-48 w-auto object-contain"
                    />

                    <article class="w-full rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-medium text-fuzzywuzzybrown-900">
                            Jour J
                        </div>
                        <p class="text-body">
                            Votre collecte prend vie avec l'accompagnement des équipes CTS. Les
                            installations, l'accueil des donneurs et le déroulement de la journée sont
                            coordonnés pour offrir une expérience fluide et rassurante.
                        </p>
                    </article>
                </div>

                {{-- Colonne droite : Inscriptions, centrée verticalement --}}
                <article class="rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-white shadow-sm lg:self-center">
                    <div class="mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-medium text-fuzzywuzzybrown-900">
                        Inscriptions
                    </div>
                    <p class="text-body">
                        Les collaborateurs accèdent à un lien dédié à votre entreprise afin de
                        réserver leur créneau. Ce système facilite l'organisation et le suivi de
                        la collecte.
                    </p>
                </article>

            </div>

            <!-- {{-- CTA --}}
            <div class="mt-16 flex justify-center">
                <a
                    href="#formulaire"
                    class="rounded-full bg-martinique-700 px-10 py-4 text-body text-white transition hover:bg-martinique-800"
                >
                    Mettre en place une collecte
                </a>
            </div> -->

        </div>
    </section>

    <div class="mx-auto max-w-2xl px-6">
        <h3 class="mt-24 text-display text-martinique-950">
            Envie d'organiser une collecte ?
        </h3>
    </div>
    <div id="formulaire"></div>
@endsection
