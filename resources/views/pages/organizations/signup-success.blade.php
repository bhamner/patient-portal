<x-layouts::auth :title="__('Organization created')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Organization created')"
            :description="__('Your organization and administrator accounts are ready.')"
        />

        <div class="rounded-lg bg-zinc-50 dark:bg-zinc-800/50 px-4 py-3 text-sm text-zinc-700 dark:text-zinc-300 space-y-2">
            @if ($trial_days > 0)
                <p>{{ __('Your account is on a :days-day free trial. You can log in and use the portal now.', ['days' => $trial_days]) }}</p>
            @else
                <p>{{ __('Your account is active. You can log in and use the portal now.') }}</p>
            @endif
            <p>{{ __('Use one of the administrator email addresses and passwords you set to log in.') }}</p>
        </div>

        <flux:link :href="route('login')" variant="primary" class="inline-flex justify-center">
            {{ __('Log in') }}
        </flux:link>
    </div>
</x-layouts::auth>
