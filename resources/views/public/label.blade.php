@extends('layouts.public')

@section('title', 'Label CDH')
@section('banner', '/img/banners/banner_home.webp')

@section('content')
    <section class="min-h-[50vh] bg-[#FAF8F2] px-12 py-16">
        <div class="mx-auto max-w-6xl"></div>
    </section>

    <div id="companies" data-companies='@json($companies)'></div>
@endsection
