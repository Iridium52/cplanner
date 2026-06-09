<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UserManager extends Component
{
    public bool $showForm = false;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'viewer';
    public ?int $editingId = null;

    public function openCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'role', 'editingId']);
        $this->showForm = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $rules = [
            'name'  => 'required|string|max:100',
            'role'  => 'required|in:admin,viewer',
        ];

        if ($this->editingId) {
            $rules['email'] = "required|email|unique:users,email,{$this->editingId}";
            $rules['password'] = 'nullable|string|min:8';
        } else {
            $rules['email'] = 'required|email|unique:users,email';
            $rules['password'] = 'required|string|min:8';
        }

        $this->validate($rules);

        if ($this->editingId) {
            $data = ['name' => $this->name, 'email' => $this->email, 'role' => $this->role];
            if ($this->password) {
                $data['password'] = Hash::make($this->password);
            }
            User::findOrFail($this->editingId)->update($data);
        } else {
            User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => Hash::make($this->password),
                'role'     => $this->role,
            ]);
        }

        $this->showForm = false;
        $this->reset(['name', 'email', 'password', 'role', 'editingId']);
    }

    public function resetTwoFactor(int $userId): void
    {
        User::findOrFail($userId)->update([
            'two_factor_secret'        => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at'   => null,
        ]);
    }

    public function render()
    {
        return view('livewire.admin.user-manager', [
            'users' => User::all(),
        ])->layout('layouts.app', ['title' => 'Users']);
    }
}
