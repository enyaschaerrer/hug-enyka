<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @if($coBrandedCollecte ?? false)
            <link rel="preload" as="image" href="/img/cobranded-background/bg-cobranded-clear.webp">
        @endif

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @php
            $authUser = auth()->user()?->only(['id', 'name', 'email', 'role']);
        @endphp
        <script>
            window.__APP__ = {
                auth: {
                    user: @json($authUser),
                },
                csrfToken: @json(csrf_token()),
                coBrandedCollecte: @json($coBrandedCollecte ?? null),
            };
        </script>
    </head>
    <body style="background-color: #ffffff;">
        <div id="app"></div>
    </body>
</html>
