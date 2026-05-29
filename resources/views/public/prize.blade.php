@extends('layouts.public')

@section('title', 'Prix du Cœur — Candidature')
@section('banner', '/img/banners/banner_home.webp')

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