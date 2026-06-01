@extends('layouts.public')

@section('title', 'Connexion administrateur')
@section('banner', '/img/banners/banner_home.webp')

@push('scripts')
<script>
    window.__APP__ = {
        csrfToken: @json(csrf_token()),
    };
</script>
@endpush

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-16">
        <h1 class="text-center text-display text-martinique-950">
            Vous êtes un administrateur ? 
        </h1>

        <div id="login-form" class="mt-10"></div>
    </section>
@endsection
