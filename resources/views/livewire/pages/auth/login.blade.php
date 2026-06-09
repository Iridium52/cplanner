<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        // After login, RequiresTwoFactor middleware will redirect to 2FA
        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-2xl">
    <h2 class="text-lg font-semibold text-white mb-6">Sign in to C Planner</h2>

    <x-auth-session-status class="mb-4 text-sm text-green-400 bg-green-500/10 border border-green-500/20 rounded-lg px-3 py-2" :status="session('status')" />

    <form wire:submit="login" class="space-y-4">
        <div>
            <label for="email" class="block text-sm text-gray-400 mb-1.5">Email</label>
            <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username"
                   class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600">
            <x-input-error :messages="$errors->get('form.email')" class="mt-1 text-xs text-red-400" />
        </div>

        <div>
            <label for="password" class="block text-sm text-gray-400 mb-1.5">Password</label>
            <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password"
                   class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <x-input-error :messages="$errors->get('form.password')" class="mt-1 text-xs text-red-400" />
        </div>

        <div class="flex items-center justify-between">
            <label class="flex items-center gap-2 cursor-pointer">
                <input wire:model="form.remember" type="checkbox"
                       class="rounded bg-gray-800 border-gray-600 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-gray-400">Remember me</span>
            </label>
            @if(Route::has('password.request'))
            <a href="{{ route('password.request') }}" wire:navigate
               class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">
                Forgot password?
            </a>
            @endif
        </div>

        <button type="submit" wire:loading.attr="disabled"
                class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-lg transition-colors text-sm">
            <span wire:loading.remove>Sign in</span>
            <span wire:loading>Signing in...</span>
        </button>
    </form>
</div>
