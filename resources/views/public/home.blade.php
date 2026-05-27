@extends('layouts.public')

@section('title', 'Accueil — Coeur d\'Honneur')
@section('page-title', 'TITRE DE LA PAGE')
@section('page-description', 'description de la page')

@php
    // Données en dur pour l'instant — à remplacer par une requête BDD plus tard.
    $jury = [
        ['name' => 'Nom Prénom Jury', 'role' => 'Titre/Description'],
        ['name' => 'Nom Prénom Jury', 'role' => 'Titre/Description'],
        ['name' => 'Nom Prénom Jury', 'role' => 'Titre/Description'],
    ];
@endphp

@section('content')
    {{-- Section 1 : Le Prix Du Coeur --}}
    <section class="bg-[#FAF8F2] px-12 py-16">
        <div class="mx-auto max-w-6xl">
            <div class="flex items-start justify-between gap-12">
                <div class="max-w-2xl">
                    <h2 class="text-2xl font-bold text-stone-900">Le Prix Du Coeur</h2>
                    <p class="mt-4 text-stone-700">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt
                        ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation
                        ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                        reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint
                        occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                    </p>
                </div>
                <div class="flex h-40 w-40 shrink-0 items-center justify-center rounded-2xl bg-stone-200 text-sm text-stone-500">
                    Illustration
                </div>
            </div>

            {{-- Sous-section : Le jury --}}
            <div class="mt-12">
                <h3 class="text-xl font-bold text-stone-900">Le jury</h3>
                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($jury as $member)
                        <article class="flex gap-4">
                            <div class="h-24 w-24 shrink-0 rounded-md bg-stone-200"></div>
                            <div>
                                <div class="font-semibold text-stone-900">{{ $member['name'] }}</div>
                                <div class="text-sm text-stone-600">{{ $member['role'] }}</div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Bande copyright --}}
    <div class="bg-[#E6E8F3] px-12 py-3 text-center text-sm text-stone-600">
        &copy; Copyright - Enyka
    </div>

    {{-- Section 2 : Podium (îlot Vue) --}}
    <div id="podium"></div>

    {{-- Section 3 : Entreprises participantes (îlot Vue) --}}
    <div id="companies"></div>
@endsection
