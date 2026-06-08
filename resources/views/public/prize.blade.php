@extends('layouts.public')

@section('title', config('page_titles.public.prix'))
@section('banner', '/img/banners/banni_re_formulaire_participation.webp')
@section('banner_content')
    <h1 class="text-[1rem] font-semibold leading-none text-white lg:text-[2.5rem] lg:leading-[1.1]">
        Participez au Prix du Cœur
    </h1>
    <p class="mt-2 max-w-xl text-[0.5rem] leading-[1.12] font-semibold text-white hidden lg:block lg:mt-4 lg:max-w-none lg:text-[1.13rem] lg:leading-snug lg:whitespace-nowrap">
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
