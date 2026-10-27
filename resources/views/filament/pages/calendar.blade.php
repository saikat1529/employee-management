{{-- Livewire needs a single root element, so the style block sits inside the wrapper div below. --}}
<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <style>
        /* Calendar status colours. Absent (cal-tone-none) has no background. */
        .cal-tone-green { background: #059669; color: #ffffff; }
        .cal-tone-red { background: #dc2626; color: #ffffff; }
        .cal-tone-ash { background: #cbd5e1; color: #1e293b; }
    </style>

    {{-- Page heading. Drawn here so it changes with the selected employee. --}}
    <x-filament-panels::header :heading="$this->getTitle()" />

    {{-- Employee picker: one choice at a time. "All employees" is the default. --}}
    <x-filament::section>
        <div style="max-width: 24rem;">
            <label for="calendar-employee" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">Employee</label>
            <x-filament::input.wrapper wire:target="employeeId">
                <x-filament::input.select id="calendar-employee" wire:model.live="employeeId">
                    @foreach ($employeeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-filament::input.select>
            </x-filament::input.wrapper>
        </div>
    </x-filament::section>

    <x-filament::section>
        {{-- Month header: Previous, month name, Next --}}
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
            <x-filament::button color="gray" wire:click="previousMonth">Previous</x-filament::button>
            <h2 style="font-size: 1.25rem; font-weight: 600; margin: 0;">{{ $monthLabel }}</h2>
            <x-filament::button color="gray" wire:click="nextMonth">Next</x-filament::button>
        </div>

        {{-- The grid scrolls sideways inside this box on small screens; the page itself does not. --}}
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch; max-width: 100%;">
            <div style="min-width: 640px;">
                <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; margin-bottom: 6px; font-size: 0.75rem; font-weight: 600; text-align: center; opacity: 0.7;">
                    @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                        <div>{{ $dayName }}</div>
                    @endforeach
                </div>

                @foreach ($weeks as $week)
                    <div style="display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 6px; margin-bottom: 6px;">
                        @foreach ($week as $cell)
                            <div style="min-height: 96px; padding: 6px; border: 1px solid rgba(128, 128, 128, 0.3); border-radius: 8px; {{ $cell['inMonth'] ? '' : 'opacity: 0.3;' }}">
                                <div style="font-weight: 600; font-size: 0.875rem;">{{ $cell['day'] }}</div>

                                @if ($cell['inMonth'])
                                    @if ($isSingle)
                                        {{-- Single employee: time text, coloured by status. Absent has no colour. --}}
                                        @foreach ($cell['entries'] as $entry)
                                            <div class="cal-tone cal-tone-{{ $entry['tone'] !== '' ? $entry['tone'] : 'none' }}" style="margin-top: 4px; padding: 3px 4px; border-radius: 4px; font-size: 0.7rem; line-height: 1.35;">{{ $entry['text'] }}</div>
                                        @endforeach
                                    @else
                                        {{-- All employees: count badges, only for counts above zero. --}}
                                        @foreach ($cell['badges'] as $badge)
                                            <div class="cal-tone cal-tone-{{ $badge['tone'] }}" style="margin-top: 4px; padding: 2px 4px; border-radius: 4px; font-size: 0.7rem; line-height: 1.35;">{{ $badge['label'] }}</div>
                                        @endforeach
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Colour legend: matches the view that is showing --}}
        @if ($isSingle)
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; font-size: 0.875rem;">
                <span><span class="cal-tone-green" style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 3px; vertical-align: middle;"></span> Present (green)</span>
                <span><span class="cal-tone-red" style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 3px; vertical-align: middle;"></span> Late (red)</span>
                <span><span class="cal-tone-ash" style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 3px; vertical-align: middle;"></span> On Leave / Half Day (ash)</span>
                <span>Absent (plain text)</span>
            </div>
        @else
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; font-size: 0.875rem;">
                <span><span class="cal-tone-green" style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 3px; vertical-align: middle;"></span> Attendance (Present)</span>
                <span><span class="cal-tone-red" style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 3px; vertical-align: middle;"></span> Late</span>
                <span><span class="cal-tone-ash" style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 3px; vertical-align: middle;"></span> Other (Absent, On Leave, Half Day)</span>
            </div>
        @endif
    </x-filament::section>
</div>
