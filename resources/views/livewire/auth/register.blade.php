<?php

use App\Livewire\Traits\ImageHandlerTrait;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {

    use ImageHandlerTrait;

    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $user_profile_image;

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'min:3', 'max:50'],
            'last_name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'user_profile_image' => 'nullable|image|max:2048',
        ]);

        $name = $validated['first_name'] . ' ' . $validated['last_name'];

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create([
            'name' => $name,
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]))));

        $this->saveImage($user, 'image', $this->user_profile_image, 'avatars/'.$user->id);

        Auth::login($user);

        $this->redirect(route('site.picker', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header title="Create an account" description="Enter your details below to create your account"/>

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')"/>

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- Name -->
        <div class="grid gap-2">
            <flux:input wire:model="first_name" min="3" id="first_name" label="{{ __('first_name') }}" type="text"
                        name="first_name" required autofocus autocomplete="first_name" placeholder="First name"/>
        </div>
        <div class="grid gap-2">
            <flux:input wire:model="last_name" id="last_name" label="{{ __('last_name') }}" type="text" name="last_name"
                        required autofocus autocomplete="last_name" placeholder="Last name"/>
        </div>

        <!-- Email Address -->
        <div class="grid gap-2">
            <flux:input wire:model="email" id="email" label="{{ __('Email address') }}" type="email" name="email"
                        required autocomplete="email" placeholder="email@example.com"/>
        </div>

        <!-- Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password"
                id="password"
                label="{{ __('Password') }}"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Password"
            />
        </div>

        <!-- Confirm Password -->
        <div class="grid gap-2">
            <flux:input
                wire:model="password_confirmation"
                id="password_confirmation"
                label="{{ __('Confirm password') }}"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Confirm password"
            />
        </div>
        <div class="grid gap-2">
            <flux:input type="file" wire:model="user_profile_image" label="{{__('Profile Avatar')}}"/>
        </div>

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 text-center text-sm text-zinc-600 dark:text-zinc-400">
        Already have an account?
        <flux:link href="{{ route('login') }}" wire:navigate>Log in</flux:link>
    </div>
</div>
