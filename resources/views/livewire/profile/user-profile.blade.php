<div class="p-6 max-w-2xl mx-auto space-y-6">
    <h1 class="text-xl font-semibold text-white">Profile & Security</h1>

    @if($saved)
    <div class="bg-green-500/10 border border-green-500/20 text-green-400 text-sm rounded-lg px-4 py-3"
         x-data x-init="setTimeout(() => $wire.set('saved', false), 3000)">
        Changes saved.
    </div>
    @endif

    {{-- Profile info --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Profile Information</h3>
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold text-white"
                 style="background-color: {{ $avatarColor }}">
                {{ auth()->user()->initials() }}
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Avatar Color</label>
                <input wire:model.live="avatarColor" type="color"
                       class="w-10 h-10 rounded-lg border border-gray-700 bg-gray-950 cursor-pointer p-1">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Name</label>
                <input wire:model="name" type="text"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Email</label>
                <input wire:model="email" type="email"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button wire:click="saveProfile" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">Save Profile</button>
        </div>
    </div>

    {{-- Change password --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-white mb-4">Change Password</h3>
        <div class="space-y-3">
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Current Password</label>
                <input wire:model="currentPassword" type="password"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                @error('currentPassword') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">New Password</label>
                <input wire:model="newPassword" type="password"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                @error('newPassword') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Confirm New Password</label>
                <input wire:model="newPasswordConfirmation" type="password"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button wire:click="changePassword" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">Update Password</button>
        </div>
    </div>

    {{-- 2FA status --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-white mb-2">Two-Factor Authentication</h3>
        @if(auth()->user()->hasTwoFactorEnabled())
        <div class="flex items-center gap-2 text-green-400 text-sm mb-3">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            2FA is enabled on your account
        </div>
        <p class="text-xs text-gray-500">Enabled {{ auth()->user()->two_factor_confirmed_at->diffForHumans() }}. Contact an admin to reset if you've lost access to your authenticator app.</p>
        @else
        <p class="text-gray-400 text-sm mb-3">2FA is not yet configured on your account.</p>
        <a href="{{ route('two-factor.setup') }}" class="text-indigo-400 hover:text-indigo-300 text-sm transition-colors">Set up 2FA →</a>
        @endif
    </div>
</div>
