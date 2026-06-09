<div class="p-6 max-w-3xl mx-auto">

    {{-- Reassign tasks modal --}}
    @if($showReassignModal)
    @php $deletingStatus = $project->statuses->firstWhere('id', $deletingStatusId); @endphp
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 w-full max-w-md shadow-2xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Reassign tasks before deleting</h3>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <span class="text-gray-300">{{ $deletingStatus?->name }}</span> has
                        {{ $deletingStatus?->tasks()->count() }} task(s). Move them to:
                    </p>
                </div>
            </div>

            <select wire:model="reassignToStatusId"
                    class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2.5 mb-4 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <option value="">— Pick a status —</option>
                @foreach($project->statuses->where('id', '!=', $deletingStatusId) as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </select>
            @error('reassignToStatusId') <p class="text-red-400 text-xs -mt-3 mb-3">{{ $message }}</p> @enderror

            <div class="flex gap-3">
                <button wire:click="confirmDeleteStatus"
                        class="flex-1 bg-red-600 hover:bg-red-500 text-white text-sm font-medium py-2 rounded-lg transition-colors">
                    Move tasks &amp; Delete status
                </button>
                <button wire:click="cancelDeleteStatus"
                        class="flex-1 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium py-2 rounded-lg transition-colors">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif
    <div class="mb-6">
        <a href="{{ route('projects.show', $project) }}" wire:navigate class="text-gray-500 hover:text-gray-300 text-sm flex items-center gap-1.5 mb-4">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            {{ $project->name }}
        </a>
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-white">Project Settings</h1>
            <a href="{{ route('projects.edit', $project) }}" wire:navigate
               class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">Edit project details →</a>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Statuses --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Workflow Statuses</h3>
            <div class="space-y-2 mb-4"
                 x-data="statusSorter(@this)"
                 x-sortable-list
                 @sortable-update="save">
                @foreach($project->statuses as $status)
                <div class="flex items-center gap-3 bg-gray-800 rounded-lg px-3 py-2"
                     data-id="{{ $status->id }}">
                    {{-- Drag handle --}}
                    <span class="text-gray-600 hover:text-gray-400 cursor-grab active:cursor-grabbing flex-shrink-0" x-sortable-handle>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8h16M4 16h16"/></svg>
                    </span>
                    <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $status->color }}"></div>
                    <span class="flex-1"
                          x-data="{ editing: false, name: '{{ addslashes($status->name) }}' }">
                        <span x-show="!editing"
                              @click="editing = true; $nextTick(() => $refs.input{{ $status->id }}.select())"
                              class="text-sm text-gray-200 cursor-text hover:text-white">
                            {{ $status->name }}
                        </span>
                        <input x-show="editing"
                               x-ref="input{{ $status->id }}"
                               x-model="name"
                               @keydown.enter="editing = false; $wire.renameStatus({{ $status->id }}, name)"
                               @keydown.escape="editing = false; name = '{{ addslashes($status->name) }}'"
                               @blur="editing = false; $wire.renameStatus({{ $status->id }}, name)"
                               type="text"
                               class="bg-gray-700 border border-indigo-500 text-white text-sm rounded px-2 py-0.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-400">
                    </span>
                    @if($status->is_done) <span class="text-xs text-green-400 bg-green-400/10 px-1.5 py-0.5 rounded">done</span> @endif
                    @if($status->is_default) <span class="text-xs text-indigo-400 bg-indigo-400/10 px-1.5 py-0.5 rounded">default</span> @endif
                    <button wire:click="deleteStatus({{ $status->id }})"
                            class="text-gray-600 hover:text-red-400 transition-colors p-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                @endforeach
            </div>
            <div class="flex items-center gap-3">
                <input wire:model="newStatusName" type="text" placeholder="Status name"
                       class="flex-1 bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                <input wire:model="newStatusColor" type="color"
                       class="w-9 h-9 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer p-1">
                <label class="flex items-center gap-1.5 text-sm text-gray-400 cursor-pointer">
                    <input wire:model="newStatusIsDone" type="checkbox" class="rounded bg-gray-800 border-gray-700">
                    Done
                </label>
                <button wire:click="addStatus" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-3 py-2 rounded-lg">Add</button>
            </div>
            @error('newStatusName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Categories --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-white mb-4">Task Categories</h3>
            <div class="flex flex-wrap gap-2 mb-4">
                @forelse($project->categories as $cat)
                <div class="flex items-center gap-1.5 bg-gray-800 border border-gray-700 rounded-full pl-2.5 pr-1.5 py-1">
                    <div class="w-2 h-2 rounded-full" style="background-color: {{ $cat->color }}"></div>
                    <span class="text-xs text-gray-300">{{ $cat->name }}</span>
                    <button wire:click="deleteCategory({{ $cat->id }})" class="text-gray-600 hover:text-red-400 transition-colors ml-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                @empty
                <p class="text-xs text-gray-600">No categories yet.</p>
                @endforelse
            </div>
            <div class="flex items-center gap-3">
                <input wire:model="newCategoryName" type="text" placeholder="Category name"
                       class="flex-1 bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                <input wire:model="newCategoryColor" type="color"
                       class="w-9 h-9 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer p-1">
                <button wire:click="addCategory" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-3 py-2 rounded-lg">Add</button>
            </div>
        </div>

        {{-- API Tokens --}}
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-white mb-1">API Access Tokens</h3>
            <p class="text-xs text-gray-500 mb-4">Create scoped tokens for external apps (bug catchers, CLI tools) to interact with this project.</p>

            @if($newToken)
            <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4 mb-4">
                <p class="text-xs text-green-400 font-medium mb-2">Token created — copy it now, it won't be shown again:</p>
                <div class="font-mono text-xs bg-gray-950 border border-gray-800 rounded px-3 py-2 text-green-300 break-all">{{ $newToken }}</div>
                <button wire:click="$set('newToken', null)" class="text-xs text-gray-500 hover:text-gray-300 mt-2">Dismiss</button>
            </div>
            @endif

            <div class="space-y-3 border-t border-gray-800 pt-4">
                <div>
                    <label class="text-xs text-gray-500 mb-1 block">Token Name</label>
                    <input wire:model="tokenName" type="text" placeholder="e.g. Bug Catcher Production"
                           class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                    @error('tokenName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-xs text-gray-500 mb-2 block">Abilities</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['tasks:read' => 'Read tasks', 'tasks:create' => 'Create tasks', 'tasks:update' => 'Update tasks', 'tasks:resolve' => 'Resolve tasks'] as $ability => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="tokenAbilities.{{ $ability }}"
                                   class="rounded bg-gray-800 border-gray-600 text-indigo-600">
                            <span class="text-sm text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('tokenAbilities') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <button wire:click="createApiToken"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                    Generate Token
                </button>
            </div>
        </div>

        {{-- Danger zone --}}
        <div class="bg-gray-900 border border-red-900/30 rounded-xl p-5">
            <h3 class="text-sm font-semibold text-red-400 mb-3">Danger Zone</h3>
            <p class="text-xs text-gray-500 mb-3">Permanently delete this project and all its tasks. Type <span class="font-mono text-gray-300">delete</span> to confirm.</p>
            <div class="flex items-center gap-3">
                <input wire:model="deleteConfirm" type="text" placeholder="delete"
                       class="bg-gray-800 border border-red-900/50 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-red-500 focus:border-transparent font-mono w-40">
                <button wire:click="deleteProject"
                        class="bg-red-600 hover:bg-red-500 text-white text-sm px-4 py-2 rounded-lg transition-colors">
                    Delete Project
                </button>
            </div>
            @error('deleteConfirm') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
