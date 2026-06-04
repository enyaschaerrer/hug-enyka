@extends('layouts.public')

@section('title', 'Label CDH')
@section('banner', '/img/banners/banni_re_label.webp')
@section('banner_content')
    <h1 class="text-heading-t1 leading-none text-white lg:text-[2.5rem] lg:leading-[1.1]">
        Le label : Cœur d'Honneur
    </h1>
    <p class="mt-2 max-w-xl text-[0.82rem] leading-[1.12] font-semibold text-white lg:mt-4 lg:max-w-none lg:text-[1.13rem] lg:leading-snug lg:whitespace-nowrap">
        Une distinction pour les entreprises qui s'engagent durablement dans le don du sang.
    </p>
@endsection

@section('content')
    @include('partials.public-side-cta')

    <section class="relative overflow-hidden px-6 py-8 lg:px-12 lg:py-16">

        <div class="mx-auto max-w-6xl">
            <h1 class="text-left text-display text-martinique-950">
                Le label Cœur d’Honneur
            </h1>

            <div class="relative mt-14 min-h-[320px]">
                <div class="pointer-events-none absolute bottom-0 left-1/2 hidden -translate-x-1/2 items-end gap-4 lg:flex">
                    <img src="/img/mascots/blutly_hero.webp" alt="" class="h-56 w-auto object-contain" />
                    <img src="/img/mascots/sanguy_thumbs_up.webp" alt="" class="h-56 w-auto object-contain" />
                </div>

                <div class="mx-auto grid max-w-5xl gap-8 lg:grid-cols-2">
                    <article class="rounded-2xl bg-razzmatazz-900 px-6 pb-8 pt-6 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                            Engagement reconnu
                        </div>
                        <p class="text-body">
                            Distingue les entreprises qui s’engagent activement en faveur du don de sang et de la solidarité.
                        </p>
                    </article>

                    <article class="rounded-2xl bg-razzmatazz-900 px-6 pb-8 pt-6 text-center text-white shadow-sm">
                        <div class="mx-auto -mt-1 mb-5 w-fit rounded-full bg-razzmatazz-50 px-5 py-1.5 text-body font-medium text-razzmatazz-900">
                            Image renforcée
                        </div>
                        <p class="text-body">
                            Il valorise votre entreprise comme acteur engagé de son territoire et de sa communauté.
                        </p>
                    </article>
                </div>

                <div class="mt-10 flex items-end justify-center gap-6 lg:hidden">
                    <img src="/img/mascots/blutly_hero.webp" alt="" class="h-36 w-auto object-contain" />
                    <img src="/img/mascots/sanguy_thumbs_up.webp" alt="" class="h-36 w-auto object-contain" />
                </div>
            </div>
        </div>
    </section>

    <div
        id="companies"
        data-companies='@json($companies)'
        data-title="Les entreprises labellisées Cœur d'Honneur"
        data-description="Découvrez les organisations distinguées par le label, en reconnaissance de leur engagement."
        data-show-trophies="false"
    ></div>
@endsection
