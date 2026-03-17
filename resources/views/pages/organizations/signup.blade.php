<x-layouts::auth :title="__('Register your organization')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Register your organization')"
            :description="__('Add your organization and at least one administrator. You will be sent to Stripe to activate your account (free trial available).')"
        />

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-200">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('organization.signup.store') }}" class="flex flex-col gap-6" x-data="{ adminCount: 1 }">
            @csrf

            <flux:input
                name="name"
                :label="__('Organization name')"
                :value="old('name')"
                required
                autofocus
            />

            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input
                    name="subdomain"
                    :label="__('Organization subdomain')"
                    :value="old('subdomain')"
                    required
                    helper-text="{{ __('Your portal will be available at :url. Do not use PHI or patient names.', ['url' => 'https://{subdomain}.' . parse_url(config('app.url'), PHP_URL_HOST)]) }}"
                    placeholder="your-practice"
                />
                <flux:input
                    name="primary_color"
                    :label="__('Primary color (hex)')"
                    :value="old('primary_color')"
                    placeholder="#00adee"
                />
            </div>

            <div class="space-y-4">
                <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Administrators') }}</p>

                @foreach ([0, 1] as $i)
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 space-y-3" x-show="adminCount > {{ $i }}" x-cloak>
                        @if ($i > 0)
                            <p class="text-xs font-medium text-zinc-500">{{ __('Administrator') }} {{ $i + 1 }}</p>
                        @endif
                        <flux:input name="admins[{{ $i }}][name]" :label="__('Name')" :value="old('admins.'.$i.'.name')" {{ $i === 0 ? 'required' : '' }} />
                        <flux:input name="admins[{{ $i }}][email]" type="email" :label="__('Email')" :value="old('admins.'.$i.'.email')" {{ $i === 0 ? 'required' : '' }} />
                        <flux:input name="admins[{{ $i }}][password]" type="password" :label="__('Password')" viewable {{ $i === 0 ? 'required' : '' }} />
                        <flux:input name="admins[{{ $i }}][password_confirmation]" type="password" :label="__('Confirm password')" viewable {{ $i === 0 ? 'required' : '' }} />
                    </div>
                @endforeach

                <button type="button" x-show="adminCount < 2" x-on:click="adminCount++" class="text-sm text-zinc-600 dark:text-zinc-400 hover:underline">
                    {{ __('Add another administrator') }}
                </button>
            </div>

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Continue to payment') }}
            </flux:button>
        </form>

        <p class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            {{ __('Already have an account?') }}
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </p>
    </div>
</x-layouts::auth>
