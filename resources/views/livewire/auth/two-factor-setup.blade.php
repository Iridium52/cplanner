<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-2xl">
    @if($confirmed)
        {{-- Recovery codes display --}}
        <div class="text-center mb-6">
            <div class="w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-lg font-semibold text-white">2FA Enabled!</h2>
            <p class="text-gray-400 text-sm mt-1">Save these recovery codes somewhere safe. Each can be used once.</p>
        </div>

        <div class="bg-gray-950 border border-gray-700 rounded-lg p-4 mb-6 font-mono text-sm">
            @foreach($recoveryCodes as $rc)
                <div class="text-gray-300 py-0.5">{{ $rc }}</div>
            @endforeach
        </div>

        <button wire:click="finishSetup" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors">
            I've saved my codes — Continue
        </button>
    @else
        <h2 class="text-lg font-semibold text-white mb-1">Set Up Two-Factor Auth</h2>
        <p class="text-gray-400 text-sm mb-6">Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.), then enter the 6-digit code to confirm.</p>

        {{-- QR Code --}}
        <div class="flex justify-center mb-6 bg-white rounded-xl p-4">
            {!! $qrCode !!}
        </div>

        {{-- Manual entry --}}
        <div class="mb-6">
            <p class="text-xs text-gray-500 mb-1">Or enter this code manually:</p>
            <div class="font-mono text-sm bg-gray-950 border border-gray-700 rounded-lg px-3 py-2 text-indigo-300 tracking-widest">
                {{ wordwrap($secret, 4, ' ', true) }}
            </div>
        </div>

        {{-- Confirm code --}}
        <div class="space-y-4">
            @if($error)
                <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-lg px-3 py-2">{{ $error }}</div>
            @endif

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Verification Code</label>
                <input wire:model="code" wire:keydown.enter="confirmSetup"
                       type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                       class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-center text-xl tracking-[0.5em] font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       placeholder="000000" autofocus>
            </div>

            <button wire:click="confirmSetup" wire:loading.attr="disabled"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-lg transition-colors">
                <span wire:loading.remove>Confirm & Enable 2FA</span>
                <span wire:loading>Verifying...</span>
            </button>
        </div>
    @endif
</div>
