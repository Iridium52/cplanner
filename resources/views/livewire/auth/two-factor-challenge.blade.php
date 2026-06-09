<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-2xl">
    @if($useRecovery)
        <h2 class="text-lg font-semibold text-white mb-1">Recovery Code</h2>
        <p class="text-gray-400 text-sm mb-6">Enter one of your saved recovery codes.</p>

        @if($error)
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-lg px-3 py-2 mb-4">{{ $error }}</div>
        @endif

        <div class="space-y-4">
            <input wire:model="recoveryCode" type="text" autocomplete="off"
                   class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="XXXXX-XXXXX" autofocus>

            <button wire:click="verify" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-medium py-2.5 px-4 rounded-lg transition-colors">
                Verify Recovery Code
            </button>

            <button wire:click="$set('useRecovery', false)" class="w-full text-gray-500 hover:text-gray-300 text-sm py-1 transition-colors">
                ← Back to code entry
            </button>
        </div>
    @else
        <h2 class="text-lg font-semibold text-white mb-1">Two-Factor Authentication</h2>
        <p class="text-gray-400 text-sm mb-6">
            @if($useEmailOtp && $otpSent)
                A 6-digit code was sent to your email address.
            @else
                Enter the 6-digit code from your authenticator app.
            @endif
        </p>

        @if($error)
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 text-sm rounded-lg px-3 py-2 mb-4">{{ $error }}</div>
        @endif

        <div class="space-y-4">
            <input wire:model="code" wire:keydown.enter="verify"
                   type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6"
                   class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-center text-xl tracking-[0.5em] font-mono focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                   placeholder="000000" autofocus>

            <button wire:click="verify" wire:loading.attr="disabled"
                    class="w-full bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-lg transition-colors">
                <span wire:loading.remove>Verify</span>
                <span wire:loading>Verifying...</span>
            </button>
        </div>

        <div class="mt-4 space-y-2 border-t border-gray-800 pt-4">
            @if(!$useEmailOtp)
                <button wire:click="sendEmailOtp" wire:loading.attr="disabled"
                        class="w-full text-gray-500 hover:text-indigo-400 text-sm py-1 transition-colors text-left flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span wire:loading.remove wire:target="sendEmailOtp">Send code to my email instead</span>
                    <span wire:loading wire:target="sendEmailOtp">Sending...</span>
                </button>
            @else
                <button wire:click="sendEmailOtp" wire:loading.attr="disabled"
                        class="w-full text-gray-500 hover:text-indigo-400 text-sm py-1 transition-colors text-left">
                    Resend email code
                </button>
            @endif

            <button wire:click="$set('useRecovery', true)"
                    class="w-full text-gray-500 hover:text-gray-300 text-sm py-1 transition-colors text-left">
                Use a recovery code
            </button>
        </div>
    @endif
</div>
