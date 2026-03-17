@php
    use Carbon\Carbon;

    $carbonStart = $startOfMonth instanceof \Carbon\CarbonInterface ? $startOfMonth : Carbon::parse($startOfMonth);
    $firstDayOfWeekIndex = $carbonStart->copy()->startOfWeek()->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
    $daysInMonth = $carbonStart->daysInMonth;
    $currentDate = now()->toDateString();

    $prevMonth = $carbonStart->copy()->subMonth();
    $nextMonth = $carbonStart->copy()->addMonth();
@endphp

<x-layouts::app :title="__('Appointments')">
    <div
        class="flex flex-col gap-6"
        x-data="{
            appointmentsData: @js($appointmentsByDayJson),
            selectedPhysicianId: '',
            selectedPatientId: '',
            showDayModal: false,
            dayModalDate: '',
            dayModalAppointments: [],
            showEditModal: false,
            showAddModal: false,
            slotMinutes: {{ $slotMinutes }},
            workingStart: '{{ $workingStart }}',
            workingEnd: '{{ $workingEnd }}',
            slotsByDay: @js($slotsByDay),
            physicians: @js($physiciansJson),
            patients: @js($patientsJson),
            storeUrl: '{{ $storeUrl }}',
            canAdd: {{ ($isStaff || $isPhysician) ? 'true' : 'false' }},
            requirePhysicianForDayModal: {{ $isStaff ? 'true' : 'false' }},
            showSelectPhysicianHint: false,
            physicianSearchQuery: '',
            patientSearchQuery: '',
            physicianSelectOpen: false,
            patientSelectOpen: false,
            get filteredPhysicians() {
                const q = (this.physicianSearchQuery || '').toLowerCase().trim();
                if (!q) return this.physicians;
                return this.physicians.filter(p => (p.name || '').toLowerCase().includes(q));
            },
            get filteredPatients() {
                const q = (this.patientSearchQuery || '').toLowerCase().trim();
                if (!q) return this.patients;
                return this.patients.filter(p => (p.name || '').toLowerCase().includes(q));
            },
            getSelectedPhysicianLabel() {
                if (!this.selectedPhysicianId) return '';
                const p = this.physicians.find(x => String(x.id) === String(this.selectedPhysicianId));
                return p?.name ?? '';
            },
            getSelectedPatientLabel() {
                if (!this.selectedPatientId) return '';
                const p = this.patients.find(x => String(x.id) === String(this.selectedPatientId));
                return p?.name ?? '';
            },
            form: {
                id: null,
                patient_name: '',
                title: '',
                starts_at: '',
                ends_at: '',
                status: 'scheduled',
                notes: '',
                edit_url: '',
                delete_url: '',
            },
            addForm: {
                patient_id: '',
                physician_id: '{{ ($isPhysician && auth()->user()?->physician) ? auth()->user()->physician->id : '' }}',
                title: '',
                starts_at: '',
                ends_at: '',
                notes: '',
            },
            getFilteredCount(dateKey) {
                return this.getFilteredForDay(dateKey).length;
            },
            getFilteredForDay(dateKey) {
                let list = this.appointmentsData[dateKey] || [];
                if (this.selectedPhysicianId) list = list.filter(a => String(a.physician_id) === String(this.selectedPhysicianId));
                if (this.selectedPatientId) list = list.filter(a => String(a.patient_id) === String(this.selectedPatientId));
                return list;
            },
            getAppointmentsForSlotOccupancy(dateKey) {
                let list = this.appointmentsData[dateKey] || [];
                if (this.selectedPhysicianId) list = list.filter(a => String(a.physician_id) === String(this.selectedPhysicianId));
                return list;
            },
            isDayFullyBooked(dateKey) {
                if (!dateKey || !this.canAdd) return false;
                const slots = (this.slotsByDay || {})[dateKey];
                if (!slots || slots.total === 0) return false;
                return this.getAvailableSlots(dateKey).length === 0;
            },
            getAvailableSlots(dateKey) {
                if (!dateKey) return [];
                const slots = (this.slotsByDay || {})[dateKey];
                if (!slots || slots.total === 0) return [];
                const appts = this.getAppointmentsForSlotOccupancy(dateKey);
                const [sh, sm] = this.workingStart.split(':').map(Number);
                const [eh, em] = this.workingEnd.split(':').map(Number);
                const startMins = sh * 60 + sm;
                const endMins = eh * 60 + em;
                const totalSlots = Math.floor((endMins - startMins) / this.slotMinutes);
                const available = [];
                for (let i = 0; i < totalSlots; i++) {
                    const slotStartMins = startMins + i * this.slotMinutes;
                    const slotStart = new Date(dateKey + 'T' + String(Math.floor(slotStartMins/60)).padStart(2,'0') + ':' + String(slotStartMins%60).padStart(2,'0'));
                    const slotEnd = new Date(slotStart.getTime() + this.slotMinutes * 60000);
                    let occupied = false;
                    for (const a of appts) {
                        const aStart = new Date(a.starts_at);
                        const aEnd = a.ends_at ? new Date(a.ends_at) : new Date(aStart.getTime() + this.slotMinutes * 60000);
                        if (slotStart < aEnd && slotEnd > aStart) { occupied = true; break; }
                    }
                    if (!occupied) available.push(String(slotStart.getHours()).padStart(2,'0') + ':' + String(slotStart.getMinutes()).padStart(2,'0'));
                }
                return available;
            },
            openDay(dateKey, dayNum) {
                if (this.requirePhysicianForDayModal && !this.selectedPhysicianId) {
                    this.showSelectPhysicianHint = true;
                    setTimeout(() => { this.showSelectPhysicianHint = false; }, 4000);
                    return;
                }
                this.dayModalDate = dateKey;
                this.dayModalAppointments = this.getFilteredForDay(dateKey);
                this.showDayModal = true;
            },
            openAddSlot(dateKey, slotTime) {
                this.addForm.starts_at = dateKey + 'T' + slotTime;
                const [h, m] = slotTime.split(':').map(Number);
                const endMins = h * 60 + m + this.slotMinutes;
                this.addForm.ends_at = dateKey + 'T' + String(Math.floor(endMins/60)).padStart(2,'0') + ':' + String(endMins%60).padStart(2,'0');
                if (this.selectedPhysicianId) this.addForm.physician_id = this.selectedPhysicianId;
                else if (this.physicians.length === 1) this.addForm.physician_id = this.physicians[0].id;
                if (this.selectedPatientId) this.addForm.patient_id = this.selectedPatientId;
                this.showAddModal = true;
                this.closeDay();
            },
            openEdit(data) {
                this.showEditModal = true;
                this.form = {
                    id: data.id,
                    patient_name: data.patient_name || '',
                    title: data.title || '',
                    starts_at: data.starts_at || '',
                    ends_at: data.ends_at || '',
                    status: data.status || 'scheduled',
                    notes: data.notes || '',
                    edit_url: data.edit_url,
                    delete_url: data.delete_url,
                };
            },
            closeDay() { this.showDayModal = false; },
            closeEdit() { this.showEditModal = false; },
            closeAdd() { this.showAddModal = false; },
        }"
    >
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">
                    {{ __('Appointments calendar') }}
                </h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $carbonStart->translatedFormat('F Y') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($isStaff)
                    <div class="flex flex-col gap-1" x-effect="if (selectedPhysicianId) showSelectPhysicianHint = false">
                        <div class="flex items-center gap-2">
                            {{-- Physician searchable select --}}
                            <div class="relative" x-effect="if (!physicianSelectOpen) physicianSearchQuery = ''" @click.outside="physicianSelectOpen = false">
                                <label class="sr-only">{{ __('Physician') }}</label>
                                <button type="button" x-on:click="physicianSelectOpen = !physicianSelectOpen" class="inline-flex h-10 min-w-[140px] items-center justify-between gap-2 rounded-lg border border-zinc-200 border-b-zinc-300/80 bg-white px-3 py-2 text-left text-sm shadow-xs dark:border-white/10 dark:bg-white/10 dark:text-zinc-300" :aria-expanded="physicianSelectOpen">
                                    <span x-text="getSelectedPhysicianLabel() || '{{ addslashes(__('All physicians')) }}'" :class="!getSelectedPhysicianLabel() ? 'text-zinc-400 dark:text-zinc-500' : ''"></span>
                                    <svg class="h-4 w-4 shrink-0 text-zinc-400 transition-transform" :class="{ 'rotate-180': physicianSelectOpen }" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div x-show="physicianSelectOpen" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                    <div class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                                        <input type="text" x-model="physicianSearchQuery" x-on:click.stop placeholder="{{ __('Search...') }}" class="w-full rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm placeholder-zinc-400 focus:border-[var(--color-portal-blue)] focus:outline-none focus:ring-1 focus:ring-[var(--color-portal-blue)] dark:border-zinc-600 dark:bg-zinc-800 dark:placeholder-zinc-500"/>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto py-1">
                                        <button type="button" x-on:click="selectedPhysicianId = ''; physicianSelectOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">{{ __('All physicians') }}</button>
                                        <template x-for="p in filteredPhysicians" :key="p.id">
                                            <button type="button" x-on:click="selectedPhysicianId = String(p.id); physicianSelectOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" x-text="p.name"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            {{-- Patient searchable select --}}
                            <div class="relative" x-effect="if (!patientSelectOpen) patientSearchQuery = ''" @click.outside="patientSelectOpen = false">
                                <label class="sr-only">{{ __('Patient') }}</label>
                                <button type="button" x-on:click="patientSelectOpen = !patientSelectOpen" class="inline-flex h-10 min-w-[140px] items-center justify-between gap-2 rounded-lg border border-zinc-200 border-b-zinc-300/80 bg-white px-3 py-2 text-left text-sm shadow-xs dark:border-white/10 dark:bg-white/10 dark:text-zinc-300" :aria-expanded="patientSelectOpen">
                                    <span x-text="getSelectedPatientLabel() || '{{ addslashes(__('All patients')) }}'" :class="!getSelectedPatientLabel() ? 'text-zinc-400 dark:text-zinc-500' : ''"></span>
                                    <svg class="h-4 w-4 shrink-0 text-zinc-400 transition-transform" :class="{ 'rotate-180': patientSelectOpen }" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <div x-show="patientSelectOpen" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                                    <div class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                                        <input type="text" x-model="patientSearchQuery" x-on:click.stop placeholder="{{ __('Search...') }}" class="w-full rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm placeholder-zinc-400 focus:border-[var(--color-portal-blue)] focus:outline-none focus:ring-1 focus:ring-[var(--color-portal-blue)] dark:border-zinc-600 dark:bg-zinc-800 dark:placeholder-zinc-500"/>
                                    </div>
                                    <div class="max-h-48 overflow-y-auto py-1">
                                        <button type="button" x-on:click="selectedPatientId = ''; patientSelectOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">{{ __('All patients') }}</button>
                                        <template x-for="p in filteredPatients" :key="p.id">
                                            <button type="button" x-on:click="selectedPatientId = String(p.id); patientSelectOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" x-text="p.name"></button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p
                            x-show="showSelectPhysicianHint"
                            x-transition
                            class="text-sm text-amber-600 dark:text-amber-400"
                            x-cloak
                        >
                            {{ __('Select a physician to view a day') }}
                        </p>
                    </div>
                @endif
                @if ($isPhysician && !$isStaff)
                    <div class="relative flex items-center gap-2" x-effect="if (!patientSelectOpen) patientSearchQuery = ''" @click.outside="patientSelectOpen = false">
                        <label class="sr-only">{{ __('Patient') }}</label>
                        <button type="button" x-on:click="patientSelectOpen = !patientSelectOpen" class="inline-flex h-10 min-w-[140px] items-center justify-between gap-2 rounded-lg border border-zinc-200 border-b-zinc-300/80 bg-white px-3 py-2 text-left text-sm shadow-xs dark:border-white/10 dark:bg-white/10 dark:text-zinc-300" :aria-expanded="patientSelectOpen">
                            <span x-text="getSelectedPatientLabel() || '{{ addslashes(__('All patients')) }}'" :class="!getSelectedPatientLabel() ? 'text-zinc-400 dark:text-zinc-500' : ''"></span>
                            <svg class="h-4 w-4 shrink-0 text-zinc-400 transition-transform" :class="{ 'rotate-180': patientSelectOpen }" viewBox="0 0 20 20" fill="none"><path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <div x-show="patientSelectOpen" x-transition x-cloak class="absolute left-0 right-0 top-full z-50 mt-1 max-h-60 overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                            <div class="border-b border-zinc-200 p-2 dark:border-zinc-700">
                                <input type="text" x-model="patientSearchQuery" x-on:click.stop placeholder="{{ __('Search...') }}" class="w-full rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-sm placeholder-zinc-400 focus:border-[var(--color-portal-blue)] focus:outline-none focus:ring-1 focus:ring-[var(--color-portal-blue)] dark:border-zinc-600 dark:bg-zinc-800 dark:placeholder-zinc-500"/>
                            </div>
                            <div class="max-h-48 overflow-y-auto py-1">
                                <button type="button" x-on:click="selectedPatientId = ''; patientSelectOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">{{ __('All patients') }}</button>
                                <template x-for="p in filteredPatients" :key="p.id">
                                    <button type="button" x-on:click="selectedPatientId = String(p.id); patientSelectOpen = false" class="w-full px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800" x-text="p.name"></button>
                                </template>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-1">
                    <flux:link
                        :href="route('appointments.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year])"
                        variant="ghost"
                        class="inline-flex items-center justify-center px-2"
                        wire:navigate
                    >
                        <span class="sr-only">{{ __('Previous month') }}</span>
                        <svg class="h-4 w-4 text-zinc-600 dark:text-zinc-300" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </flux:link>
                    <flux:link
                        :href="route('appointments.calendar', ['month' => now()->month, 'year' => now()->year])"
                        variant="ghost"
                        class="px-2"
                        wire:navigate
                    >
                        {{ __('Today') }}
                    </flux:link>
                    <flux:link
                        :href="route('appointments.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year])"
                        variant="ghost"
                        class="inline-flex items-center justify-center px-2"
                        wire:navigate
                    >
                        <span class="sr-only">{{ __('Next month') }}</span>
                        <svg class="h-4 w-4 text-zinc-600 dark:text-zinc-300" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M7.5 5L12.5 10L7.5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </flux:link>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-lg bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/20 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="grid grid-cols-7 text-center text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                    <div>{{ __('Mon') }}</div>
                    <div>{{ __('Tue') }}</div>
                    <div>{{ __('Wed') }}</div>
                    <div>{{ __('Thu') }}</div>
                    <div>{{ __('Fri') }}</div>
                    <div>{{ __('Sat') }}</div>
                    <div>{{ __('Sun') }}</div>
                </div>

                @php
                    $day = 1;
                    $startWeekOffset = $carbonStart->copy()->startOfWeek();
                @endphp

                <div class="mt-2 grid grid-cols-7 gap-1 text-sm">
                    {{-- Leading empty days --}}
                    @for ($blank = 0; $blank < ($carbonStart->dayOfWeekIso - 1); $blank++)
                        <div class="h-24 rounded-lg bg-transparent"></div>
                    @endfor

                    {{-- Days in month --}}
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = $carbonStart->copy()->day($day);
                            $dateKey = $date->toDateString();
                            $isToday = $dateKey === $currentDate;
                        @endphp
                        <button
                            type="button"
                            class="flex h-24 flex-col rounded-lg border border-zinc-200 bg-zinc-50 p-1 text-left text-xs transition-colors hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900/70 dark:hover:bg-zinc-800"
                            :class="{ 'opacity-60 hover:opacity-80': isDayFullyBooked('{{ $dateKey }}') }"
                            x-on:click="openDay('{{ $dateKey }}', {{ $day }})"
                        >
                            <div class="mb-1 flex items-center justify-between">
                                <span class="font-medium {{ $isToday ? 'text-[var(--color-portal-blue)] dark:text-[var(--color-portal-green)]' : 'text-zinc-700 dark:text-zinc-200' }}">
                                    {{ $day }}
                                </span>
                                @if ($isToday)
                                    <span class="rounded-full bg-[var(--color-portal-blue)] px-1.5 py-0.5 text-[10px] font-medium text-white dark:bg-[var(--color-portal-green)] dark:text-zinc-900">
                                        {{ __('Today') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex-1 flex flex-col items-start justify-end gap-0.5">
                                @php $slots = $slotsByDay[$dateKey] ?? ['total' => 0, 'taken' => 0, 'available' => 0]; @endphp
                                <span
                                    class="text-[11px] text-zinc-600 dark:text-zinc-400"
                                    x-text="getFilteredCount('{{ $dateKey }}') === 0 ? '' : (getFilteredCount('{{ $dateKey }}') === 1 ? '{{ __('1 appointment') }}' : getFilteredCount('{{ $dateKey }}') + ' {{ __('appointments') }}')"
                                ></span>
                                <span class="text-[10px] text-zinc-500 dark:text-zinc-500">
                                    @if ($slots['total'] > 0)
                                        {{ $slots['taken'] }} {{ __('taken') }} · {{ $slots['available'] }} {{ __('available') }}
                                    @else
                                        {{ !empty($slots['label']) ? $slots['label'] : __('Closed') }}
                                    @endif
                                </span>
                            </div>
                        </button>
                    @endfor
                </div>
        </div>

        {{-- Day view modal: appointments + available slots --}}
        <div
            x-cloak
            x-show="showDayModal"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            x-on:keydown.escape.window="closeDay()"
        >
            <div
                x-on:click.outside="closeDay()"
                class="w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        <span x-text="dayModalDate ? new Date(dayModalDate + 'T12:00:00').toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) : ''"></span>
                    </h2>
                    <button type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" x-on:click="closeDay()">
                        <span class="sr-only">{{ __('Close') }}</span>
                        &times;
                    </button>
                </div>

                <div class="mt-4 max-h-80 overflow-y-auto space-y-4">
                    <div x-show="dayModalAppointments.length > 0">
                        <h3 class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Appointments') }}</h3>
                        <div class="mt-2 space-y-2">
                            <template x-for="appt in dayModalAppointments" :key="appt.id">
                                <button
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-lg border border-zinc-200 bg-zinc-50 px-3 py-2 text-left text-sm hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:hover:bg-zinc-700"
                                    x-on:click="openEdit(appt); closeDay();"
                                >
                                    <div>
                                        <span class="font-medium" x-text="appt.time"></span>
                                        <span class="text-zinc-600 dark:text-zinc-400" x-show="appt.patient_name" x-text="' · ' + appt.patient_name"></span>
                                        @if ($isStaff)
                                            <span class="block text-xs text-zinc-500 dark:text-zinc-400" x-show="appt.physician_name" x-text="appt.physician_name"></span>
                                        @endif
                                    </div>
                                    <span class="text-xs text-zinc-500" x-text="appt.title || ''"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div x-show="canAdd && dayModalDate && getAvailableSlots(dayModalDate).length > 0">
                        <h3 class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Available slots') }}</h3>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Click a slot to add an appointment') }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <template x-for="slot in getAvailableSlots(dayModalDate)" :key="slot">
                                <button
                                    type="button"
                                    class="rounded-lg border border-dashed border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 hover:border-[var(--color-portal-blue)] hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200 dark:hover:border-[var(--color-portal-green)] dark:hover:bg-zinc-700"
                                    x-on:click="openAddSlot(dayModalDate, slot)"
                                >
                                    <span x-text="slot"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    <p class="text-sm text-zinc-500 dark:text-zinc-400" x-show="dayModalAppointments.length === 0 && (!canAdd || getAvailableSlots(dayModalDate).length === 0)" x-cloak>
                        {{ __('No appointments this day') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Add appointment modal --}}
        <div
            x-cloak
            x-show="showAddModal"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            x-on:keydown.escape.window="closeAdd()"
        >
            <div
                x-on:click.outside="closeAdd()"
                class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                        {{ __('Add appointment') }}
                    </h2>
                    <button type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" x-on:click="closeAdd()">
                        <span class="sr-only">{{ __('Close') }}</span>
                        &times;
                    </button>
                </div>
                <form :action="storeUrl" method="POST" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="starts_at" :value="addForm.starts_at">
                    <input type="hidden" name="ends_at" :value="addForm.ends_at">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Patient') }}</label>
                        <select name="patient_id" class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" x-model="addForm.patient_id">
                            <option value="">{{ __('No patient') }}</option>
                            <template x-for="p in patients" :key="p.id">
                                <option :value="String(p.id)" x-text="p.name"></option>
                            </template>
                        </select>
                    </div>
                    @if ($isStaff)
                        <div>
                            <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Physician') }}</label>
                            <select name="physician_id" class="mt-1 block w-full rounded-lg border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" x-model="addForm.physician_id" required>
                                <option value="">{{ __('Select physician') }}</option>
                                <template x-for="p in physicians" :key="p.id">
                                    <option :value="String(p.id)" x-text="p.name"></option>
                                </template>
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="physician_id" :value="addForm.physician_id">
                    @endif
                    <flux:input
                        name="title"
                        :label="__('Title')"
                        placeholder="{{ __('Checkup, follow-up, etc.') }}"
                    />
                    <flux:textarea
                        name="notes"
                        :label="__('Notes')"
                        rows="2"
                    ></flux:textarea>
                    <div class="flex justify-end gap-2">
                        <flux:button type="button" variant="ghost" x-on:click="closeAdd()">
                            {{ __('Cancel') }}
                        </flux:button>
                        <flux:button type="submit" variant="primary">
                            {{ __('Add appointment') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit / delete appointment modal --}}
        <div
            x-cloak
            x-show="showEditModal"
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
            x-on:keydown.escape.window="closeEdit()"
        >
            <div
                x-on:click.outside="closeEdit()"
                class="w-full max-w-lg rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ __('Edit appointment') }}
                        </h2>
                        <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400" x-show="form.patient_name">
                            <span class="font-medium" x-text="form.patient_name"></span>
                        </p>
                    </div>
                    <button type="button" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200" x-on:click="closeEdit()">
                        <span class="sr-only">{{ __('Close') }}</span>
                        &times;
                    </button>
                </div>

                <form :action="form.edit_url" method="POST" class="mt-4 space-y-3">
                    @csrf
                    @method('PUT')

                    <flux:input
                        name="title"
                        :label="__('Title')"
                        x-model="form.title"
                    />

                    <div class="grid gap-3 sm:grid-cols-2">
                        <flux:input
                            name="starts_at"
                            type="datetime-local"
                            :label="__('Starts at')"
                            required
                            x-model="form.starts_at"
                        />
                        <flux:input
                            name="ends_at"
                            type="datetime-local"
                            :label="__('Ends at')"
                            x-model="form.ends_at"
                        />
                    </div>

                    <flux:select
                        name="status"
                        :label="__('Status')"
                        x-model="form.status"
                    >
                        <option value="scheduled">{{ __('Scheduled') }}</option>
                        <option value="completed">{{ __('Completed') }}</option>
                        <option value="canceled">{{ __('Canceled') }}</option>
                    </flux:select>

                    <flux:textarea
                        name="notes"
                        :label="__('Notes')"
                        rows="3"
                        x-model="form.notes"
                    ></flux:textarea>

                    <div class="mt-4 flex items-center justify-between gap-3">
                        <form :action="form.delete_url" method="POST" onsubmit="return confirm('{{ __('Delete this appointment?') }}');">
                            @csrf
                            @method('DELETE')
                            <flux:button type="submit" variant="danger">
                                {{ __('Delete') }}
                            </flux:button>
                        </form>

                        <div class="ml-auto flex gap-2">
                            <flux:button type="button" variant="ghost" x-on:click="closeEdit()">
                                {{ __('Cancel') }}
                            </flux:button>
                            <flux:button type="submit" variant="primary">
                                {{ __('Save changes') }}
                            </flux:button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts::app>

