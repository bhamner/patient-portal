@props(['row', 'value'])

<div class="flex flex-wrap gap-1">
    @foreach ($row->roles->pluck('name') as $role)
        <span class="rounded bg-zinc-200 px-1.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">{{ __(ucfirst($role)) }}</span>
    @endforeach
</div>
