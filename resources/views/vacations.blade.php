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
                <span class="inline-block px-2 py-0.5 text-xs font-bold uppercase tracking-wider text-orange-700 bg-orange-100 border border-orange-300 rounded cursor-help" title="Wersja rozwojowa — jeśli zauważysz błąd, zgłoś go proszę administratorowi.">BETA</span>
            </header>

            <div class="w-full" id="vacations" data-is-admin="{{ Auth::user()->is_admin ? '1' : '0' }}"></div>
        </section>
    </div>
</main>
@endsection
