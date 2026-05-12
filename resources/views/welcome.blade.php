<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'AppointPro') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="shell antialiased">
        <main class="mx-auto flex min-h-screen max-w-7xl items-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid w-full gap-8 lg:grid-cols-[1.05fr_0.95fr]">
                <section class="panel relative overflow-hidden p-8 lg:p-12">
                    <div class="absolute -left-10 top-10 h-32 w-32 rounded-full bg-amber-300/20 blur-3xl"></div>
                    <div class="absolute bottom-0 right-0 h-56 w-56 rounded-full bg-orange-500/10 blur-3xl"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3">
                            <x-application-logo class="h-12 w-12" />
                            <div>
                                <p class="text-xs uppercase tracking-[0.35em] text-amber-300">AppointPro</p>
                                <p class="text-sm text-stone-400">Appointment management system</p>
                            </div>
                        </div>
                        <p class="mt-14 text-sm uppercase tracking-[0.35em] text-amber-300/80">Built on Laravel Breeze</p>
                        <h1 class="mt-5 max-w-2xl text-5xl leading-tight text-stone-50">Premium scheduling for admin, receptionist, and staff operations.</h1>
                        <p class="mt-6 max-w-xl text-base leading-7 text-stone-300">
                            Book appointments, manage clients, assign staff members, and track service history from dashboards designed around each team role.
                        </p>
                        <div class="mt-10 grid gap-4 sm:grid-cols-3">
                            <div class="panel-soft p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Admin</p>
                                <p class="mt-2 text-lg font-semibold text-stone-50">Users, reports, full control</p>
                            </div>
                            <div class="panel-soft p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Receptionist</p>
                                <p class="mt-2 text-lg font-semibold text-stone-50">Clients and bookings</p>
                            </div>
                            <div class="panel-soft p-5">
                                <p class="text-xs uppercase tracking-[0.3em] text-stone-500">Staff</p>
                                <p class="mt-2 text-lg font-semibold text-stone-50">Assigned schedule and notes</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel flex flex-col justify-between p-8 lg:p-10">
                    <div>
                        <p class="text-sm uppercase tracking-[0.3em] text-stone-500">Quick Access</p>
                        <h2 class="mt-3 text-3xl text-stone-50">Sign in to continue</h2>
                        <p class="mt-3 text-sm leading-6 text-stone-400">The first admin account is seeded for setup. Admin can register receptionist and staff users from inside the app.</p>
                    </div>

                    <div class="mt-10 space-y-4 text-sm text-stone-300">
                        <div class="panel-soft p-5">
                            <p class="text-xs uppercase tracking-[0.25em] text-amber-300">Demo Admin</p>
                            <p class="mt-3">Email: <span class="font-semibold text-stone-50">admin@appointpro.test</span></p>
                            <p class="mt-1">Password: <span class="font-semibold text-stone-50">password</span></p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <a href="{{ route('login') }}" class="btn-primary">Open Login</a>
                            <a href="{{ route('login') }}" class="btn-secondary">Manage Appointments</a>
                        </div>
                    </div>
                </section>
            </div>
        </main>
    </body>
</html>
