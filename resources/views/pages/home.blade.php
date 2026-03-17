@extends('layouts.public')

@section('content')
    {{-- Hero: full-width parallax image (only when file exists; public/images is gitignored) + content that scrolls over it --}}
    @php
        $heroImage = 'images/medicine-doctor-touching-electronic-medical-record-on-tablet.jpg';
        $heroImageExists = file_exists(public_path($heroImage));
    @endphp

    @if ($heroImageExists)
        {{-- Full-width parallax: image stays fixed while content scrolls over it --}}
        <div
            class="w-full min-h-screen bg-cover bg-center bg-no-repeat"
            style="background-image: url({{ asset($heroImage) }}); background-attachment: fixed;"
            role="img"
            aria-hidden="true"
        ></div>
    @endif

    {{-- Hero content: overlaps bottom of image, then scrolls up and over it (solid bg covers image) --}}
    <section
        class="relative z-10 px-4 pt-16 pb-24 sm:px-6 sm:pt-24 sm:pb-32 lg:px-8 bg-white dark:bg-zinc-950 {{ $heroImageExists ? '-mt-[70vh]' : '' }}"
    >
        <div class="mx-auto max-w-4xl text-center">
            <h1 class="text-4xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-5xl lg:text-6xl">
                {{ __('Connect your practice with patients') }}
            </h1>
            <p class="mt-6 text-lg text-zinc-600 dark:text-zinc-400 sm:text-xl max-w-2xl mx-auto">
                {{ __('A patient portal that brings doctors and patients together with secure messaging, visit notes, appointments, and reminders—all in one place.') }}
            </p>
            <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                @if (Route::has('organization.signup'))
                    <a href="{{ route('organization.signup') }}" class="inline-flex w-full justify-center rounded-lg bg-[var(--color-portal-blue)] px-6 py-3 text-base font-medium text-white hover:opacity-90 dark:bg-[var(--color-portal-green)] dark:text-zinc-900 sm:w-auto" wire:navigate>
                        {{ __('Get started') }}
                    </a>
                @endif
                <a href="{{ route('pricing') }}" class="inline-flex w-full justify-center rounded-lg border border-zinc-300 bg-white px-6 py-3 text-base font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:bg-zinc-700 sm:w-auto" wire:navigate>
                    {{ __('View pricing') }}
                </a>
            </div>
        </div>
    </section>

    {{-- Features summary --}}
    <section class="border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-900/30 py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-center text-2xl font-semibold text-zinc-900 dark:text-white sm:text-3xl">
                {{ __('Everything you need to stay connected') }}
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-center text-zinc-600 dark:text-zinc-400">
                {{ __('Messaging, history, appointments, and reminders—so you can focus on care.') }}
            </p>
            <div class="mt-16 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Secure messaging') }}</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Private, compliant communication between providers and patients.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Patient history & notes') }}</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Keep visit notes and history in one secure, accessible place.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Appointments & scheduling') }}</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Schedule and manage appointments with ease.') }}</p>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-800/50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m-6 0H9" /></svg>
                    </div>
                    <h3 class="mt-4 font-semibold text-zinc-900 dark:text-white">{{ __('Appointment reminders') }}</h3>
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Automated email and SMS reminders to reduce no-shows.') }}</p>
                </div>
            </div>
            <div class="mt-12 text-center">
                <a href="{{ route('features') }}" class="text-sm font-medium text-portal-blue dark:text-portal-green hover:underline" wire:navigate>{{ __('See all features') }} &rarr;</a>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white sm:text-3xl">
                {{ __('Ready to get started?') }}
            </h2>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                {{ __('Register your organization, add your team, and start inviting patients. Free trial available.') }}
            </p>
            @if (Route::has('organization.signup'))
                <a href="{{ route('organization.signup') }}" class="mt-8 inline-flex rounded-lg bg-portal-blue px-6 py-3 text-base font-medium text-white hover:opacity-90 dark:bg-portal-green dark:text-zinc-900" wire:navigate>
                    {{ __('Register your organization') }}
                </a>
            @endif
        </div>
    </section>
@endsection
