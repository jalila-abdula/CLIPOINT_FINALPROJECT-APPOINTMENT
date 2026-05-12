<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AppointPro') }}</title>
        <style>[x-cloak]{display:none!important;}</style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        @php
            $isPortalDashboard = request()->routeIs('portal.*', 'clients.*', 'appointments.*', 'reports.*', 'service-records.*', 'users.*', 'profile.*');
        @endphp

        <div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" @class([
            'app-shell',
            'dashboard-shell' => $isPortalDashboard,
        ])>
            @include('layouts.navigation')

            <div x-cloak x-show="sidebarOpen" x-transition.opacity class="app-overlay lg:hidden" @click="sidebarOpen = false"></div>

            <div @class([
                'app-content',
                'dashboard-app-content' => $isPortalDashboard,
            ])>
                <main @class([
                    'app-page',
                    'dashboard-app-page' => $isPortalDashboard,
                ])>
                    <button type="button"
                            class="app-menu-button mb-4 lg:hidden"
                            @click="sidebarOpen = !sidebarOpen"
                            aria-label="Toggle navigation">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round"/>
                        </svg>
                    </button>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
