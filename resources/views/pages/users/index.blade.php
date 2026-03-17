<x-layouts::app :title="__('Users')">
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Users') }}
                </h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('Manage users and roles for :name', ['name' => $organization->name]) }}
                </p>
            </div>
            @if ($canInvite)
                <flux:link :href="route('invitations.create')" variant="primary" wire:navigate>
                    {{ __('Invite user') }}
                </flux:link>
            @endif
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

        {{-- Pending invitations --}}
        @if ($pendingInvites->isNotEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Pending invitations') }}
                </h2>
                <ul class="mt-4 space-y-2">
                    @foreach ($pendingInvites as $invite)
                        <li class="flex items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm dark:border-zinc-700 dark:bg-zinc-800">
                            <div>
                                <span class="font-medium text-zinc-700 dark:text-zinc-200">{{ $invite->contact }}</span>
                                <span class="ml-2 rounded bg-zinc-200 px-1.5 py-0.5 text-xs text-zinc-600 dark:bg-zinc-700 dark:text-zinc-400">{{ __(ucfirst($invite->role)) }}</span>
                                <span class="ml-2 text-zinc-500 dark:text-zinc-400">{{ __('Expires :date', ['date' => $invite->expires_at->format('M j, Y')]) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Users table --}}
        <div id="users-table" data-flux-table>
            @livewire(\App\Livewire\DataTable::class, [
                'table' => 'users',
                'perPage' => 10,
                'context' => [
                    'organization' => $organization,
                    'canManageRoles' => $canManageRoles ?? false,
                ],
                'scrollToId' => 'users-table',
            ])
        </div>
    </div>
</x-layouts::app>
