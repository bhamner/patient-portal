@props(['row', 'value'])

@php
    $date = $value ? ($value instanceof \Carbon\Carbon ? $value : \Carbon\Carbon::parse($value)) : null;
@endphp

@if ($date)
    <span class="whitespace-nowrap" title="{{ $date->format('M j, Y g:i A') }}">
        {{ $date->format('M j, Y') }}
    </span>
@else
    <span class="text-zinc-400 dark:text-zinc-500">—</span>
@endif
