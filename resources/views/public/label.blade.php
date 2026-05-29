@extends('layouts.public')

@section('title', 'Label CDH')
@section('banner', '/img/banners/banner_home.webp')

@section('content')
    <section class="relative overflow-hidden bg-[#FAF8F2] px-6 pb-16 pt-8 sm:px-10 lg:px-12">
        <aside class="absolute right-0 top-8 z-30 hidden flex-col gap-4 lg:flex">
            <a
                href="/collecte"
                class="flex min-h-20 w-44 translate-x-3 items-center rounded-l-2xl border-4 border-r-0 border-[#76523A] bg-[#EAE0C9] px-5 py-4 text-sm font-bold leading-tight text-[#76523A] transition-transform duration-200 ease-out hover:translate-x-0"
            >
                Mettre en place une collecte
            </a>
            <a
                href="#companies"
                class="flex min-h-20 w-44 translate-x-3 items-center rounded-l-2xl border-4 border-r-0 border-[#76523A] bg-[#EAE0C9] px-5 py-4 text-sm font-bold leading-tight text-[#76523A] transition-transform duration-200 ease-out hover:translate-x-0"
            >
                S’inscrire au Prix du Cœur
            </a>
        </aside>

        <div class="mx-auto max-w-6xl">
            <h1 class="text-left text-2xl font-semibold text-stone-900">
                Le label Coeur d’Honneur, qu’est-ce que c’est ?
            </h1>

            <div class="relative mt-20 min-h-[320px]">
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
                    <article class="rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                            Engagement reconnu
                        </div>
                        <p class="text-base leading-relaxed">
                            Coeur d’Honneur distingue les entreprises qui s’engagent activement en faveur du don de sang et de la solidarité.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                            Reconnaissance officielle
                        </div>
                        <p class="text-base leading-relaxed">
                            Plus qu’un label, Coeur d’Honneur met en lumière les organisations qui transforment leurs valeurs en actions concrètes.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-[#84202D] px-8 pb-9 pt-5 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-[#FFF6F2] px-5 py-1.5 text-base font-medium text-[#84202D]">
                            Image renforcée
                        </div>
                        <p class="text-base leading-relaxed">
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

    <div id="companies" data-companies='@json($companies)'></div>
@endsection
