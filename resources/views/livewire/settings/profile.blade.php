<?php

use App\Livewire\Traits\ImageHandlerTrait;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {

    use ImageHandlerTrait;

    public string $name = '';
    public string $email = '';

    #[Validate('required|image|mimes:jpg,png,jpeg,webp|max:5120')]
    public $profile_image;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function updatedProfileImage(): void
    {
        try {
            $this->validate();
        } catch (ValidationException $e) {
            $this->profile_image = null;
            Session::flash('status', __('interface.invalid_file_type'));
        }
    }

    public function saveProfileImage(): void
    {
        if (empty($this->profile_image)) return;
        if ($this->saveImage(auth()->user(), 'image', $this->profile_image, 'avatars/' . auth()->id(), use_db_transaction: true)) $this->dispatch('reloadPage');
        $this->profile_image = null;

    }


    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')
    <x-settings.layout heading="{{ __('Profile') }}" subheading="{{ __('Update your name and email address') }}">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" label="{{ __('Name') }}" type="text" name="name" required autofocus
                        autocomplete="name"/>

            <div>
                <flux:input wire:model="email" label="{{ __('Email') }}" type="email" name="email" required
                            autocomplete="email"/>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button
                                wire:click.prevent="resendVerificationNotification"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <div class="flex lg:flex-row flex-col my-10 gap-8">
            <img src="{{ $profile_image?->temporaryUrl() ?? route('view-file', ['path' => auth()->user()->image]) }}"
                 class="rounded-lg size-32" alt="{{auth()->user()->name}}">
            <flux:input type="file" wire:model="profile_image" label="{{__('Change Profile Image')}}"/>
        </div>
        <flux:button variant="primary" wire:click="saveProfileImage()">{{ __('Save') }}</flux:button>

        <livewire:settings.delete-user-form/>

    </x-settings.layout>
</section>
@script
<script>
    Livewire.on('reloadPage', () => {
        location.reload();
    });
</script>
@endscript
