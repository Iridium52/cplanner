<?php

namespace App\Livewire\Auth;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSetup extends Component
{
    public string $qrCode = '';
    public string $secret = '';
    public string $code = '';
    public ?string $error = null;
    public bool $confirmed = false;
    public array $recoveryCodes = [];

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->hasTwoFactorEnabled()) {
            $this->redirectRoute('dashboard');
            return;
        }

        // Generate secret if not already started
        if (!$user->two_factor_secret) {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            $user->update(['two_factor_secret' => encrypt($secret)]);
        }

        $this->secret = decrypt($user->two_factor_secret);
        $this->qrCode = $this->generateQrCode($user);
    }

    public function confirmSetup(): void
    {
        $this->error = null;
        /** @var User $user */
        $user = Auth::user();
        $google2fa = new Google2FA();

        $valid = $google2fa->verifyKey($this->secret, $this->code, 2);

        if (!$valid) {
            $this->error = 'Invalid code. Please try again.';
            $this->code = '';
            return;
        }

        $recoveryCodes = $this->generateRecoveryCodes();
        $user->update([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        ]);

        $this->recoveryCodes = $recoveryCodes;
        $this->confirmed = true;
    }

    public function finishSetup(): void
    {
        session(['two_factor_verified' => true]);
        $this->redirectRoute('dashboard', navigate: true);
    }

    private function generateQrCode(User $user): string
    {
        $google2fa = new Google2FA();
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $this->secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        return $writer->writeString($qrCodeUrl);
    }

    private function generateRecoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn() => strtoupper(bin2hex(random_bytes(5))) . '-' . strtoupper(bin2hex(random_bytes(5))))
            ->toArray();
    }

    public function render()
    {
        return view('livewire.auth.two-factor-setup')
            ->layout('layouts.guest');
    }
}
