<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-white">Flagged Tasks</h1>
                @if($activeType)
                <span class="inline-flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full font-medium"
                      style="background-color: {{ $activeType->color }}22; color: {{ $activeType->color }}; border: 1px solid {{ $activeType->color }}44;">
                    <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $activeType->color }}"></span>
                    {{ $activeType->name }}
                </span>
                @endif
            </div>
            <p class="text-gray-500 text-sm mt-0.5">{{ $tasks->count() }} task{{ $tasks->count() !== 1 ? 's' : '' }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-6 border-b border-gray-800">
        <button wire:click="$set('tab', 'needs_discussion')"
                class="px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px
                       {{ $tab === 'needs_discussion'
                           ? 'text-amber-400 border-amber-400'
                           : 'text-gray-500 border-transparent hover:text-gray-300' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                Needs Discussion
            </span>
        </button>
        <button wire:click="$set('tab', 'needs_action')"
                class="px-4 py-2.5 text-sm font-medium transition-colors border-b-2 -mb-px
                       {{ $tab === 'needs_action'
                           ? 'text-rose-400 border-rose-400'
                           : 'text-gray-500 border-transparent hover:text-gray-300' }}">
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Needs Action
            </span>
        </button>
    </div>

    {{-- Task list --}}
    @if($tasks->isEmpty())
        <div class="text-center py-16">
            <div class="w-14 h-14 bg-gray-900 rounded-2xl flex items-center justify-center mx-auto mb-4">
                @if($tab === 'needs_discussion')
                    <svg class="w-7 h-7 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                @else
                    <svg class="w-7 h-7 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                @endif
            </div>
            <p class="text-gray-500 text-sm">
                No tasks flagged for {{ $tab === 'needs_discussion' ? 'Needs Discussion' : 'Needs Action' }}.
            </p>
        </div>
    @else
        <div class="bg-gray-900 rounded-xl border border-gray-800 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm min-w-[500px]">
                <thead>
                    <tr class="border-b border-gray-800">
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-4 py-3">Task</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-4 py-3">Project</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider px-4 py-3">Assignee</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($tasks as $task)
                    <tr class="hover:bg-gray-800/50 transition-colors group">
                        <td class="px-4 py-3">
                            <a href="{{ route('projects.show', $task->project) }}" wire:navigate
                               class="flex flex-col gap-0.5 group-hover:text-indigo-300 transition-colors">
                                <span class="font-mono text-xs text-gray-500">{{ $task->task_number }}</span>
                                <span class="text-gray-200 group-hover:text-indigo-300">{{ $task->title }}</span>
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-gray-400">{{ $task->project->name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($task->status)
                            <span class="inline-flex items-center gap-1.5 text-xs px-2 py-0.5 rounded-full font-medium"
                                  style="background-color: {{ $task->status->color }}22; color: {{ $task->status->color }}; border: 1px solid {{ $task->status->color }}44;">
                                <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $task->status->color }}"></span>
                                {{ $task->status->name }}
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($task->assignee)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold text-white flex-shrink-0"
                                     style="background-color: {{ $task->assignee->avatar_color }}">
                                    {{ $task->assignee->initials() }}
                                </div>
                                <span class="text-gray-400 text-xs">{{ $task->assignee->name }}</span>
                            </div>
                            @else
                            <span class="text-gray-600 text-xs">Unassigned</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</div>
