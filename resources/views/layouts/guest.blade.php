<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'IoT ') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Grid Pattern Background */
            .bg-grid-slate-900 {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(30 41 59 / 0.5)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e");
            }
            
            /* Animasi Fade In yang Smooth */
            .animate-fade-in-up {
                animation: fadeInUp 0.8s ease-out forwards;
                opacity: 0;
            }
            
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-slate-950 text-slate-100 overflow-x-hidden selection:bg-cyan-500 selection:text-white">
        
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 relative overflow-hidden">

            <div class="absolute inset-0 bg-grid-slate-900 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.5))] -z-20"></div>

            <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-600/30 rounded-full blur-[100px] pointer-events-none -z-10"></div>
            <div class="absolute bottom-0 right-0 translate-x-1/3 translate-y-1/3 w-[500px] h-[500px] bg-cyan-600/20 rounded-full blur-[120px] pointer-events-none -z-10"></div>

            <div class="mb-8 text-center animate-fade-in-up" style="animation-delay: 0.1s;">
                <a href="/" class="group flex flex-col items-center justify-center">
                    <div class="relative flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-br from-slate-800 to-slate-900 border border-slate-700 shadow-2xl shadow-blue-500/20 group-hover:scale-105 transition-transform duration-300">
                        <svg class="w-8 h-8 text-cyan-400 group-hover:text-cyan-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-cyan-500"></span>
                        </span>
                    </div>
                    
                    <h1 class="mt-4 text-3xl font-bold tracking-tight text-white drop-shadow-md">
                        Sistem <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-cyan-400">Monitoring</span> IoT
                    </h1>
                </a>
            </div>

            <div class="w-full sm:max-w-md px-8 py-10 bg-slate-900/70 backdrop-blur-xl border border-slate-700/50 shadow-2xl sm:rounded-3xl relative z-10 animate-fade-in-up ring-1 ring-white/10" style="animation-delay: 0.2s;">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center text-xs text-slate-500 animate-fade-in-up" style="animation-delay: 0.3s;">
                &copy; {{ date('Y') }} IoT Project by Farrell. 
            </div>
        </div>
    </body>
</html>