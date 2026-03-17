<x-layouts::app :title="__('Invite user')">
    <div class="mx-auto max-w-md space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Invite user') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Create an invite link and send it by email or SMS. The recipient will use it to create an account.') }}
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-200">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invitations.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:select
                name="organization_id"
                :label="__('Organization')"
                :value="old('organization_id', $currentOrganization?->id ?? $organizations->first()?->id)"
                required
            >
                @foreach ($organizations as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
            </flux:select>

            <flux:select
                name="role"
                :label="__('Role')"
                :value="old('role', 'patient')"
                required
            >
                <option value="patient">{{ __('Patient') }}</option>
                <option value="physician">{{ __('Physician') }}</option>
                <option value="staff">{{ __('Staff') }}</option>
                <option value="admin">{{ __('Admin') }}</option>
            </flux:select>

            <flux:input
                name="email"
                :label="__('Email (optional if sending by SMS only)')"
                type="email"
                :value="old('email')"
                placeholder="email@example.com"
            />

            <flux:input
                name="phone"
                :label="__('Phone (optional if sending by email only). E.164 format.')"
                type="tel"
                :value="old('phone')"
                placeholder="+15551234567"
            />

            <flux:select
                name="send_via"
                :label="__('Send invite via')"
                :value="old('send_via', 'email')"
                required
            >
                <option value="email">{{ __('Email only') }}</option>
                <option value="sms">{{ __('SMS only') }}</option>
                <option value="both">{{ __('Email and SMS') }}</option>
            </flux:select>

            <flux:button variant="primary" type="submit">
                {{ __('Invite user') }}
            </flux:button>
        </form>
    </div>
</x-layouts::app>
