<x-filament-panels::page>
    <form wire:submit="save" class="max-w-xl space-y-6">
        <x-filament::section>
            <x-slot name="heading">Checkout environment</x-slot>
            <x-slot name="description">Test mode uses test keys and never charges real money. Live mode uses live keys and can accept real payments.</x-slot>
            <div class="space-y-4">
                <div>
                    <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3"><input wire:model="mode" type="radio" value="test"> <span>Test mode</span></label>
                    <label class="fi-fo-field-wrp-label ml-6 inline-flex items-center gap-x-3"><input wire:model="mode" type="radio" value="live"> <span>Live mode</span></label>
                </div>
                <div style="margin-top: 20px;">
                    <label for="payment-mode-password" style="display: block; margin-bottom: 8px; font-size: 14px; font-weight: 600; color: #111827;">Confirm with your administrator password</label>
                    <input
                        wire:model="password"
                        id="payment-mode-password"
                        type="password"
                        style="display: block !important; width: 100% !important; height: 44px !important; box-sizing: border-box !important; padding: 0 12px !important; border: 1px solid #9ca3af !important; border-radius: 8px !important; background: #ffffff !important; color: #111827 !important; font-size: 14px !important; outline: none !important;"
                        autocomplete="current-password"
                        placeholder="Enter your current password"
                    >
                    @error('password') <p class="mt-1 text-sm text-danger-600">{{ $message }}</p> @enderror
                </div>
                <br>
                

                <x-filament::button type="submit" color="danger">Save payment mode</x-filament::button>
            </div>
        </x-filament::section>
    </form>
</x-filament-panels::page>
