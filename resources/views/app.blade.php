<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        <script>
            window.__APP__ = {
                auth: {
                    user: @json(auth()->user()?->only(['id', 'name', 'email'])),
                },
                csrfToken: @json(csrf_token()),
                errors: @json($errors->toArray()),
                old: {
                    email: @json(old('email')),
                },
            };
        </script>
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
