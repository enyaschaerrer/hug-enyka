<!DOCTYPE html>
<html lang="fr" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Collecte</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <script>
            window.__APP__ = {
                csrfToken: @json(csrf_token()),
            };
        </script>
    </head>
    <body class="min-h-screen bg-[#FAF8F2] text-stone-950">
        @section('banner', '/img/banners/banner_home.webp')
        @include('partials.public-header')
        <div id="collecte-form"></div>
        <div id="cookie-consent-root"></div>
    </body>
</html>
