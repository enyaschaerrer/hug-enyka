@extends('layouts.public')

@section('title', 'Collecte')
@section('banner', '/img/banners/banner_home.webp')

@push('scripts')
<script>
    window.__APP__ = {
        csrfToken: @json(csrf_token()),
    };
</script>
@endpush

@section('content')
    <section class="relative overflow-hidden bg-[#FAF8F2] px-6 pb-16 pt-8 sm:px-10 lg:px-12">

        <aside class="absolute right-0 top-8 z-30 hidden flex-col gap-4 lg:flex">
            <a
                href="#collecte-form"
                class="flex min-h-20 w-44 translate-x-3 items-center rounded-l-2xl border-4 border-r-0 border-[#76523A] bg-[#EAE0C9] px-5 py-4 text-sm font-bold leading-tight text-[#76523A] transition-transform duration-200 ease-out hover:translate-x-0"
            >
                S'inscrire à une collecte
            </a>
            <a
                href="/label#companies"
                class="flex min-h-20 w-44 translate-x-3 items-center rounded-l-2xl border-4 border-r-0 border-[#76523A] bg-[#EAE0C9] px-5 py-4 text-sm font-bold leading-tight text-[#76523A] transition-transform duration-200 ease-out hover:translate-x-0"
            >
                S'inscrire au Prix du Cœur
            </a>
        </aside>

        <div class="mx-auto max-w-6xl">
            <h1 class="text-left text-2xl font-semibold text-stone-900">
                Comment organiser une collecte ?
            </h1>

            <div class="mt-16 grid grid-cols-1 items-start gap-8 lg:grid-cols-2">

                {{-- Card 1 --}}
                <article class="rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-white shadow-sm">
                    <div class="mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                        Prise de contact
                    </div>
                    <p class="text-base leading-relaxed">
                        Échangez avec le CTS afin de définir votre projet de collecte, vos besoins et
                        le format le plus adapté à votre entreprise. Cette première étape permet de
                        poser les bases de la collaboration.
                    </p>
                </article>

                {{-- Colonne droite : mascotte + Card 2 --}}
                <div class="flex flex-col items-center gap-6">
                    <img
                        src="/img/mascots/sanguy_happy.png"
                        alt=""
                        class="pointer-events-none h-48 w-auto object-contain"
                    />
                    <article class="w-full rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                            Préparation
                        </div>
                        <p class="text-base leading-relaxed">
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
                <div class="flex flex-col items-center gap-6">
                    <article class="w-full rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                            Communication
                        </div>
                        <p class="text-base leading-relaxed">
                            Mobilisez vos équipes grâce à des supports prêts à l'emploi : emails,
                            affiches, visuels et contenus adaptables à votre entreprise. Tout est pensé
                            pour faciliter votre communication interne.
                        </p>
                    </article>

                    <img
                        src="/img/mascots/blutly_happy.png"
                        alt=""
                        class="pointer-events-none h-48 w-auto object-contain"
                    />

                    <article class="w-full rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-white shadow-sm">
                        <div class="mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                            Jour-J
                        </div>
                        <p class="text-base leading-relaxed">
                            Votre collecte prend vie avec l'accompagnement des équipes CTS. Les
                            installations, l'accueil des donneurs et le déroulement de la journée sont
                            coordonnés pour offrir une expérience fluide et rassurante.
                        </p>
                    </article>
                </div>

                {{-- Colonne droite : Inscriptions, centrée verticalement --}}
                <article class="rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-white shadow-sm lg:self-center">
                    <div class="mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                        Inscriptions
                    </div>
                    <p class="text-base leading-relaxed">
                        Les collaborateurs accèdent à un lien dédié à votre entreprise afin de
                        réserver leur créneau. Ce système facilite l'organisation et le suivi de
                        la collecte.
                    </p>
                </article>

            </div>

            {{-- CTA --}}
            <div class="mt-16 flex justify-center">
                <a
                    href="#collecte-form"
                    class="rounded-full bg-[#84202D] px-10 py-4 text-sm font-semibold text-white transition hover:bg-[#6e1a25]"
                >
                    Inscrivez-vous à une collecte
                </a>
            </div>

        </div>
    </section>

    <div id="collecte-form"></div>
@endsection
