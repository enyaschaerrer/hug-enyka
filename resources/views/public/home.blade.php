@extends('layouts.public')

@section('title', 'Accueil — Coeur d\'Honneur')
@section('banner', '/img/banners/banner_home.webp')

@section('content')
    {{-- Section 1 : Le Prix du Coeur --}}
    <section class="px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex items-start justify-between gap-12">
                <div class="max-w-2xl">
                    <h2 class="text-display text-martinique-950">Le Prix du Coeur</h2>
                    <p class="mt-4 text-body text-martinique-900">
                        Chaque année, le Prix du Coeur met à l'honneur les entreprises de la région
                        qui s'engagent activement dans le don du sang aux côtés du Centre de Transfusion
                        Sanguine des HUG. À travers les collectes organisées sur leur lieu de travail,
                        salariés et employeurs se rassemblent autour d'un même geste : sauver des vies.
                        Trois récompenses sont décernées par un jury composé de soignants et de
                        personnalités du monde de la santé, pour saluer la mobilisation, la fidélité
                        et l'inventivité des entreprises participantes.
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-4">
                    <img
                        src="/img/mascots/blutly1.webp"
                        alt="Mascottes Blutly et Sanguy"
                        class="h-70 w-70 object-contain"
                    />
                    <div class="flex flex-col gap-3">
                        <a
                            href="/contact"
                            class="rounded-full bg-merino-300 px-5 py-3 text-center text-body font-semibold text-martinique-950 transition hover:bg-merino-400"
                        >
                            Mettre en place une collecte
                        </a>
                        <a
                            href="#"
                            class="rounded-full bg-merino-300 px-5 py-3 text-center text-body font-semibold text-martinique-950 transition hover:bg-merino-400"
                        >
                            S'inscrire au Prix du Coeur
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sous-section : Le jury --}}
            <div class="mt-12">
                <h3 class="text-heading-t1 text-martinique-950">Le jury</h3>
                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($jury as $member)
                        <article class="flex items-start gap-4">
                            <img
                                src="{{ $member['photo'] }}"
                                alt="{{ $member['name'] }}"
                                class="h-24 w-24 shrink-0 rounded-md object-cover object-top"
                            />
                            <div>
                                <div class="text-heading-t2 text-martinique-950">{{ $member['name'] }}</div>
                                <p class="mt-1 text-body">{{ $member['bio'] }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Section 2 : Podium (îlot Vue) --}}
    <div id="podium" data-podiums='@json($podiums)'></div>

    {{-- Section 3 : Entreprises participantes (îlot Vue) --}}
    <div id="companies" data-companies='@json($companies)'></div>
@endsection
