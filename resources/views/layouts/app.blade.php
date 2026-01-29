<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Project farrell') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Grid Pattern Background */
            .bg-grid-slate-900 {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(30 41 59 / 0.5)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100 overflow-x-hidden selection:bg-cyan-500 selection:text-white">
        
        <div class="min-h-screen relative">
            
            <div class="absolute inset-0 bg-grid-slate-900 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.5))] -z-20 pointer-events-none fixed"></div>
            
            <div class="absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-blue-600/20 rounded-full blur-[120px] pointer-events-none -z-10 fixed"></div>
            
            <div class="absolute bottom-0 right-0 translate-x-1/3 translate-y-1/3 w-[600px] h-[600px] bg-cyan-600/20 rounded-full blur-[120px] pointer-events-none -z-10 fixed"></div>
            <div class="relative z-50 bg-slate-900/50 backdrop-blur-md border-b border-slate-800 shadow-sm">
                @include('layouts.navigation')
            </div>

            @if (isset($header))
                <header class="relative z-40 bg-slate-900/30 backdrop-blur-sm border-b border-slate-800/50 shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="relative z-30">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>