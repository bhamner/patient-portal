<x-layouts::app :title="__('Organization settings')">
    <div class="mx-auto max-w-2xl space-y-8">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Organization settings') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Manage calendar and appointment settings for :name', ['name' => $organization->name]) }}
            </p>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        {{-- Organization branding --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Organization branding') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Customize your organization name, logo, and colors.') }}
            </p>
            <form method="POST" action="{{ route('organization.branding') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <flux:input
                    name="name"
                    :label="__('Organization name')"
                    :value="old('name', $organization->name)"
                    required
                />
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Logo') }}</label>
                    @if ($organization->logo_url)
                        <div class="mt-2 flex items-center gap-4">
                            <img src="{{ Storage::url($organization->logo_url) }}" alt="" class="h-16 w-auto object-contain rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <label class="inline-flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-400">
                                <input type="checkbox" name="remove_logo" value="1" class="rounded border-zinc-300 text-[var(--color-portal-blue)] focus:ring-[var(--color-portal-blue)] dark:border-zinc-600 dark:bg-zinc-800">
                                {{ __('Remove logo') }}
                            </label>
                        </div>
                    @endif
                    <input
                        type="file"
                        name="logo"
                        accept=".png,.jpg,.jpeg,.gif,.webp,.svg"
                        class="mt-2 block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200 dark:text-zinc-400 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700"
                    >
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('PNG, JPG, GIF, WebP or SVG. Max 2MB.') }}</p>
                </div>
                <div x-data="{
                    primaryColor: '{{ old('primary_color', $organization->primary_color ?? '#00adee') }}',
                    secondaryColor: '{{ old('secondary_color', $organization->secondary_color ?? '#7ac8a9') }}',
                    accentColor: '{{ old('accent_color', $organization->accent_color ?? '#00adee') }}'
                }">
                    <span class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Brand colors') }}</span>
                    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Used for buttons, links, and accents across the portal.') }}</p>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Primary') }}</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" x-model="primaryColor" class="h-10 w-14 cursor-pointer rounded border border-zinc-200 bg-transparent p-1 dark:border-zinc-600">
                                <input type="text" name="primary_color" x-model="primaryColor" placeholder="#00adee" class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-mono dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Secondary') }}</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" x-model="secondaryColor" class="h-10 w-14 cursor-pointer rounded border border-zinc-200 bg-transparent p-1 dark:border-zinc-600">
                                <input type="text" name="secondary_color" x-model="secondaryColor" placeholder="#7ac8a9" class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-mono dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ __('Accent') }}</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input type="color" x-model="accentColor" class="h-10 w-14 cursor-pointer rounded border border-zinc-200 bg-transparent p-1 dark:border-zinc-600">
                                <input type="text" name="accent_color" x-model="accentColor" placeholder="#00adee" class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm font-mono dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                            </div>
                        </div>
                    </div>
                </div>
                <flux:button type="submit" variant="primary">
                    {{ __('Save branding') }}
                </flux:button>
            </form>
        </div>

        {{-- Slot duration --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Appointment slot duration') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Length of each appointment slot on the calendar.') }}
            </p>
            <form method="POST" action="{{ route('organization.appointment-settings') }}" class="mt-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="business_hours_start" value="{{ $businessHoursStart }}">
                <input type="hidden" name="business_hours_end" value="{{ $businessHoursEnd }}">
                @foreach ($businessDays as $d)
                    <input type="hidden" name="business_days[]" value="{{ $d }}">
                @endforeach
                <flux:select
                    name="appointment_slot_minutes"
                    :label="__('Slot duration')"
                    onchange="this.form.submit()"
                >
                    <option value="15" @selected($slotMinutes === 15)>{{ __('15 minutes') }}</option>
                    <option value="30" @selected($slotMinutes === 30)>{{ __('30 minutes') }}</option>
                    <option value="60" @selected($slotMinutes === 60)>{{ __('1 hour') }}</option>
                </flux:select>
            </form>
        </div>

        {{-- Business hours --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Business hours') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Days and times when appointments are available.') }}
            </p>
            <form method="POST" action="{{ route('organization.appointment-settings') }}" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="appointment_slot_minutes" value="{{ $slotMinutes }}">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:input
                        name="business_hours_start"
                        type="time"
                        :label="__('Open')"
                        :value="old('business_hours_start', $businessHoursStart)"
                    />
                    <flux:input
                        name="business_hours_end"
                        type="time"
                        :label="__('Close')"
                        :value="old('business_hours_end', $businessHoursEnd)"
                    />
                </div>
                <div>
                    <span class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Open days') }}</span>
                    <div class="mt-2 flex flex-wrap gap-3">
                        @foreach (\App\Models\Organization::BUSINESS_DAYS_OPTIONS as $dayNum => $dayName)
                            <label class="inline-flex items-center gap-1.5 text-sm">
                                <input
                                    type="checkbox"
                                    name="business_days[]"
                                    value="{{ $dayNum }}"
                                    {{ in_array($dayNum, $businessDays, true) ? 'checked' : '' }}
                                    class="rounded border-zinc-300 text-[var(--color-portal-blue)] focus:ring-[var(--color-portal-blue)] dark:border-zinc-600 dark:bg-zinc-800"
                                >
                                <span class="text-zinc-600 dark:text-zinc-400">{{ __($dayName) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <flux:button type="submit" variant="primary">
                    {{ __('Save business hours') }}
                </flux:button>
            </form>
        </div>

        {{-- Holidays --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Holidays') }}
            </h2>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Days when the organization is closed. These appear as closed on the calendar.') }}
            </p>
            <form method="POST" action="{{ route('holidays.store') }}" class="mt-4 space-y-4">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <flux:input
                        name="date"
                        type="date"
                        :label="__('Date')"
                        required
                    />
                    <flux:input
                        name="name"
                        :label="__('Name (optional)')"
                        placeholder="{{ __('e.g. Christmas Day') }}"
                    />
                    <flux:button type="submit" variant="primary">
                        {{ __('Add holiday') }}
                    </flux:button>
                </div>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        name="recurring"
                        value="1"
                        {{ old('recurring') ? 'checked' : '' }}
                        class="rounded border-zinc-300 text-[var(--color-portal-blue)] focus:ring-[var(--color-portal-blue)] dark:border-zinc-600 dark:bg-zinc-800"
                    >
                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Recurring (every year)') }}</span>
                </label>
            </form>
            @if ($holidays->isNotEmpty())
                <ul class="mt-6 space-y-2">
                    @foreach ($holidays as $holiday)
                        <li class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <span class="text-zinc-700 dark:text-zinc-200">
                                @if ($holiday->recurring)
                                    {{ $holiday->date->format('F j') }}{{ $holiday->name ? ' · ' . $holiday->name : '' }}
                                    <span class="ml-1 rounded bg-zinc-200 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">{{ __('Every year') }}</span>
                                @else
                                    {{ $holiday->date->format('l, F j, Y') }}{{ $holiday->name ? ' · ' . $holiday->name : '' }}
                                @endif
                            </span>
                            <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-zinc-400 hover:text-red-600 dark:hover:text-red-400" title="{{ __('Remove') }}">
                                    <span class="sr-only">{{ __('Remove') }}</span>
                                    &times;
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts::app>
