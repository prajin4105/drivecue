<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $first_name = '';
    public string $last_name = '';
    public string $center_name = '';
    public string $center_address = '';
    public string $mobile = '';
    public $profile_image = null;
    public ?string $current_profile_image = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->first_name = (string) $user->first_name;
        $this->last_name = (string) $user->last_name;
        $this->center_name = (string) $user->center_name;
        $this->center_address = (string) $user->center_address;
        $this->mobile = (string) $user->mobile;
        $this->current_profile_image = $user->profile_image;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'center_name' => ['required', 'string', 'max:180'],
            'center_address' => ['required', 'string', 'max:500'],
        ];

        if ($this->profile_image) {
            $rules['profile_image'] = ['image', 'max:2048']; // 2MB Max
        }

        $this->validate($rules);

        $data = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'center_name' => $this->center_name,
            'center_address' => $this->center_address,
        ];

        if ($this->profile_image) {
            $path = $this->profile_image->store('profile-photos', 'public');
            $data['profile_image'] = 'storage/' . $path;
            $this->current_profile_image = $data['profile_image'];
        }

        $user->update($data);

        $this->dispatch('profile-updated', name: $user->first_name);
        
        session()->flash('success', 'Profile updated successfully!');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile details and center settings.") }}
        </p>
    </header>

    @if (session()->has('success'))
        <div class="mt-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-6" enctype="multipart/form-data">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <x-input-label for="first_name" :value="__('First Name')" />
                <x-text-input wire:model="first_name" id="first_name" name="first_name" type="text" class="mt-1 block w-full" required autocomplete="given-name" />
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div>
                <x-input-label for="last_name" :value="__('Last Name')" />
                <x-text-input wire:model="last_name" id="last_name" name="last_name" type="text" class="mt-1 block w-full" required autocomplete="family-name" />
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>
        </div>

        <div>
            <x-input-label for="center_name" :value="__('Center Name')" />
            <x-text-input wire:model="center_name" id="center_name" name="center_name" type="text" class="mt-1 block w-full" required />
            <x-input-error class="mt-2" :messages="$errors->get('center_name')" />
        </div>

        <div>
            <x-input-label for="center_address" :value="__('Center Address')" />
            <textarea wire:model="center_address" id="center_address" name="center_address" required rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
            <x-input-error class="mt-2" :messages="$errors->get('center_address')" />
        </div>

        <div>
            <x-input-label for="mobile" :value="__('Mobile Number')" />
            <x-text-input wire:model="mobile" id="mobile" name="mobile" type="text" class="mt-1 block w-full bg-gray-50 text-gray-500 cursor-not-allowed" disabled />
            <span class="text-xs text-gray-400 mt-1 block">Mobile number is verified and cannot be changed.</span>
        </div>

        <div>
            <x-input-label :value="__('Profile Photo')" />
            <div class="mt-2 flex items-center gap-4">
                <div class="w-16 h-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center overflow-hidden">
                    @if ($profile_image)
                        <img src="{{ $profile_image->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($current_profile_image)
                        <img src="{{ asset($current_profile_image) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-2xl font-bold text-gray-400">{{ strtoupper(substr($first_name ?: 'C', 0, 1)) }}</span>
                    @endif
                </div>
                <div>
                    <input type="file" wire:model="profile_image" id="profile_photo" class="hidden" accept="image/*">
                    <label for="profile_photo" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 cursor-pointer transition ease-in-out duration-150">
                        {{ __('Choose Photo') }}
                    </label>
                    <div wire:loading wire:target="profile_image" class="text-xs text-gray-500 mt-1">Uploading...</div>
                    <x-input-error class="mt-2" :messages="$errors->get('profile_image')" />
                </div>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            <x-action-message class="me-3" on="profile-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
