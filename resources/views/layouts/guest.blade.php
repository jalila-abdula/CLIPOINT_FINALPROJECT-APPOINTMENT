<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AppointPro') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        @php
            $loginIllustrationPublic = public_path('images/login-illustration.png');
            $loginIllustrationRoot = base_path('login-illustration.png');
            $loginIllustrationPath = file_exists($loginIllustrationPublic) ? $loginIllustrationPublic : (file_exists($loginIllustrationRoot) ? $loginIllustrationRoot : null);
            $hasLoginIllustration = filled($loginIllustrationPath);
            $loginIllustrationMime = $hasLoginIllustration ? mime_content_type($loginIllustrationPath) : null;
            $loginIllustrationData = $hasLoginIllustration ? 'data:' . $loginIllustrationMime . ';base64,' . base64_encode(file_get_contents($loginIllustrationPath)) : null;
            $isLoginRoute = request()->routeIs('login');
        @endphp

        <div class="shell flex min-h-screen items-center justify-center px-4 py-8">
            <div @class([
                'panel auth-shell p-4 sm:p-6 lg:p-7',
                'auth-shell-login' => $isLoginRoute,
            ])>
                <section @class([
                    'auth-illustration',
                    'auth-illustration-login' => $isLoginRoute,
                    'min-h-[320px] lg:min-h-[590px]' => ! $isLoginRoute,
                ])>
                    @if ($hasLoginIllustration)
                        <div class="flex h-full items-center justify-center">
                            <img src="{{ $loginIllustrationData }}"
                                 alt="Appointment booking illustration"
                                 class="h-full max-h-[540px] w-full max-w-[720px] object-contain">
                        </div>
                    @else
                        <div class="absolute inset-6 rounded-[2.4rem] bg-[linear-gradient(180deg,#fcfbff_0%,#f2eefc_100%)]"></div>
                        <div class="absolute left-10 top-10 h-28 w-28 rounded-full bg-violet-100/70"></div>
                        <div class="absolute left-10 top-10 h-14 w-14 rounded-full border-[5px] border-violet-200/90"></div>
                        <div class="absolute left-[4.05rem] top-[4rem] h-5 w-1 rounded-full bg-violet-300"></div>
                        <div class="absolute left-[4rem] top-[4rem] h-1 w-5 rounded-full bg-violet-300"></div>

                        <div class="absolute right-14 top-20 h-14 w-14 rounded-full bg-blue-100/70"></div>
                        <div class="absolute right-10 top-16 rounded-2xl bg-white/90 px-4 py-3 shadow-[0_12px_24px_rgba(139,92,246,0.10)]">
                            <div class="grid grid-cols-3 gap-2">
                                @for ($i = 0; $i < 6; $i++)
                                    <span class="h-2.5 w-2.5 rounded-full bg-violet-200"></span>
                                @endfor
                            </div>
                        </div>

                        <div class="absolute inset-x-10 bottom-8 top-24">
                            <div class="relative h-full">
                                <div class="absolute left-0 top-12 h-[58%] w-[78%] rounded-[45%] bg-violet-50/90"></div>

                                <div class="absolute left-16 top-28 w-[54%] overflow-hidden rounded-[1.9rem] bg-white shadow-[0_14px_34px_rgba(139,92,246,0.08)]">
                                    <div class="h-9 bg-gradient-to-r from-violet-500 to-indigo-500"></div>
                                    <div class="grid grid-cols-4 gap-3 px-6 py-5">
                                        @for ($i = 0; $i < 12; $i++)
                                            <span class="h-5 rounded-lg {{ $i === 4 ? 'bg-violet-400' : 'bg-slate-100' }}"></span>
                                        @endfor
                                    </div>
                                </div>

                                <div class="absolute bottom-12 left-10 h-28 w-52 rounded-[2rem] bg-gradient-to-b from-slate-200 to-slate-300/90"></div>
                                <div class="absolute bottom-0 left-0 h-3 w-full bg-violet-300/70"></div>
                                <div class="absolute bottom-0 left-4 h-24 w-11 rounded-t-[2rem] bg-emerald-300/75"></div>
                                <div class="absolute bottom-12 left-0 h-16 w-16 rounded-full bg-emerald-200/85"></div>
                                <div class="absolute bottom-1 left-10 h-16 w-14 rounded-full bg-emerald-100/80"></div>

                                <div class="absolute bottom-0 right-10 h-48 w-36 rounded-t-[4rem] rounded-b-[2.2rem] bg-violet-300/95"></div>
                                <div class="absolute bottom-36 right-[4.4rem] h-20 w-20 rounded-full bg-[#ffcfb8]"></div>
                                <div class="absolute bottom-[12.5rem] right-[3.8rem] h-16 w-28 rounded-[45%] bg-slate-800"></div>
                                <div class="absolute bottom-[8rem] right-[7rem] h-16 w-10 rotate-[12deg] rounded-full bg-[#ffcfb8]"></div>
                                <div class="absolute bottom-[6.4rem] right-[5.2rem] h-10 w-9 rounded-full bg-[#ffcfb8]"></div>
                                <div class="absolute bottom-[5.6rem] right-[6.6rem] h-16 w-14 rounded-[1.7rem] bg-slate-700"></div>
                                <div class="absolute bottom-[4.2rem] right-[4.7rem] h-12 w-10 rounded-[1.2rem] bg-orange-200"></div>
                                <div class="absolute bottom-[4.8rem] right-[8.6rem] h-12 w-7 rotate-[16deg] rounded-full bg-violet-300"></div>
                                <div class="absolute bottom-[4rem] right-[8.2rem] h-8 w-8 rounded-xl bg-violet-400"></div>
                            </div>
                        </div>
                    @endif
                </section>

                <section @class([
                    'auth-form-panel',
                    'auth-form-panel-login' => $isLoginRoute,
                ])>
                    {{ $slot }}
                </section>
            </div>
        </div>
    </body>
</html>
