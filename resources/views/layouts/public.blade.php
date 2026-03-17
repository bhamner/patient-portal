<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="sticky top-0 z-50 border-b border-zinc-200 bg-white/95 dark:border-zinc-800 dark:bg-zinc-900/95 backdrop-blur">
            <nav class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-medium text-zinc-900 dark:text-white" wire:navigate>
                    <x-app-logo-icon class="size-8" aria-hidden="true" />
                    <span>{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center gap-6">
                    <a href="{{ route('home') }}" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Home') }}</a>
                    <a href="{{ route('features') }}" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Features') }}</a>
                    <a href="{{ route('pricing') }}" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('Pricing') }}</a>
                    <a href="{{ route('about') }}" class="text-sm text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white" wire:navigate>{{ __('About') }}</a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-[var(--color-portal-blue)] dark:text-[var(--color-portal-green)]" wire:navigate>{{ __('Dashboard') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-900 dark:hover:text-white" wire:navigate>{{ __('Log in') }}</a>
                        @if (Route::has('organization.signup'))
                            <a href="{{ route('organization.signup') }}" class="inline-flex items-center rounded-lg bg-[var(--color-portal-blue)] px-4 py-2 text-sm font-medium text-white hover:opacity-90 dark:bg-[var(--color-portal-green)] dark:text-zinc-900" wire:navigate>{{ __('Get started') }}</a>
                        @endif
                    @endauth
                </div>
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 mt-24">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <a href="{{ route('home') }}" class="flex items-center gap-2" wire:navigate>
                        <x-app-logo-icon class="size-6 fill-[var(--color-portal-green)]" />
                        <span class="font-medium">{{ config('app.name') }}</span>
                    </a>
                    <div class="flex gap-8 text-sm text-zinc-600 dark:text-zinc-400">
                        <a href="{{ route('features') }}" class="hover:text-zinc-900 dark:hover:text-white" wire:navigate>{{ __('Features') }}</a>
                        <a href="{{ route('pricing') }}" class="hover:text-zinc-900 dark:hover:text-white" wire:navigate>{{ __('Pricing') }}</a>
                        <a href="{{ route('about') }}" class="hover:text-zinc-900 dark:hover:text-white" wire:navigate>{{ __('About') }}</a>
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="hover:text-zinc-900 dark:hover:text-white" wire:navigate>{{ __('Log in') }}</a>
                        @endif
                    </div>
                </div>
                <p class="mt-8 text-center text-sm text-zinc-500 dark:text-zinc-500">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
            </div>
        </footer>

        @fluxScripts
    </body>
</html>
