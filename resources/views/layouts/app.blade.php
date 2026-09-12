<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Platform Keperawatan') }}</title>
        <meta name="description" content="Platform Keperawatan KADIN — Talent OS ekosistem perawat Indonesia">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased" style="background-color:#f4efe6; color:#2c3732;">
        <div style="min-height:100vh; display:flex; flex-direction:column;">

            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <div style="background:linear-gradient(180deg, #eae2d3 0%, #f4efe6 100%); border-bottom:1px solid #e3d1b0; padding:1.75rem 0;">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 fade-up">
                        {{ $header }}
                    </div>
                </div>
            @endisset

            <!-- Page Content -->
            <main style="flex:1; padding-bottom:4rem;">
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer style="background:#143a30; border-top:1px solid rgba(255,255,255,0.1); padding:2.25rem 0; text-align:center; color:rgba(255,255,255,0.5);">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div style="font-size:0.75rem; letter-spacing:0.06em;">
                        Platform Keperawatan &copy; {{ date('Y') }} &middot; KADIN Partner Ecosystem &middot; Botanical Earth &amp; Warm Sand OS
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
