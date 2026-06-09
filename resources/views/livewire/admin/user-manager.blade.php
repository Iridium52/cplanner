<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-xl font-semibold text-white">Users</h1>
        <button wire:click="openCreate"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add User
        </button>
    </div>

    {{-- User form --}}
    @if($showForm)
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-6">
        <h3 class="text-sm font-semibold text-white mb-4">{{ $editingId ? 'Edit User' : 'New User' }}</h3>
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
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Password {{ $editingId ? '(leave blank to keep)' : '' }}</label>
                <input wire:model="password" type="password"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Role</label>
                <select wire:model="role"
                        class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                    <option value="viewer">Viewer</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-800">
            <button wire:click="$set('showForm', false)" class="text-sm text-gray-400 hover:text-gray-200 px-4 py-2 transition-colors">Cancel</button>
            <button wire:click="save" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                {{ $editingId ? 'Save Changes' : 'Create User' }}
            </button>
        </div>
    </div>
    @endif

    {{-- Users table --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="border-b border-gray-800">
                <tr>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">2FA</th>
                    <th class="text-left px-5 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-5 py-3 w-24"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @foreach($users as $user)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold text-white flex-shrink-0"
                                 style="background-color: {{ $user->avatar_color }}">
                                {{ $user->initials() }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-200">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $user->role === 'admin' ? 'bg-indigo-500/20 text-indigo-300' : 'bg-gray-700 text-gray-400' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($user->hasTwoFactorEnabled())
                        <span class="text-xs text-green-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Enabled
                        </span>
                        @else
                        <span class="text-xs text-gray-600">Not set up</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-500">{{ $user->created_at->format('M j, Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2 justify-end">
                            <button wire:click="openEdit({{ $user->id }})" class="text-gray-500 hover:text-indigo-400 text-xs transition-colors">Edit</button>
                            @if($user->hasTwoFactorEnabled() && $user->id !== auth()->id())
                            <button wire:click="resetTwoFactor({{ $user->id }})"
                                    wire:confirm="Reset 2FA for this user? They will need to set it up again."
                                    class="text-gray-600 hover:text-yellow-400 text-xs transition-colors">Reset 2FA</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
