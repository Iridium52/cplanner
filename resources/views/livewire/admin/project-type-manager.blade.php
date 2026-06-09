<div class="p-6 max-w-2xl">
    <h1 class="text-xl font-semibold text-white mb-6">Project Types</h1>

    <div class="bg-gray-900 border border-gray-800 rounded-xl p-5 mb-6">
        <h3 class="text-sm font-semibold text-white mb-4">{{ $editingId ? 'Edit Type' : 'New Project Type' }}</h3>
        <div class="flex items-end gap-3">
            <div class="flex-1">
                <label class="text-xs text-gray-500 mb-1 block">Name</label>
                <input wire:model="name" type="text" placeholder="e.g. Securex"
                       class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-xs text-gray-500 mb-1 block">Color</label>
                <input wire:model="color" type="color"
                       class="w-10 h-10 rounded-lg border border-gray-700 bg-gray-800 cursor-pointer p-1">
            </div>
            <button wire:click="save" class="bg-indigo-600 hover:bg-indigo-500 text-white text-sm px-4 py-2 rounded-lg h-10 transition-colors">
                {{ $editingId ? 'Update' : 'Add' }}
            </button>
            @if($editingId)
            <button wire:click="$set('editingId', null)" class="text-sm text-gray-500 hover:text-gray-300 px-3 py-2 h-10 transition-colors">Cancel</button>
            @endif
        </div>
    </div>

    <div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
        @forelse($types as $type)
        <div class="flex items-center gap-4 px-5 py-3 {{ !$loop->last ? 'border-b border-gray-800' : '' }}">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                 style="background-color: {{ $type->color }}30; border: 1px solid {{ $type->color }}40">
                <div class="w-3 h-3 rounded-full" style="background-color: {{ $type->color }}"></div>
            </div>
            <div class="flex-1">
                <div class="text-sm font-medium text-gray-200">{{ $type->name }}</div>
                <div class="text-xs text-gray-600">{{ $type->projects_count }} project{{ $type->projects_count !== 1 ? 's' : '' }}</div>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="edit({{ $type->id }})" class="text-xs text-gray-500 hover:text-indigo-400 transition-colors">Edit</button>
                <button wire:click="delete({{ $type->id }})"
                        wire:confirm="Delete this type? Projects using it will be untyped."
                        class="text-xs text-gray-600 hover:text-red-400 transition-colors">Delete</button>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-500 text-sm">No project types yet. Add one above.</div>
        @endforelse
    </div>
</div>
