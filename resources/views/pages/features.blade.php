@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">
                {{ __('Features built for care') }}
            </h1>
            <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                {{ __('Tools that help providers and patients stay connected and organized.') }}
            </p>
        </div>

        <div class="mt-20 space-y-24">
            <section class="flex flex-col gap-8 md:flex-row md:items-center md:gap-12">
                <div class="flex-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                    </div>
                    <h2 class="mt-6 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Secure messaging') }}</h2>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('Communicate with patients in a private, compliant way. Messages are stored securely and tied to the patient record, so your team and patients can stay in sync without relying on personal email or phone.') }}
                    </p>
                </div>
            </section>

            <section class="flex flex-col gap-8 md:flex-row-reverse md:items-center md:gap-12">
                <div class="flex-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h2 class="mt-6 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Patient history & notes') }}</h2>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('Keep visit notes and patient history in one place. Providers can document encounters and review past notes, so care is continuous and nothing falls through the cracks.') }}
                    </p>
                </div>
            </section>

            <section class="flex flex-col gap-8 md:flex-row md:items-center md:gap-12">
                <div class="flex-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <h2 class="mt-6 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Appointments & scheduling') }}</h2>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('Schedule and manage appointments from a single calendar. Patients can see availability and book when it works for them; staff can reschedule or manage recurring visits without double-booking.') }}
                    </p>
                </div>
            </section>

            <section class="flex flex-col gap-8 md:flex-row-reverse md:items-center md:gap-12">
                <div class="flex-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-portal-blue/10 dark:bg-portal-green/20 text-portal-blue dark:text-portal-green" aria-hidden="true">
                        <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m-6 0H9" /></svg>
                    </div>
                    <h2 class="mt-6 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Appointment reminders') }}</h2>
                    <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                        {{ __('Reduce no-shows with automated reminders. Send email and SMS reminders before appointments so patients are more likely to show up, and free your staff from manual follow-ups.') }}
                    </p>
                </div>
            </section>
        </div>

        <div class="mt-24 rounded-2xl border border-zinc-200 bg-zinc-50 p-8 dark:border-zinc-700 dark:bg-zinc-800/50 text-center">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ __('Ready to try it?') }}</h2>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('Start with a free trial—no credit card required.') }}</p>
            @if (Route::has('organization.signup'))
                <a href="{{ route('organization.signup') }}" class="mt-6 inline-flex rounded-lg bg-portal-blue px-6 py-2.5 text-sm font-medium text-white hover:opacity-90 dark:bg-portal-green dark:text-zinc-900" wire:navigate>{{ __('Get started') }}</a>
            @endif
        </div>
    </div>
@endsection
