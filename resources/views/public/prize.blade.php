@extends('layouts.public')

@section('title', 'Prix du Cœur — Candidature')
@section('banner', '/img/banners/banni_re_formulaire_participation_neutral.webp')
@section('banner_content')
    <h1 class="text-heading-t1 leading-none text-white lg:text-[2.5rem] lg:leading-[1.1]">
        Participez au Prix du Cœur
    </h1>
    <p class="mt-2 max-w-xl text-[0.82rem] leading-[1.12] font-semibold text-white lg:mt-4 lg:max-w-none lg:text-[1.13rem] lg:leading-snug lg:whitespace-nowrap">
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
