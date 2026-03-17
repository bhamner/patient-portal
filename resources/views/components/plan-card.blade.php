@props([
    'plan' => [],
    'variant' => 'public', // 'public' | 'organization'
    'currentLimit' => null,
])

@php
    $name = $plan['name'] ?? '';
    $tagline = $plan['tagline'] ?? '';
    $price = $plan['price'] ?? 0;
    $limit = $plan['limit'] ?? 0;
    $features = $plan['features'] ?? [];
    $featured = $plan['featured'] ?? false;

    $highestLimit = config('plans.highest_limit', 10000);
    $isHighest = $limit >= $highestLimit;
    $isCurrentPlan = $variant === 'organization' && $currentLimit !== null && ($currentLimit === $limit || ($isHighest && $currentLimit >= $limit));
    $canUpgrade = $variant === 'organization' && $currentLimit !== null && $currentLimit < $limit;
@endphp

<div class="flex flex-col rounded-2xl {{ $featured ? 'relative border-2 border-portal-blue bg-white p-8 shadow-lg dark:border-portal-green dark:bg-zinc-800/80' : 'border border-zinc-200 bg-white p-8 dark:border-zinc-700 dark:bg-zinc-800/50' }}">
    @if ($featured)
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-portal-blue px-3 py-0.5 text-xs font-medium text-white dark:bg-portal-green dark:text-zinc-900">{{ __('Most popular') }}</div>
    @endif
    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __($name) }}</h2>
    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __($tagline) }}</p>
    <div class="mt-6 flex items-baseline gap-1">
        <span class="text-4xl font-semibold tracking-tight text-zinc-900 dark:text-white">${{ number_format($price) }}</span>
        <span class="text-zinc-500 dark:text-zinc-400">/{{ __('month') }}</span>
    </div>
    <ul class="mt-8 flex-1 space-y-4 text-sm text-zinc-600 dark:text-zinc-400">
        @foreach ($features as $feature)
            <li class="flex gap-3">
                <svg class="h-5 w-5 shrink-0 text-portal-green" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                {{ __($feature, ['limit' => number_format($limit)]) }}
            </li>
        @endforeach
    </ul>

    @if ($variant === 'public')
        @if (Route::has('organization.signup'))
            <a href="{{ route('organization.signup') }}" class="{{ $featured ? 'mt-8 inline-flex justify-center rounded-lg bg-portal-blue px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 dark:bg-portal-green dark:text-zinc-900' : 'mt-8 inline-flex justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600' }}" wire:navigate>{{ __('Get started') }}</a>
        @endif
    @else
        @if ($isCurrentPlan)
            <span class="mt-8 inline-flex justify-center rounded-lg border border-zinc-300 bg-zinc-100 px-4 py-2.5 text-sm font-medium text-zinc-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                {{ __('Current plan') }}
            </span>
        @elseif ($canUpgrade && $isHighest)
            <a href="{{ route('about') }}#contact" class="mt-8 inline-flex justify-center rounded-lg border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600" wire:navigate>
                {{ __('Contact us') }}
            </a>
        @elseif ($canUpgrade)
            <a href="{{ route('pricing') }}" class="mt-8 inline-flex justify-center rounded-lg {{ $featured ? 'bg-portal-blue px-4 py-2.5 text-sm font-medium text-white hover:opacity-90 dark:bg-portal-green dark:text-zinc-900' : 'border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-600' }}" wire:navigate>
                {{ __('Upgrade') }}
            </a>
        @endif
    @endif
</div>
