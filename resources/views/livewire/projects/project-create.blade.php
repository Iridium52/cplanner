<div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    {{-- Modal panel — @click.stop prevents backdrop clicks from doing anything --}}
    <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-lg shadow-2xl" @click.stop>

        {{-- Modal header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-800">
            <h2 class="text-base font-semibold text-white">New Project</h2>
            <a href="{{ route('dashboard') }}" wire:navigate
               class="text-gray-500 hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>

        {{-- Form body --}}
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm text-gray-400 mb-1.5">Project Name <span class="text-red-400">*</span></label>
                    <input wire:model.live="name" type="text" autofocus
                           class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="My Awesome Project">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Key <span class="text-red-400">*</span></label>
                    <input wire:model="key" type="text" maxlength="10" style="text-transform: uppercase"
                           class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm font-mono uppercase focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                           placeholder="PROJ">
                    @error('key') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1.5">Description</label>
                <textarea wire:model="description" rows="3"
                          class="w-full bg-gray-950 border border-gray-700 text-white rounded-lg px-3 py-2.5 text-sm resize-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                          placeholder="What is this project about?"></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Project Type</label>
                    <select wire:model="type_id"
                            class="w-full bg-gray-950 border border-gray-700 text-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">No type</option>
                        @foreach($types as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1.5">Color</label>
                    <div class="flex items-center gap-3">
                        <input wire:model="color" type="color"
                               class="w-10 h-10 rounded-lg border border-gray-700 bg-gray-950 cursor-pointer p-1">
                        <span class="text-sm font-mono text-gray-400">{{ $color }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-800">
            <a href="{{ route('dashboard') }}" wire:navigate
               class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200 transition-colors">
                Cancel
            </a>
            <button wire:click="save" wire:loading.attr="disabled"
                    class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                <span wire:loading.remove>Create Project</span>
                <span wire:loading>Creating...</span>
            </button>
        </div>
    </div>
</div>
