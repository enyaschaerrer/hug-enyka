<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>Connexion — Administration CTS</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <script>
            window.__APP__ = {
                csrfToken: @json(csrf_token()),
            };
        </script>
    </head>
    <body class="font-cooper flex min-h-screen items-center justify-center bg-[#FAF8F2] px-4 text-[#2F2F36]">
        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <p class="cooper-text-baseline text-2xl font-semibold text-[#5A002A]">Administration CTS</p>
                <p class="cooper-text-baseline mt-2 text-sm text-[#2F2F36]/60">Connectez-vous pour accéder à votre espace</p>
            </div>

            <div id="login-form" class="rounded-2xl border border-[#EFE8DD] bg-white p-8 shadow-sm"></div>
        </div>
    </body>
</html>
