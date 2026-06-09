<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserProfile extends Component
{
    public string $name = '';
    public string $email = '';
    public string $currentPassword = '';
    public string $newPassword = '';
    public string $newPasswordConfirmation = '';
    public string $avatarColor = '';
    public bool $saved = false;

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->avatarColor = $user->avatar_color;
    }

    public function saveProfile(): void
    {
        $user = Auth::user();
        $this->validate([
            'name'  => 'required|string|max:100',
            'email' => "required|email|unique:users,email,{$user->id}",
            'avatarColor' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $user->update(['name' => $this->name, 'email' => $this->email, 'avatar_color' => $this->avatarColor]);
        $this->saved = true;
    }

    public function changePassword(): void
    {
        $this->validate([
            'currentPassword'          => 'required',
            'newPassword'              => 'required|min:8|same:newPasswordConfirmation',
        ]);

        $user = Auth::user();
        if (!Hash::check($this->currentPassword, $user->password)) {
            $this->addError('currentPassword', 'Current password is incorrect.');
            return;
        }

        $user->update(['password' => Hash::make($this->newPassword)]);
        $this->reset(['currentPassword', 'newPassword', 'newPasswordConfirmation']);
        $this->saved = true;
    }

    public function render()
    {
        return view('livewire.profile.user-profile')
            ->layout('layouts.app', ['title' => 'Profile']);
    }
}
