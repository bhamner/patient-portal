@extends('layouts.public')

@section('content')
    <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
        <div class="text-center">
            <h1 class="text-4xl font-semibold tracking-tight text-zinc-900 dark:text-white sm:text-5xl">
                {{ __('Simple pricing for practices of every size') }}
            </h1>
            <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                {{ __('Choose a plan based on the number of active patients. All plans include messaging, notes, appointments, and reminders.') }}
            </p>
        </div>

        <div class="mt-16 grid gap-8 lg:grid-cols-3">
            @foreach (config('plans.order', []) as $key)
                @php $plan = config("plans.plans.{$key}", []); @endphp
                @if (!empty($plan))
                    <x-plan-card :plan="$plan" variant="public" />
                @endif
            @endforeach
        </div>

        <p class="mt-12 text-center text-sm text-zinc-500 dark:text-zinc-500">
            {{ __('All plans include a free trial. Need more than :limit patients?', ['limit' => number_format(config('plans.highest_limit', 10000))]) }}
            <a href="{{ route('about') }}#contact" class="font-medium text-portal-blue dark:text-portal-green hover:underline" wire:navigate>{{ __('Contact us') }}</a>.
        </p>
    </div>
@endsection
