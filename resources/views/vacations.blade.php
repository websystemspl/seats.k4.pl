@extends('layouts.app')

@section('content')
<main class="sm:container sm:mx-auto sm:mt-10">
    <div class="w-full sm:px-6">

        @if (session('status'))
            <div class="text-sm border border-t-8 rounded text-green-700 border-green-600 bg-green-100 px-3 py-4 mb-4" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <section class="flex flex-col break-words bg-white sm:border-1 sm:rounded-md sm:shadow-lg">

            <header class="font-semibold bg-gray-200 text-gray-700 py-5 px-6 sm:py-6 sm:px-8 sm:rounded-t-md flex items-center justify-between">
                <p>{{ __('Urlopy') }}</p>
                <span class="relative group inline-flex items-center gap-1 px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-orange-700 bg-orange-100 border border-orange-300 rounded cursor-help">BETA
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <span class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 rounded px-3 py-2 text-[11px] font-normal normal-case tracking-normal opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-50" style="background-color: #1f2937; color: #ffffff;">Wersja rozwojowa. Prosimy o zgłaszanie ewentualnych błędów.</span>
                </span>
            </header>

            <div class="w-full" id="vacations" data-is-admin="{{ Auth::user()->is_admin ? '1' : '0' }}"></div>
        </section>
    </div>
</main>
@endsection
