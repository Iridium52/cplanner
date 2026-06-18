<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-semibold text-white">Projects</h1>
            <p class="text-gray-500 text-sm mt-0.5">{{ $projects->total() }} project{{ $projects->total() !== 1 ? 's' : '' }}</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('projects.create') }}" wire:navigate
           class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Project
        </a>
        @endif
    </div>

    {{-- Project Type Pills --}}
    @if($types->isNotEmpty())
    <div class="flex flex-wrap gap-2 mb-5">
        <button wire:click="setProjectType(null)"
                class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors
                       {{ $activeProjectTypeId === null ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200' }}">
            All Types
        </button>
        @foreach($types as $type)
        <button wire:click="setProjectType({{ $type->id }})"
                class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors border
                       {{ $activeProjectTypeId === $type->id ? 'text-white border-transparent' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-gray-200 border-transparent' }}"
                @if($activeProjectTypeId === $type->id)
                    style="background-color: {{ $type->color }}; border-color: {{ $type->color }};"
                @else
                    style="border-color: {{ $type->color }}33;"
                @endif>
            {{ $type->name }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- Active Type Banner --}}
    @if($activeType)
    <div class="flex items-center gap-2 mb-5 px-3 py-2 rounded-lg bg-gray-900 border border-gray-800">
        <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $activeType->color }}"></span>
        <span class="text-sm text-gray-300 font-medium">{{ $activeType->name }}</span>
        <span class="text-xs text-gray-600 ml-1">— showing projects from this type</span>
    </div>
    @endif

    {{-- Search + Status Filters --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1 sm:max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search projects..."
                   class="w-full bg-gray-900 border border-gray-700 text-gray-100 text-sm rounded-lg pl-9 pr-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600">
        </div>

        <select wire:model.live="filterStatus"
                class="bg-gray-900 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            <option value="active">Active</option>
            <option value="archived">Archived</option>
            <option value="">All</option>
        </select>
    </div>

    {{-- Project Cards Grid --}}
    @if($projects->isEmpty())
        <div class="text-center py-16">
            <div class="w-16 h-16 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z"/></svg>
            </div>
            <p class="text-gray-500 text-sm">No projects found.</p>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('projects.create') }}" wire:navigate class="text-indigo-400 hover:text-indigo-300 text-sm mt-2 inline-block">Create your first project →</a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($projects as $project)
            <a href="{{ route('projects.show', $project) }}" wire:navigate
               class="group bg-gray-900 border border-gray-800 hover:border-gray-600 rounded-xl p-5 transition-all hover:shadow-xl hover:shadow-black/30 block">
                {{-- Card header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                             style="background-color: {{ $project->color }}">
                            {{ strtoupper(substr($project->key, 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-white text-sm group-hover:text-indigo-300 transition-colors">{{ $project->name }}</h3>
                            <span class="text-xs text-gray-600 font-mono">{{ $project->key }}</span>
                        </div>
                    </div>
                    @if($project->type)
                        <span class="text-xs px-2 py-0.5 rounded-full border text-gray-400 border-gray-700"
                              style="border-color: {{ $project->type->color }}33; color: {{ $project->type->color }}">
                            {{ $project->type->name }}
                        </span>
                    @endif
                </div>

                {{-- Description --}}
                @if($project->description)
                <p class="text-gray-500 text-xs mb-4 line-clamp-2">{{ $project->description }}</p>
                @endif

                {{-- Stats --}}
                <div class="flex items-center gap-4 pt-3 border-t border-gray-800">
                    <div class="flex items-center gap-1.5 text-xs text-gray-500">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $project->tasks_count }} tasks
                    </div>
                    <div class="flex items-center gap-1.5 text-xs {{ $project->open_tasks_count > 0 ? 'text-amber-400' : 'text-gray-600' }}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/></svg>
                        {{ $project->open_tasks_count }} open
                    </div>
                    @if($project->status === 'archived')
                    <span class="ml-auto text-xs text-gray-600 bg-gray-800 px-2 py-0.5 rounded-full">Archived</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($projects->hasPages())
        <div class="mt-6">
            {{ $projects->links() }}
        </div>
        @endif
    @endif
</div>
