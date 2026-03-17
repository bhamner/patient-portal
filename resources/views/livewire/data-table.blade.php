<div>
    {{-- Search and filters (Filament-style) --}}
    <div class="mb-4 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-end">
        <div class="flex items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                :placeholder="__('Search...')"
                icon="magnifying-glass"
                class="min-w-[200px] sm:max-w-xs"
            />
            @if ($this->getDefinition->filters())
                <flux:dropdown position="bottom" align="end">
                    <flux:tooltip :content="__('Filters')" position="bottom">
                        <div class="relative">
                            <flux:button variant="ghost" icon="funnel" icon:variant="outline" size="sm" square>
                            </flux:button>
                            @if ($this->getActiveFilterCount() > 0)
                                <flux:badge size="sm" color="zinc" class="absolute -end-1 -top-1">
                                    {{ $this->getActiveFilterCount() }}
                                </flux:badge>
                            @endif
                        </div>
                    </flux:tooltip>
                    <flux:menu keep-open class="min-w-[200px] p-2">
                        @foreach ($this->getDefinition->filters() as $filter)
                            <flux:menu.group :heading="$filter['label']">
                                <flux:menu.radio.group wire:model.live="filters.{{ $filter['key'] }}" keep-open>
                                    @foreach ($filter['options'] as $value => $label)
                                        <flux:menu.radio :value="$value">
                                            {{ $label }}
                                        </flux:menu.radio>
                                    @endforeach
                                </flux:menu.radio.group>
                            </flux:menu.group>
                            @if (!$loop->last)
                                <flux:menu.separator />
                            @endif
                        @endforeach
                    </flux:menu>
                </flux:dropdown>
            @endif
            <flux:tooltip :content="__('Download CSV')" position="bottom">
                <flux:button variant="ghost" size="sm" icon="arrow-down-tray" icon:variant="outline" wire:click="downloadCsv" square class="ms-auto" />
            </flux:tooltip>
        </div>
    </div>

    {{-- Active filters row --}}
    @if ($this->getActiveFilterCount() > 0)
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-lg border border-zinc-200 bg-zinc-50/50 px-4 py-3 dark:border-zinc-700 dark:bg-zinc-800/30">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Current filters:') }}</span>
                @foreach ($this->getActiveFiltersWithLabels() as $active)
                    <flux:badge size="sm" color="zinc">
                        {{ $active['filterLabel'] }}: {{ $active['valueLabel'] }}
                    </flux:badge>
                @endforeach
            </div>
            <flux:button variant="ghost" size="sm" icon="x-mark" icon:variant="outline" wire:click="clearFilters">
                {{ __('Clear all') }}
            </flux:button>
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
        <flux:table :paginate="null">
        <flux:table.columns sticky class="bg-white dark:bg-zinc-900">
            @foreach ($this->getDefinition->columns() as $column)
                @if ($column['sortable'] ?? false)
                    <flux:table.column
                        sortable
                        :sorted="$sortBy === ($column['key'])"
                        :direction="$sortDirection"
                        :align="$column['align'] ?? 'start'"
                        wire:click="sort('{{ $column['key'] }}')"
                    >
                        {{ __($column['label']) }}
                    </flux:table.column>
                @else
                    <flux:table.column :align="$column['align'] ?? 'start'">
                        {{ __($column['label']) }}
                    </flux:table.column>
                @endif
            @endforeach
        </flux:table.columns>
        <flux:table.rows>
            @php $context = $this->context ?? []; @endphp
            @forelse ($this->rows as $row)
                <flux:table.row :key="$row->id">
                    @foreach ($this->getDefinition->columns() as $column)
                        <flux:table.cell :align="$column['align'] ?? 'start'">
                            @if (isset($column['view']))
                                @include($column['view'], ['row' => $row, 'value' => data_get($row, $column['key']), 'context' => $context])
                            @else
                                {{ data_get($row, $column['key']) }}
                            @endif
                        </flux:table.cell>
                    @endforeach
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell :colspan="count($this->getDefinition->columns())" class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                        {{ $this->getDefinition->emptyMessage() }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>
    </div>

    {{-- Custom pagination: Showing (left), Per page (center), Page controls (right) --}}
    @php
        $paginator = $this->rows;
        $scrollToSelector = $scrollToId ? '#' . $scrollToId : null;
        $scrollIntoViewJs = $scrollToSelector
            ? "(\$el.closest('{$scrollToSelector}') || document.querySelector('{$scrollToSelector}')).scrollIntoView()"
            : '';
    @endphp
    <div class="relative flex flex-wrap items-center gap-3 border-t border-zinc-100 pt-3 dark:border-zinc-700" data-flux-pagination>
        {{-- Per page (bottom left) --}}
        <div class="flex items-center gap-2">
            <span class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Per page') }}</span>
            <flux:select wire:model.live="perPage" size="sm" class="w-20">
                @foreach (\App\Livewire\DataTable::PER_PAGE_OPTIONS as $option)
                    <option value="{{ $option }}">{{ $option === 'all' ? __('All') : $option }}</option>
                @endforeach
            </flux:select>
        </div>

        {{-- Pagination numbers (bottom right) --}}
        @if ($paginator->hasPages())
            <div class="flex items-center bg-white border border-zinc-200 rounded-[8px] p-[1px] dark:bg-white/10 dark:border-white/10 shrink-0 ml-auto">
                @if ($paginator->onFirstPage())
                    <div class="flex justify-center items-center size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                        <flux:icon.chevron-left variant="micro" />
                    </div>
                @else
                    <button type="button" wire:click="previousPage('{{ $paginator->getPageName() }}')" @if($scrollIntoViewJs) x-on:click="{{ $scrollIntoViewJs }}" @endif class="flex justify-center items-center size-6 rounded-[6px] text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">
                        <flux:icon.chevron-left variant="micro" />
                    </button>
                @endif
                @foreach (\Livewire\invade($paginator)->elements() as $element)
                    @if (is_string($element))
                        <div class="cursor-default flex justify-center items-center text-xs size-6 rounded-[6px] font-medium text-zinc-400 dark:text-zinc-400">{{ $element }}</div>
                    @endif
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <div class="cursor-default flex justify-center items-center text-xs h-6 px-2 rounded-[6px] font-medium dark:text-white text-zinc-800">{{ $page }}</div>
                            @else
                                <button type="button" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" @if($scrollIntoViewJs) x-on:click="{{ $scrollIntoViewJs }}" @endif class="text-xs h-6 px-2 rounded-[6px] text-zinc-400 font-medium dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">{{ $page }}</button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $paginator->getPageName() }}')" @if($scrollIntoViewJs) x-on:click="{{ $scrollIntoViewJs }}" @endif class="flex justify-center items-center size-6 rounded-[6px] text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">
                        <flux:icon.chevron-right variant="micro" />
                    </button>
                @else
                    <div class="flex justify-center items-center size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                        <flux:icon.chevron-right variant="micro" />
                    </div>
                @endif
            </div>
        @endif

        {{-- Results text (centered overlay) --}}
        <div class="pointer-events-none absolute inset-x-0 flex justify-center">
            <div class="pointer-events-auto text-zinc-500 dark:text-zinc-400 text-xs font-medium whitespace-nowrap">
                @if ($paginator->total() > 0)
                    {!! __('Showing') !!} {{ $paginator->firstItem() }} {!! __('to') !!} {{ $paginator->lastItem() }} {!! __('of') !!} {{ $paginator->total() }} {!! __('results') !!}
                @else
                    &nbsp;
                @endif
            </div>
        </div>
    </div>
</div>
