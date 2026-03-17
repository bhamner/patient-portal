<x-layouts::app :title="__('Send test SMS')">
    <div class="mx-auto max-w-md space-y-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Send test SMS') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Send a test text message to a phone number. Use E.164 format (e.g. +15551234567).') }}
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

        <form method="POST" action="{{ route('sms.test.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="to"
                :label="__('Phone number')"
                type="tel"
                :value="old('to')"
                required
                placeholder="+15551234567"
                :hint="__('E.164 format, e.g. +15551234567')"
            />

            <flux:textarea
                name="message"
                :label="__('Message')"
                :value="old('message')"
                required
                rows="3"
                maxlength="160"
                :hint="__('Max 160 characters')"
            />

            <flux:button variant="primary" type="submit">
                {{ __('Send SMS') }}
            </flux:button>
        </form>
    </div>
</x-layouts::app>
