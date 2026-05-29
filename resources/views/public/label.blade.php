@extends('layouts.public')

@section('title', 'Label CDH')
@section('banner', '/img/banners/banner_label.webp')

@section('content')
    @include('partials.public-side-cta')

    <section class="relative overflow-hidden px-12 py-16">

        <div class="mx-auto max-w-6xl">
            <h1 class="text-left text-display text-martinique-950">
                Le label Coeur d’Honneur
            </h1>

            <div class="relative mt-14 min-h-[320px]">
                <img
                    src="/img/mascots/blutly_happy.png"
                    alt=""
                    class="pointer-events-none absolute bottom-0 left-0 hidden h-56 w-auto object-contain lg:block"
                />

                <img
                    src="/img/mascots/sanguy_happy.png"
                    alt=""
                    class="pointer-events-none absolute bottom-0 right-0 hidden h-56 w-auto object-contain lg:block"
                />

                <div class="mx-auto grid max-w-4xl gap-8 lg:grid-cols-3">
                    <article class="rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-semibold text-fuzzywuzzybrown-900">
                            Engagement reconnu
                        </div>
                        <p class="text-body">
                            Le Coeur d’Honneur distingue les entreprises qui s’engagent activement en faveur du don de sang et de la solidarité.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-semibold text-fuzzywuzzybrown-900">
                            Reconnaissance officielle
                        </div>
                        <p class="text-body">
                            Plus qu’un label, le Coeur d’Honneur met en lumière les organisations qui transforment leurs valeurs en actions concrètes.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-fuzzywuzzybrown-900 px-8 pb-9 pt-5 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-fuzzywuzzybrown-50 px-5 py-1.5 text-body font-semibold text-fuzzywuzzybrown-900">
                            Image renforcée
                        </div>
                        <p class="text-body">
                            Le label Coeur d’Honneur valorise votre entreprise comme acteur engagé de son territoire et de sa communauté.
                        </p>
                    </article>
                </div>

                <div class="mt-10 flex items-end justify-between gap-6 lg:hidden">
                    <img src="/img/mascots/blutly_happy.png" alt="" class="h-36 w-auto object-contain" />
                    <img src="/img/mascots/sanguy_happy.png" alt="" class="h-36 w-auto object-contain" />
                </div>
            </div>
        </div>
    </section>

    <div
        id="companies"
        data-companies='@json($companies)'
        data-title="Les entreprises labellisées Coeur d'Honneur"
        data-description="Découvrez les organisations distinguées par le label, en reconnaissance de leur engagement."
        data-show-trophies="false"
    ></div>
@endsection
