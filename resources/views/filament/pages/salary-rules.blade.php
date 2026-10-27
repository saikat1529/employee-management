<div style="display: flex; flex-direction: column; gap: 1.5rem;">
    <x-filament-panels::header :heading="$this->getTitle()" />

    <x-filament::section>
        <div style="display: grid; gap: 1.25rem; max-width: 36rem;">
            <div>
                <label for="days-in-month" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">Days in month for daily rate</label>
                <x-filament::input id="days-in-month" type="number" min="1" max="31" step="1" wire:model="daysInMonth" />
                <p style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.25rem;">Daily rate = monthly salary / this number. Default 30.</p>
                @error('daysInMonth') <p style="color: #dc2626; font-size: 0.8rem;">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="late-per-absent" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">Late days per 1 absent day</label>
                <x-filament::input id="late-per-absent" type="number" min="1" max="31" step="1" wire:model="lateDaysPerAbsent" />
                <p style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.25rem;">Every this many Late days count as 1 Absent day. Default 3.</p>
                @error('lateDaysPerAbsent') <p style="color: #dc2626; font-size: 0.8rem;">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="half-day-fraction" style="display: block; font-weight: 600; margin-bottom: 0.375rem;">Half day deduction (fraction of a day)</label>
                <x-filament::input id="half-day-fraction" type="number" min="0" max="1" step="0.01" wire:model="halfDayFraction" />
                <p style="font-size: 0.8rem; opacity: 0.7; margin-top: 0.25rem;">Each Half Day deducts this part of a day. Default 0.5.</p>
                @error('halfDayFraction') <p style="color: #dc2626; font-size: 0.8rem;">{{ $message }}</p> @enderror
            </div>

            <div>
                <x-filament::button wire:click="save">Save</x-filament::button>
            </div>
        </div>
    </x-filament::section>
</div>
