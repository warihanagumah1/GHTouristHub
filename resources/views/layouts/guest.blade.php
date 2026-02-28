<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo/favicon.png') }}">
        <link rel="shortcut icon" href="{{ asset('images/logo/favicon.png') }}">
        @livewireStyles

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireScriptConfig
    </head>
    <body class="flex min-h-screen flex-col bg-slate-100 font-sans text-primary antialiased">
        <div class="relative flex-1 overflow-hidden">
            <img
                src="https://images.unsplash.com/photo-1516426122078-c23e76319801?auto=format&fit=crop&w=1800&q=80"
                alt="Safari tour background"
                class="absolute inset-0 h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-br from-primary/85 via-primary/70 to-secondary/70"></div>

            <div class="relative flex min-h-full flex-col items-center justify-center px-4 py-8">
                <div>
                    <a href="/" wire:navigate>
                        <img src="{{ asset('images/logo/logo.png') }}" alt="GH Tourist Hub logo" class="h-14 w-auto rounded-lg bg-white p-1 shadow-lg" />
                    </a>
                </div>

                <div class="mt-6 w-full sm:max-w-md overflow-hidden rounded-xl bg-white/95 px-6 py-6 shadow-2xl backdrop-blur">
                    {{ $slot }}
                </div>
            </div>
        </div>

        <x-site-footer />
    </body>
</html>
