@extends('layouts.public')

@section('body-bg', 'bg-martinique-900')
@section('title', 'Prix du Cœur — Candidature')
@section('banner', '/img/banners/banni_re_formulaire_participation.webp')
@section('banner_content')
    <h1 class="text-[1rem] font-semibold leading-none text-white lg:text-[2.5rem] lg:leading-[1.1]">
        Participez au Prix du Cœur
    </h1>
    <p class="hidden lg:block lg:mt-4 lg:max-w-none lg:text-[1.13rem] lg:leading-snug lg:whitespace-nowrap">
        Inscrivez votre entreprise et rejoignez le concours.
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

    <div id="prize-form"></div>
@endsection
