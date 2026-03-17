@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">
                {{ __('About') }} {{ config('app.name') }}
            </h1>
            <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                {{ __('A patient portal that connects practices and patients.') }}
            </p>
        </div>

        <div class="mt-16 prose prose-zinc dark:prose-invert max-w-none">
            <h2 class="text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Who it’s for') }}</h2>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                {{ __('Our patient portal is built for medical and healthcare practices that want to stay connected with patients through secure messaging, shared visit notes, and appointments. Whether you’re a small practice or a larger group, you can invite patients, manage schedules, send reminders, and keep history in one place.') }}
            </p>

            <h2 class="mt-12 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('How it works') }}</h2>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                {{ __('You create an organization, add your staff as administrators, and subscribe to a plan that matches your patient volume. After a free trial, you can invite patients by email or SMS. Patients use the same portal to message you, view relevant notes, and manage appointments. Reminders go out automatically to help reduce no-shows.') }}
            </p>

            <h2 class="mt-12 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Security & compliance') }}</h2>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                {{ __('We take security seriously. Data is stored securely and access is controlled by role. Communication is kept within the platform so sensitive information stays off personal email and SMS threads. We aim to support practices in meeting their compliance and privacy obligations.') }}
            </p>

            <h2 id="contact" class="mt-12 text-2xl font-semibold text-zinc-900 dark:text-white">{{ __('Contact') }}</h2>
            <p class="mt-4 text-zinc-600 dark:text-zinc-400">
                {{ __('For questions about pricing, custom plans, or enterprise needs, please reach out.') }}
            </p>
            <p class="mt-2 text-zinc-600 dark:text-zinc-400">
                {{ __('Email') }}: <a href="mailto:{{ config('mail.from.address', 'support@example.com') }}" class="font-medium text-portal-blue dark:text-portal-green hover:underline">{{ config('mail.from.address', 'support@example.com') }}</a>
            </p>
        </div>
    </div>
@endsection
