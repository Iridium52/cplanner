<?php

namespace App\Livewire\Profile;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    public function revokeSession(string $sessionId): void
    {
        DB::table('sessions')
            ->where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->where('id', '!=', session()->getId())
            ->delete();
    }

    public function revokeAllOtherSessions(): void
    {
        DB::table('sessions')
            ->where('user_id', Auth::id())
            ->where('id', '!=', session()->getId())
            ->delete();
        $this->saved = true;
    }

    public function render()
    {
        $sessions = DB::table('sessions')
            ->where('user_id', Auth::id())
            ->orderByDesc('last_activity')
            ->get()
            ->map(function ($s) {
                $ua = $s->user_agent ?? '';
                return (object) [
                    'id'            => $s->id,
                    'ip'            => $s->ip_address ?? 'Unknown',
                    'browser'       => $this->parseBrowser($ua),
                    'os'            => $this->parseOs($ua),
                    'last_activity' => Carbon::createFromTimestamp($s->last_activity)->diffForHumans(),
                    'is_current'    => $s->id === session()->getId(),
                ];
            });

        return view('livewire.profile.user-profile', ['sessions' => $sessions])
            ->layout('layouts.app', ['title' => 'Profile']);
    }

    private function parseBrowser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/')     => 'Edge',
            str_contains($ua, 'Chrome/')  => 'Chrome',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Safari/') && !str_contains($ua, 'Chrome') => 'Safari',
            default => 'Unknown browser',
        };
    }

    private function parseOs(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS')  => 'macOS',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux')   => 'Linux',
            default => 'Unknown OS',
        };
    }
}
