<?php

namespace App\Livewire\Auth;

use App\Mail\EmailOtpMail;
use App\Models\EmailOtpToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallenge extends Component
{
    public string $code = '';
    public ?string $error = null;
    public bool $useEmailOtp = false;
    public bool $otpSent = false;
    public string $recoveryCode = '';
    public bool $useRecovery = false;

    public function verify(): void
    {
        $this->error = null;
        $user = Auth::user();

        if ($this->useRecovery) {
            $this->verifyRecoveryCode($user);
            return;
        }

        if ($this->useEmailOtp) {
            $this->verifyEmailOtp($user);
            return;
        }

        // TOTP verification
        $google2fa = new Google2FA();
        $secret = decrypt($user->two_factor_secret);
        $valid = $google2fa->verifyKey($secret, $this->code, 2);

        if (!$valid) {
            $this->error = 'Invalid authentication code.';
            $this->code = '';
            return;
        }

        $this->markVerified();
    }

    public function sendEmailOtp(): void
    {
        $user = Auth::user();

        // Delete old tokens
        EmailOtpToken::where('user_id', $user->id)->delete();

        $token = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        EmailOtpToken::create([
            'user_id'    => $user->id,
            'token'      => $token,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user)->send(new EmailOtpMail($token));

        $this->useEmailOtp = true;
        $this->otpSent = true;
        $this->code = '';
    }

    private function verifyEmailOtp($user): void
    {
        $otpRecord = EmailOtpToken::where('user_id', $user->id)
            ->where('token', $this->code)
            ->first();

        if (!$otpRecord || $otpRecord->isExpired()) {
            $this->error = 'Invalid or expired code.';
            $this->code = '';
            return;
        }

        $otpRecord->delete();
        $this->markVerified();
    }

    private function verifyRecoveryCode($user): void
    {
        $codes = json_decode(decrypt($user->two_factor_recovery_codes), true);
        $index = array_search($this->recoveryCode, $codes);

        if ($index === false) {
            $this->error = 'Invalid recovery code.';
            return;
        }

        // Remove used recovery code
        unset($codes[$index]);
        $user->update([
            'two_factor_recovery_codes' => encrypt(json_encode(array_values($codes))),
        ]);

        $this->markVerified();
    }

    private function markVerified(): void
    {
        session(['two_factor_verified' => true]);
        $this->redirectIntended(default: route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.two-factor-challenge')
            ->layout('layouts.guest');
    }
}
