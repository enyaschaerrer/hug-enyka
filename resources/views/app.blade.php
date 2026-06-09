<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        @php
            $companyName = $coBrandedCollecte['company']['name'] ?? config('page_titles.defaults.site');
            $replaceCompany = fn (string $title) => str_replace(':company', $companyName, $title);

            $pageTitle = match (true) {
                request()->is('admin/comptes') => config('page_titles.admin.accounts'),
                request()->is('admin/registrations') => config('page_titles.admin.registrations'),
                request()->is('admin/campagnes') => config('page_titles.admin.campaigns'),
                request()->is('admin/companies/create') => config('page_titles.admin.company_create'),
                request()->is('admin/companies/*/edit') => config('page_titles.admin.company_edit'),
                request()->is('admin/trophee') => config('page_titles.admin.trophee'),
                request()->is('admin') => config('page_titles.admin.dashboard'),
                request()->is('collecte/*/*/questionnaire') => $replaceCompany(config('page_titles.cobranded.eligibility')),
                request()->is('collecte/*/*') => $replaceCompany(config('page_titles.cobranded.collection')),
                default => config('page_titles.defaults.site'),
            };
        @endphp

        <title>{{ $pageTitle }}</title>

        @if($coBrandedCollecte ?? false)
            <link rel="preload" as="image" href="/img/cobranded-background/bg-cobranded-clear.webp">
        @endif

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @php
            $authUser = auth()->user()?->only(['id', 'name', 'email', 'role']);
            $pageTitles = config('page_titles');
        @endphp
        <script>
            window.__APP__ = {
                auth: {
                    user: @json($authUser),
                },
                csrfToken: @json(csrf_token()),
                coBrandedCollecte: @json($coBrandedCollecte ?? null),
                pageTitles: @json($pageTitles),
            };
        </script>
    </head>
    <body style="background-color: #ffffff;">
        <div id="app"></div>
    </body>
</html>
