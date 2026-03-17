<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        @php $org = $currentOrganization ?? (app()->has(\App\Models\Organization::class) ? app(\App\Models\Organization::class) : null); @endphp
        @if($org && ($org->primary_color || $org->accent_color))
                <style>
                    :root {
                        @if($org->primary_color)
                            --color-portal-blue: {{ $org->primary_color }};
                        @endif
                        @if($org->accent_color)
                            --color-portal-green: {{ $org->accent_color }};
                        @endif
                    }
                </style>
        @endif
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.header>
                <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
                <flux:sidebar.collapse class="lg:hidden" />
            </flux:sidebar.header>

            <flux:sidebar.nav>
                <flux:sidebar.group :heading="__('Platform')" class="grid">
                    <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="calendar" :href="route('appointments.calendar')" :current="request()->routeIs('appointments.*')" wire:navigate>
                        {{ __('Appointments') }}
                    </flux:sidebar.item>
                    @if (auth()->user()?->hasAnyRole('admin', 'staff'))
                        <flux:sidebar.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.*')" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="building-office-2" :href="route('organization.settings')" :current="request()->routeIs('organization.settings')" wire:navigate>
                            {{ __('Organization') }}
                        </flux:sidebar.item>
                        <flux:sidebar.item icon="credit-card" :href="route('organization.plans')" :current="request()->routeIs('organization.plans')" wire:navigate>
                            {{ __('Plans') }}
                        </flux:sidebar.item>
                    @endif
                </flux:sidebar.group>
            </flux:sidebar.nav>

            <flux:sidebar.spacer />

            @if ($org && auth()->user()?->hasAnyRole('admin', 'staff'))
                @php
                    $userCount = $org->getUserCount();
                    $planLimit = $org->getPlanLimit();
                    $percent = $planLimit > 0 ? min(100, (int) round(($userCount / $planLimit) * 100)) : 0;
                    $onHighestPlan = $org->isOnHighestPlan();
                @endphp
                <div class="px-2 pb-2 in-data-flux-sidebar-collapsed-desktop:hidden">
                    <div class="rounded-lg border border-zinc-200 bg-zinc-100/50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800/50">
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="text-zinc-600 dark:text-zinc-400">{{ __('Users & Patients') }}</span>
                            <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $userCount }} / {{ $planLimit }}</span>
                        </div>
                        <div class="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-zinc-200 dark:bg-zinc-700">
                            <div
                                class="h-full rounded-full bg-[var(--color-portal-blue)] transition-all dark:bg-[var(--color-portal-green)]"
                                style="width: {{ $percent }}%"
                            ></div>
                        </div>
                        @if (!$onHighestPlan)
                            <a href="{{ route('organization.plans') }}" class="mt-2 block text-xs font-medium text-[var(--color-portal-blue)] hover:underline dark:text-[var(--color-portal-green)]" wire:navigate>
                                {{ __('Upgrade') }}
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            <x-desktop-user-menu class="hidden lg:block" :name="auth()->user()->name" />
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar
                                    :name="auth()->user()->name"
                                    :initials="auth()->user()->initials()"
                                />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">{{ auth()->user()->name }}</flux:heading>
                                    <flux:text class="truncate">{{ auth()->user()->email }}</flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
