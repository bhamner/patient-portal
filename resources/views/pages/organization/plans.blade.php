<x-layouts::app :title="__('Plans')">
    <div class="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Plans') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Upgrade your plan to add more users and patients.') }}
            </p>
        </div>

        @php
            $currentLimit = $organization->getPlanLimit();
            $userCount = $organization->getUserCount();
        @endphp

        <div class="mb-8 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/50">
            <p class="text-sm text-zinc-700 dark:text-zinc-300">
                {{ __('Current usage') }}: <strong>{{ $userCount }} / {{ $currentLimit }}</strong> {{ __('users & patients') }}
            </p>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            @foreach (config('plans.order', []) as $key)
                @php $plan = config("plans.plans.{$key}", []); @endphp
                @if (!empty($plan))
                    <x-plan-card :plan="$plan" variant="organization" :current-limit="$currentLimit" />
                @endif
            @endforeach
        </div>

        <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-500">
            {{ __('Need more than :limit users & patients?', ['limit' => number_format(config('plans.highest_limit', 10000))]) }}
            <a href="{{ route('about') }}#contact" class="font-medium text-portal-blue dark:text-portal-green hover:underline" wire:navigate>{{ __('Contact us') }}</a>.
        </p>
    </div>
</x-layouts::app>
