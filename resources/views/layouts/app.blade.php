<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
</head>
<body class="bg-gray-100 h-screen antialiased leading-none">
    <div id="app">
        <header>
            <nav class="bg-k4dark border-gray-200 px-2 sm:px-4 py-2.5">
                <div class="container flex flex-wrap items-center justify-between mx-auto">
                    <a href="{{ url('/') }}" class="flex items-center gap-2">
                        <svg class="w-16" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlnsXlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 750 750" xmlSpace="preserve">
                            <g>
                                <path class="fill-k4pink" d="M29.1,177.8h68v141.7l106.6-141.7h85.2L139.5,374.8l151.3,198h-85.9L97,430.1v88.2H29.1V177.8z"></path>
                                <path class="fill-k4pink" d="M455.3,428.6V178.1h-82L224.2,375.3l90.9,120.6l72.9-0.2v76.9h67.3v-76.7h20.4V463h15.7v-34.4H455.3zM388,428.6H266.8l121.2-160V428.6z"></path>
                                <rect x="490.3" y="478.4" class="fill-k4green" width="33.5" height="33.4"></rect>
                                <path class="fill-white" d="M691.6,328.4h35v193.2h-35V328.4z"></path>
                                <path class="fill-white" d="M648.9,399.3c-12.7-13.7-28.2-20.6-46.4-20.6c-8.4,0-16.2,1.6-23.6,4.7c-7.3,3.2-14.2,7.9-20.5,14.2v-15.4
                                h-34.7V463h15.1v63.2h-15.1v46.4h34.7v-65.7c6.8,6.5,13.7,11.2,20.6,14s14.4,4.3,22.4,4.3c18,0,33.6-7,46.8-20.9
                                c13.1-14,19.7-31.3,19.7-52.1C668,430.7,661.6,413,648.9,399.3z M622.2,481.5c-7.3,7.8-16.3,11.7-27,11.7c-11,0-20.2-3.8-27.4-11.5
                                c-7.3-7.6-10.9-17.7-10.9-30c0-12.1,3.6-21.9,10.9-29.5c7.3-7.5,16.4-11.3,27.4-11.3c10.9,0,20,3.8,27.2,11.5
                                c7.2,7.6,10.8,17.4,10.8,29.4C633.1,463.8,629.5,473.8,622.2,481.5z"></path>
                            </g>
                        </svg>
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-white opacity-60">
                            <path d="M7 18v-2a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v2" stroke="white" opacity="0.6"/>
                            <path d="M7 18H5v3M17 18h2v3" stroke="white" opacity="0.6"/>
                            <rect x="6" y="4" width="12" height="8" rx="2" stroke="white" opacity="0.6"/>
                        </svg>
                    </a>
                    <button id="open-mobile-menu" type="button" class="inline-flex items-center p-2 ml-3 text-sm text-white md:hidden focus:outline-none focus:ring-0" aria-controls="navbar-default" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="w-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path></svg>
                    </button>
                    <div class="hidden fixed md:static w-3/4 h-full top-0 right-0 bg-k4dark z-50 md:block md:w-auto" id="navbar-default">
                        <button id="close-mobile-menu" type="button" class="absolute right-6 py-2 px-3 top-6 text-white md:hidden focus:outline-none focus:ring-0">
                            <span class="sr-only">Close main menu</span>
                            <svg class="w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--! Font Awesome Pro 6.2.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"/></svg>
                        </button>
                        <ul class="flex flex-col p-4 mt-12 md:flex-row md:space-x-8 md:mt-0 md:text-sm md:font-medium">
                            @guest
                                <li><a class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" href="{{ url('/') }}">{{ __('Kalendarz') }}</a></li>
                                <li><a class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" href="{{ route('login') }}">{{ __('Logowanie') }}</a></li>
                                @if (Route::has('register'))
                                    <li><a class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" href="{{ route('register') }}">{{ __('Rejestracja') }}</a></li>
                                @endif
                            @else
                                {{-- <span>{{ Auth::user()->name }}</span> --}}
                                <li><a class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" href="{{ url('/') }}">{{ __('Kalendarz') }}</a></li>
                                <li><a class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" href="{{ route('vacations') }}">{{ __('Urlopy') }}</a></li>
                                @if (Auth::user()->is_admin)
                                    <li><a class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" href="{{ route('settings') }}">{{ __('Ustawienia') }}</a></li>
                                @endif
                                <li>
                                    <a href="{{ route('logout') }}" class="block py-4 md:py-2 pl-3 pr-4 text-white md:p-0 text-xs tracking-widest font-bold uppercase" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">{{ __('Wyloguj') }}</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                                        {{ csrf_field() }}
                                    </form>
                                </li>
                            @endguest
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        @yield('content')
    </div>
</body>
</html>
