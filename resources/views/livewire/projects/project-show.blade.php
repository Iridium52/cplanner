<div class="flex flex-col h-full" x-data="kanban(@this)" @download-export.window="window.location.href = $event.detail.url">

    {{-- Top bar --}}
    <div class="flex-shrink-0 border-b border-gray-800 bg-gray-900">
    <div class="flex items-center gap-2 px-3 h-12 md:h-14 md:px-6 md:gap-4">
        <div class="flex items-center gap-2.5 flex-shrink-0" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
            <div class="w-7 h-7 rounded-md flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                 style="background-color: {{ $project->color }}">
                {{ strtoupper(substr($project->key, 0, 2)) }}
            </div>
            <div class="relative">
                <button @click="open = !open"
                        class="flex items-center gap-1.5 group focus:outline-none">
                    <span class="font-semibold text-white text-sm group-hover:text-indigo-300 transition-colors">{{ $project->name }}</span>
                    @if($siblingProjects->isNotEmpty())
                        <svg x-show="!open" class="w-3.5 h-3.5 text-gray-500 group-hover:text-indigo-300 transition-colors flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        <svg x-show="open" class="w-3.5 h-3.5 text-indigo-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    @endif
                </button>

                @if($siblingProjects->isNotEmpty())
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 top-full mt-2 w-60 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50 py-1"
                     style="display: none;">
                    <div class="px-3 py-1.5 text-xs text-gray-500 font-medium uppercase tracking-wider border-b border-gray-700 mb-1">
                        Switch Project
                    </div>
                    @foreach($siblingProjects as $sibling)
                    <a href="{{ route('projects.show', $sibling) }}"
                       class="flex items-center gap-2.5 px-3 py-2 hover:bg-gray-700 transition-colors group/item">
                        <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                             style="background-color: {{ $sibling->color }}">
                            {{ strtoupper(substr($sibling->key, 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm text-gray-200 group-hover/item:text-white truncate">{{ $sibling->name }}</div>
                            <div class="text-xs text-gray-500 font-mono">{{ $sibling->key }}</div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
            <span class="text-gray-600 text-xs font-mono">{{ $project->key }}</span>
        </div>

        <div class="flex-1"></div>

        {{-- Search: desktop only (mobile row below) --}}
        <div class="hidden md:block relative">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tasks..."
                   class="bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg pl-8 pr-3 py-1.5 w-52 focus:ring-1 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600">
        </div>

        {{-- Filters: desktop only --}}
        <select wire:model.live="filterType"
                class="hidden md:block bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
            <option value="">All types</option>
            <option value="bug">Bug</option>
            <option value="feature">Feature</option>
            <option value="improvement">Improvement</option>
            <option value="chore">Chore</option>
            <option value="question">Question</option>
        </select>

        <select wire:model.live="filterPriority"
                class="hidden md:block bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
            <option value="">All priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>

        {{-- View toggle --}}
        <div class="flex items-center bg-gray-800 rounded-lg p-0.5 border border-gray-700 flex-shrink-0">
            <button wire:click="$set('view','kanban')"
                    class="flex items-center gap-1.5 px-2 md:px-3 py-1.5 rounded-md text-xs transition-colors {{ $view === 'kanban' ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-gray-300' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="12" rx="1"/><rect x="17" y="3" width="4" height="16" rx="1"/></svg>
                <span class="hidden sm:inline">Kanban</span>
            </button>
            <button wire:click="$set('view','list')"
                    class="flex items-center gap-1.5 px-2 md:px-3 py-1.5 rounded-md text-xs transition-colors {{ $view === 'list' ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-gray-300' }}">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span class="hidden sm:inline">List</span>
            </button>
        </div>

        {{-- Export --}}
        <button wire:click="openExportModal"
                class="flex items-center gap-1.5 bg-gray-800 hover:bg-gray-700 border border-gray-700 text-gray-300 hover:text-white text-xs font-medium px-2 md:px-3 py-1.5 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            <span class="hidden md:inline">Export</span>
        </button>

        @if(auth()->user()->isAdmin())
        <button wire:click="$set('showNewTaskModal', true)"
                class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-2 md:px-3 py-1.5 rounded-lg transition-colors flex-shrink-0">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            <span class="hidden md:inline">New Task</span>
        </button>
        <a href="{{ route('projects.settings', $project) }}" wire:navigate
           class="p-1.5 text-gray-500 hover:text-gray-300 transition-colors rounded-lg hover:bg-gray-800 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/></svg>
        </a>
        @endif
    </div>

    {{-- Mobile search + filter row --}}
    <div class="flex items-center gap-2 px-3 pb-2 md:hidden">
        <div class="relative flex-1">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search tasks..."
                   class="w-full bg-gray-800 border border-gray-700 text-gray-100 text-xs rounded-lg pl-8 pr-3 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600">
        </div>
        <select wire:model.live="filterType"
                class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
            <option value="">All types</option>
            <option value="bug">Bug</option>
            <option value="feature">Feature</option>
            <option value="improvement">Improvement</option>
            <option value="chore">Chore</option>
            <option value="question">Question</option>
        </select>
        <select wire:model.live="filterPriority"
                class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
            <option value="">All priorities</option>
            <option value="critical">Critical</option>
            <option value="high">High</option>
            <option value="medium">Medium</option>
            <option value="low">Low</option>
        </select>
    </div>
    </div>{{-- end top bar wrapper --}}

    {{-- Category Filter Bar --}}
    @if($project->categories->isNotEmpty())
    <div class="flex-shrink-0 flex items-center gap-2 px-6 py-2 border-b border-gray-800 bg-gray-950/50 flex-wrap">
        <span class="text-xs text-gray-600 mr-1">Tags:</span>
        @foreach($project->categories as $cat)
        <button wire:click="toggleCategoryFilter({{ $cat->id }})"
                class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border transition-all
                       {{ in_array($cat->id, $filterCategories)
                           ? 'text-white border-transparent'
                           : 'text-gray-500 border-gray-700 hover:border-gray-500 hover:text-gray-300' }}"
                @if(in_array($cat->id, $filterCategories))
                    style="background-color: {{ $cat->color }}; border-color: {{ $cat->color }};"
                @else
                    style="border-color: {{ $cat->color }}44;"
                @endif>
            <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background-color: {{ in_array($cat->id, $filterCategories) ? '#fff' : $cat->color }}"></span>
            {{ $cat->name }}
        </button>
        @endforeach
        @if($filterCategories)
        <button wire:click="$set('filterCategories', [])" class="text-xs text-gray-600 hover:text-gray-400 transition-colors ml-1">
            Clear
        </button>
        @endif
    </div>
    @endif

    {{-- New Task Modal --}}
    @if($showNewTaskModal)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-md shadow-2xl">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h2 class="text-sm font-semibold text-white">New Task — {{ $project->name }}</h2>
                <button wire:click="$set('showNewTaskModal', false)"
                        class="text-gray-500 hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            {{-- Body --}}
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input wire:model="newTaskTitle"
                           wire:keydown.enter="createTask"
                           wire:keydown.escape="$set('showNewTaskModal', false)"
                           type="text" placeholder="What needs to be done?" autofocus
                           class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600">
                    @error('newTaskTitle') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Type</label>
                        <select wire:model="newTaskType"
                                class="w-full bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2.5 focus:ring-1 focus:ring-indigo-500">
                            <option value="feature">✨ Feature</option>
                            <option value="bug">🐛 Bug</option>
                            <option value="improvement">📈 Improvement</option>
                            <option value="chore">🔧 Chore</option>
                            <option value="question">❓ Question</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1.5">Priority</label>
                        <select wire:model="newTaskPriority"
                                class="w-full bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2.5 focus:ring-1 focus:ring-indigo-500">
                            <option value="critical">🔴 Critical</option>
                            <option value="high">🟠 High</option>
                            <option value="medium">🟡 Medium</option>
                            <option value="low">⚪ Low</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1.5">Status</label>
                    <select wire:model="newTaskStatusId"
                            class="w-full bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2.5 focus:ring-1 focus:ring-indigo-500">
                        @foreach($statuses as $s)
                            <option value="{{ $s->id }}" {{ $s->is_default ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-800">
                <button wire:click="$set('showNewTaskModal', false)"
                        class="text-sm text-gray-400 hover:text-gray-200 transition-colors px-4 py-2">
                    Cancel
                </button>
                <button wire:click="createTask" wire:loading.attr="disabled"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    <span wire:loading.remove wire:target="createTask">Create Task</span>
                    <span wire:loading wire:target="createTask">Creating…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- KANBAN VIEW --}}
    @if($view === 'kanban')
    <div class="flex-1 overflow-x-auto overflow-y-hidden">
        <div class="flex gap-4 p-6 h-full min-w-max">
            @foreach($statuses as $status)
            <div class="flex flex-col w-72 flex-shrink-0 group/col">
                {{-- Column header --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full" style="background-color: {{ $status->color }}"></div>
                        <span class="text-sm font-medium text-gray-300">{{ $status->name }}</span>
                        <span class="text-xs text-gray-600 bg-gray-800 px-1.5 py-0.5 rounded-full">
                            {{ ($tasksByStatus[$status->id] ?? collect())->count() }}
                        </span>
                    </div>
                </div>

                {{-- Task cards --}}
                <div class="flex-1 overflow-y-auto">
                <div class="space-y-2 min-h-4"
                     id="kanban-col-{{ $status->id }}"
                     data-status-id="{{ $status->id }}"
                     x-sortable
                     @sortable-update="updatePositions">
                    @foreach(($tasksByStatus[$status->id] ?? collect()) as $task)
                    <div class="bg-gray-900 border border-gray-800 hover:border-gray-600 rounded-xl p-3.5 cursor-pointer transition-all hover:shadow-lg hover:shadow-black/20 group"
                         data-task-id="{{ $task->id }}"
                         wire:click="openTask({{ $task->id }})"
                         x-sortable-item>
                        {{-- Drag handle --}}
                        <div x-sortable-handle
                             class="flex justify-center mb-2 -mt-1 cursor-grab active:cursor-grabbing touch-none">
                            <svg class="w-4 h-4 text-gray-700 group-hover:text-gray-500 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 4a1 1 0 100 2 1 1 0 000-2zM7 9a1 1 0 100 2 1 1 0 000-2zM7 14a1 1 0 100 2 1 1 0 000-2zM13 4a1 1 0 100 2 1 1 0 000-2zM13 9a1 1 0 100 2 1 1 0 000-2zM13 14a1 1 0 100 2 1 1 0 000-2z"/>
                            </svg>
                        </div>
                        {{-- Type + task number --}}
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs {{ \App\Models\Task::typeColor($task->type) }}">
                                    @if($task->type === 'bug') 🐛
                                    @elseif($task->type === 'feature') ✨
                                    @elseif($task->type === 'improvement') 📈
                                    @elseif($task->type === 'chore') 🔧
                                    @else ❓
                                    @endif
                                </span>
                                <span class="text-xs font-mono text-gray-600">{{ $task->task_number }}</span>
                            </div>
                            {{-- Priority badge --}}
                            @php
                                $priorityConfig = match($task->priority) {
                                    'critical' => ['label' => 'Critical', 'class' => 'bg-red-500/15 text-red-400'],
                                    'high'     => ['label' => 'High',     'class' => 'bg-orange-500/15 text-orange-400'],
                                    'medium'   => ['label' => 'Medium',   'class' => 'bg-yellow-500/15 text-yellow-500'],
                                    'low'      => ['label' => 'Low',      'class' => 'bg-gray-700 text-gray-500'],
                                };
                            @endphp
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ $priorityConfig['class'] }}">
                                {{ $priorityConfig['label'] }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-100 group-hover:text-white leading-snug mb-1.5">{{ $task->title }}</p>

                        {{-- Description preview --}}
                        @if($task->description)
                        <p class="text-xs text-gray-500 leading-relaxed line-clamp-2 mb-2">{{ $task->description }}</p>
                        @endif

                        {{-- Footer --}}
                        <div class="flex items-center justify-between mt-2 pt-2 border-t border-gray-800">
                            <div class="flex items-center gap-2">
                                @foreach($task->categories as $cat)
                                    <span class="text-xs px-1.5 py-0.5 rounded-full border"
                                          style="background-color: {{ $cat->color }}22; color: {{ $cat->color }}; border-color: {{ $cat->color }}44;">
                                        {{ $cat->name }}
                                    </span>
                                @endforeach
                                @if($task->due_date)
                                    <span class="text-xs {{ $task->isOverdue() ? 'text-red-400' : 'text-gray-500' }}">
                                        📅 {{ $task->due_date->format('M j') }}
                                    </span>
                                @endif
                                @if($task->attachments_count > 0)
                                <span class="flex items-center gap-0.5 text-xs text-gray-600">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    {{ $task->attachments_count }}
                                </span>
                                @endif
                                @if($task->needs_discussion)
                                <span class="flex items-center text-amber-500/70" title="Needs Discussion">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </span>
                                @endif
                                @if($task->needs_action)
                                <span class="flex items-center text-rose-500/70" title="Needs Action">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </span>
                                @endif
                            </div>
                            @if($task->assignee)
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-semibold text-white"
                                 style="background-color: {{ $task->assignee->avatar_color }}"
                                 title="{{ $task->assignee->name }}">
                                {{ $task->assignee->initials() }}
                            </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Quick-add zone (admins only) --}}
                @if(auth()->user()->isAdmin())
                <div x-data="{ adding: false, title: '' }" class="mt-2">
                    {{-- Ghost card — hover reveal --}}
                    <div x-show="!adding"
                         @click="adding = true; $nextTick(() => $refs.quickInput{{ $status->id }}.focus())"
                         class="opacity-0 group-hover/col:opacity-100 cursor-pointer rounded-xl border-2 border-dashed border-gray-800 hover:border-indigo-700/60 transition-all duration-150 flex items-center justify-center py-4 text-gray-700 hover:text-indigo-500">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    {{-- Inline input --}}
                    <div x-show="adding" x-cloak
                         class="bg-gray-900 border border-indigo-700/60 rounded-xl p-3 shadow-lg">
                        <input x-ref="quickInput{{ $status->id }}"
                               x-model="title"
                               @keydown.enter.prevent="title.trim() && ($wire.quickCreateTask(title.trim(), {{ $status->id }}), adding = false, title = '')"
                               @keydown.escape="adding = false; title = ''"
                               type="text"
                               placeholder="Task title…"
                               class="w-full bg-transparent text-white text-sm placeholder-gray-600 focus:outline-none leading-snug">
                        <div class="flex items-center gap-2 mt-2.5 pt-2 border-t border-gray-800">
                            <button @click="title.trim() && ($wire.quickCreateTask(title.trim(), {{ $status->id }}), adding = false, title = '')"
                                    class="text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1 rounded-lg transition-colors">
                                Add
                            </button>
                            <button @click="adding = false; title = ''"
                                    class="text-xs text-gray-500 hover:text-gray-300 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
                @endif
                </div>{{-- end outer scroll wrapper --}}

            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- LIST VIEW --}}
    @if($view === 'list')
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- List toolbar: status filter --}}
        <div class="flex-shrink-0 flex items-center gap-3 px-6 py-2 border-b border-gray-800 bg-gray-950/40">
            <span class="text-xs text-gray-600">Show:</span>
            <select wire:model.live="listFilterStatus"
                    class="bg-gray-800 border border-gray-700 text-gray-300 text-xs rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500 focus:border-transparent">
                <option value="open">Open (excl. Done)</option>
                <option value="all">All statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
            <span class="text-xs text-gray-600 ml-auto">{{ $listTasks->count() }} task{{ $listTasks->count() !== 1 ? 's' : '' }}</span>
        </div>

        <div class="flex-1 overflow-y-auto overflow-x-auto">
        @php
            $sortIcon = function(string $col) use ($listSortColumn, $listSortDir): string {
                if ($listSortColumn !== $col) return '<svg class="w-3 h-3 text-gray-700 group-hover/th:text-gray-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>';
                return $listSortDir === 'asc'
                    ? '<svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>'
                    : '<svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>';
            };
        @endphp
        <table class="w-full min-w-[700px]">
            <thead class="sticky top-0 bg-gray-950 border-b border-gray-800 z-10">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                        <button wire:click="sortList('task_number')" class="flex items-center gap-1 group/th uppercase">Key {!! $sortIcon('task_number') !!}</button>
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button wire:click="sortList('title')" class="flex items-center gap-1 group/th uppercase">Title {!! $sortIcon('title') !!}</button>
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                        <button wire:click="sortList('status')" class="flex items-center gap-1 group/th uppercase">Status {!! $sortIcon('status') !!}</button>
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                        <button wire:click="sortList('type')" class="flex items-center gap-1 group/th uppercase">Type {!! $sortIcon('type') !!}</button>
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                        <button wire:click="sortList('priority')" class="flex items-center gap-1 group/th uppercase">Priority {!! $sortIcon('priority') !!}</button>
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                        <button wire:click="sortList('assignee')" class="flex items-center gap-1 group/th uppercase">Assignee {!! $sortIcon('assignee') !!}</button>
                    </th>
                    <th class="text-left px-4 py-3 text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                        <button wire:click="sortList('due_date')" class="flex items-center gap-1 group/th uppercase">Due {!! $sortIcon('due_date') !!}</button>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($listTasks as $task)
                <tr x-data="{ editing: null }" class="hover:bg-gray-900/60 transition-colors group/row">

                    {{-- Key --}}
                    <td class="px-6 py-3 text-xs font-mono text-gray-600 whitespace-nowrap">{{ $task->task_number }}</td>

                    {{-- Title + description — opens modal --}}
                    <td class="px-4 py-2.5 cursor-pointer" wire:click="openTask({{ $task->id }})">
                        <div class="flex items-start gap-2">
                            <span class="text-sm mt-0.5 flex-shrink-0 {{ \App\Models\Task::typeColor($task->type) }}">
                                @if($task->type === 'bug') 🐛
                                @elseif($task->type === 'feature') ✨
                                @elseif($task->type === 'improvement') 📈
                                @elseif($task->type === 'chore') 🔧
                                @else ❓
                                @endif
                            </span>
                            <div>
                                <span class="text-sm text-gray-200 group-hover/row:text-white leading-snug">{{ $task->title }}</span>
                                @if($task->description)
                                <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $task->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Status — inline edit --}}
                    <td class="px-4 py-2.5 w-32" @click.stop>
                        <div x-show="editing !== 'status'" @click="editing = 'status'" class="cursor-pointer">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-1 rounded-full font-medium"
                                  style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                                {{ $task->status->name }}
                            </span>
                        </div>
                        <div x-show="editing === 'status'" @click.stop>
                            <select
                                x-init="$el.focus()"
                                @keydown.escape="editing = null"
                                @change="$wire.inlineUpdateTask({{ $task->id }}, 'status_id', $event.target.value); editing = null"
                                class="bg-gray-800 border border-indigo-600 text-white text-xs rounded-lg px-2 py-1.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                @foreach($statuses as $s)
                                    <option value="{{ $s->id }}" {{ $task->status_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>

                    {{-- Type — inline edit --}}
                    <td class="px-4 py-2.5 w-28" @click.stop>
                        @if(auth()->user()->isAdmin())
                        <div x-show="editing !== 'type'" @click="editing = 'type'" class="cursor-pointer text-xs text-gray-400 capitalize">{{ $task->type }}</div>
                        <div x-show="editing === 'type'" @click.stop>
                            <select
                                x-init="$el.focus()"
                                @keydown.escape="editing = null"
                                @change="$wire.inlineUpdateTask({{ $task->id }}, 'type', $event.target.value); editing = null"
                                class="bg-gray-800 border border-indigo-600 text-white text-xs rounded-lg px-2 py-1.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="bug" {{ $task->type === 'bug' ? 'selected' : '' }}>Bug</option>
                                <option value="feature" {{ $task->type === 'feature' ? 'selected' : '' }}>Feature</option>
                                <option value="improvement" {{ $task->type === 'improvement' ? 'selected' : '' }}>Improvement</option>
                                <option value="chore" {{ $task->type === 'chore' ? 'selected' : '' }}>Chore</option>
                                <option value="question" {{ $task->type === 'question' ? 'selected' : '' }}>Question</option>
                            </select>
                        </div>
                        @else
                        <span class="text-xs text-gray-400 capitalize">{{ $task->type }}</span>
                        @endif
                    </td>

                    {{-- Priority — inline edit --}}
                    <td class="px-4 py-2.5 w-28" @click.stop>
                        @if(auth()->user()->isAdmin())
                        <div x-show="editing !== 'priority'" @click="editing = 'priority'" class="cursor-pointer">
                            <span class="text-xs {{ \App\Models\Task::priorityColor($task->priority) }} capitalize">{{ $task->priority }}</span>
                        </div>
                        <div x-show="editing === 'priority'" @click.stop>
                            <select
                                x-init="$el.focus()"
                                @keydown.escape="editing = null"
                                @change="$wire.inlineUpdateTask({{ $task->id }}, 'priority', $event.target.value); editing = null"
                                class="bg-gray-800 border border-indigo-600 text-white text-xs rounded-lg px-2 py-1.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="critical" {{ $task->priority === 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="high" {{ $task->priority === 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ $task->priority === 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ $task->priority === 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                        </div>
                        @else
                        <span class="text-xs {{ \App\Models\Task::priorityColor($task->priority) }} capitalize">{{ $task->priority }}</span>
                        @endif
                    </td>

                    {{-- Assignee — inline edit --}}
                    <td class="px-4 py-2.5 w-36" @click.stop>
                        @if(auth()->user()->isAdmin())
                        <div x-show="editing !== 'assignee'" @click="editing = 'assignee'" class="cursor-pointer">
                            @if($task->assignee)
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-semibold text-white flex-shrink-0"
                                     style="background-color: {{ $task->assignee->avatar_color }}">{{ $task->assignee->initials() }}</div>
                                <span class="text-xs text-gray-400 truncate">{{ $task->assignee->name }}</span>
                            </div>
                            @else
                            <span class="text-xs text-gray-600 hover:text-gray-400">Unassigned</span>
                            @endif
                        </div>
                        <div x-show="editing === 'assignee'" @click.stop>
                            <select
                                x-init="$el.focus()"
                                @keydown.escape="editing = null"
                                @change="$wire.inlineUpdateTask({{ $task->id }}, 'assignee_id', $event.target.value); editing = null"
                                class="bg-gray-800 border border-indigo-600 text-white text-xs rounded-lg px-2 py-1.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ $task->assignee_id == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @else
                        @if($task->assignee)
                        <div class="flex items-center gap-1.5">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-semibold text-white flex-shrink-0"
                                 style="background-color: {{ $task->assignee->avatar_color }}">{{ $task->assignee->initials() }}</div>
                            <span class="text-xs text-gray-400 truncate">{{ $task->assignee->name }}</span>
                        </div>
                        @else
                        <span class="text-xs text-gray-600">—</span>
                        @endif
                        @endif
                    </td>

                    {{-- Due Date — inline edit --}}
                    <td class="px-4 py-2.5 w-28" @click.stop>
                        @if(auth()->user()->isAdmin())
                        <div x-show="editing !== 'due_date'" @click="editing = 'due_date'" class="cursor-pointer">
                            @if($task->due_date)
                            <span class="text-xs {{ $task->isOverdue() ? 'text-red-400' : 'text-gray-400' }}">
                                {{ $task->due_date->format('M j, Y') }}
                            </span>
                            @else
                            <span class="text-xs text-gray-700 hover:text-gray-500">Set date</span>
                            @endif
                        </div>
                        <div x-show="editing === 'due_date'" @click.stop>
                            <input type="date"
                                   value="{{ $task->due_date?->format('Y-m-d') }}"
                                   x-init="$el.focus()"
                                   @keydown.escape="editing = null"
                                   @change="$wire.inlineUpdateTask({{ $task->id }}, 'due_date', $event.target.value); editing = null"
                                   class="bg-gray-800 border border-indigo-600 text-white text-xs rounded-lg px-2 py-1.5 w-full focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        </div>
                        @else
                        @if($task->due_date)
                        <span class="text-xs {{ $task->isOverdue() ? 'text-red-400' : 'text-gray-400' }}">
                            {{ $task->due_date->format('M j, Y') }}
                        </span>
                        @else
                        <span class="text-xs text-gray-700">—</span>
                        @endif
                        @endif
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 text-sm">No tasks found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- TASK DETAIL MODAL --}}
    @if($showTaskModal && $selectedTask)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-end sm:items-start sm:justify-end sm:p-4"
         x-data="clipboardUploader()"
         @paste.window="handlePaste($event)"
         wire:click.self="closeTask">

        {{-- Image lightbox --}}
        <div x-show="preview"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="preview = null"
             @keydown.escape.window="preview = null"
             class="fixed inset-0 bg-black/95 z-[60] flex items-center justify-center p-8"
             style="display:none">
            <button @click.stop="preview = null"
                    class="absolute top-4 right-4 text-white/50 hover:text-white transition-colors">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="preview" @click.stop class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
        </div>

        <div class="relative bg-gray-900 border-0 sm:border border-gray-800 rounded-t-2xl sm:rounded-xl w-full sm:max-w-2xl h-[92dvh] sm:h-[calc(100vh-2rem)] flex flex-col shadow-2xl"
             @dragenter="handleDragEnter($event)"
             @dragover="handleDragOver($event)"
             @dragleave="handleDragLeave($event)"
             @drop="handleDrop($event)">

            {{-- Drag-over overlay --}}
            <div x-show="dragging"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-[70] rounded-t-2xl sm:rounded-xl bg-indigo-950/90 border-2 border-dashed border-indigo-500 flex flex-col items-center justify-center gap-3 pointer-events-none"
                 style="display:none">
                <svg class="w-12 h-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                </svg>
                <p class="text-indigo-300 text-sm font-medium">Drop files to attach</p>
                <p class="text-indigo-500 text-xs">Images, PDFs, Word, Excel and more</p>
            </div>

            {{-- Modal header --}}
            <div class="flex-shrink-0 flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono text-gray-500">{{ $selectedTask->task_number }}</span>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                          style="background-color: {{ $selectedTask->status->color }}20; color: {{ $selectedTask->status->color }}">
                        {{ $selectedTask->status->name }}
                    </span>
                </div>
                <div class="flex items-center gap-2">
                    @if(auth()->user()->isAdmin())
                    <button wire:click="$set('showDeleteConfirm', true)"
                            class="text-gray-600 hover:text-red-400 transition-colors p-1 rounded-lg hover:bg-red-500/10"
                            title="Delete task">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    @endif
                    <button wire:click="closeTask" class="text-gray-500 hover:text-gray-300 transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            {{-- Delete confirmation --}}
            @if($showDeleteConfirm)
            <div class="flex-shrink-0 bg-red-950/40 border-b border-red-900/40 px-5 py-4">
                <p class="text-sm font-medium text-red-300 mb-1">Delete this task?</p>
                <p class="text-xs text-gray-500 mb-3">This cannot be undone. Type <span class="font-mono text-gray-300">delete</span> to confirm.</p>
                <div class="flex items-center gap-3">
                    <input wire:model="deleteConfirmText"
                           wire:keydown.enter="deleteTask"
                           wire:keydown.escape="$set('showDeleteConfirm', false)"
                           type="text" placeholder="delete" autofocus
                           class="bg-gray-900 border border-red-800 text-white text-sm rounded-lg px-3 py-1.5 w-36 font-mono focus:ring-1 focus:ring-red-500 focus:border-red-500 placeholder-gray-700">
                    <button wire:click="deleteTask"
                            class="bg-red-600 hover:bg-red-500 text-white text-sm font-medium px-4 py-1.5 rounded-lg transition-colors">
                        Delete
                    </button>
                    <button wire:click="$set('showDeleteConfirm', false); $set('deleteConfirmText', '')"
                            class="text-sm text-gray-500 hover:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
                @error('deleteConfirmText') <p class="text-red-400 text-xs mt-2">{{ $message }}</p> @enderror
            </div>
            @endif

            {{-- Modal body --}}
            <div class="flex-1 overflow-y-auto">
                <div class="p-5 space-y-5">
                    {{-- Title --}}
                    @if(auth()->user()->isAdmin())
                    <div class="flex items-start gap-2 -mx-2">
                        <input wire:model="editTitle"
                               wire:keydown.enter="saveTitle"
                               wire:keydown.escape="$set('editTitle', selectedTask.title)"
                               type="text"
                               class="flex-1 bg-transparent border-0 text-white text-lg font-semibold focus:bg-gray-800 focus:ring-1 focus:ring-indigo-500 rounded-lg px-2 py-1 transition-colors min-w-0">
                        <button wire:click="saveTitle"
                                class="flex-shrink-0 mt-1 text-xs text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 hover:bg-indigo-500/20 px-2.5 py-1.5 rounded-lg transition-colors">
                            Rename
                        </button>
                    </div>
                    @error('editTitle') <p class="text-red-400 text-xs mt-1 px-2">{{ $message }}</p> @enderror
                    @else
                    <h2 class="text-lg font-semibold text-white">{{ $selectedTask->title }}</h2>
                    @endif

                    {{-- Metadata grid --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Status</label>
                            @if(auth()->user()->isAdmin())
                            <select wire:change="updateTaskField('status_id', $event.target.value)"
                                    class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500">
                                @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ $selectedTask->status_id == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-sm text-gray-300">{{ $selectedTask->status->name }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Priority</label>
                            @if(auth()->user()->isAdmin())
                            <select wire:change="updateTaskField('priority', $event.target.value)"
                                    class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500">
                                <option value="critical" {{ $selectedTask->priority == 'critical' ? 'selected' : '' }}>Critical</option>
                                <option value="high" {{ $selectedTask->priority == 'high' ? 'selected' : '' }}>High</option>
                                <option value="medium" {{ $selectedTask->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                <option value="low" {{ $selectedTask->priority == 'low' ? 'selected' : '' }}>Low</option>
                            </select>
                            @else
                            <span class="text-sm text-gray-300 capitalize">{{ $selectedTask->priority }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Assignee</label>
                            @if(auth()->user()->isAdmin())
                            <select wire:change="updateTaskField('assignee_id', $event.target.value)"
                                    class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500">
                                <option value="">Unassigned</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $selectedTask->assignee_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @else
                            <span class="text-sm text-gray-300">{{ $selectedTask->assignee?->name ?? 'Unassigned' }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Due Date</label>
                            @if(auth()->user()->isAdmin())
                            <input type="date" value="{{ $selectedTask->due_date?->format('Y-m-d') }}"
                                   wire:change="updateTaskField('due_date', $event.target.value)"
                                   class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500">
                            @else
                            <span class="text-sm text-gray-300">{{ $selectedTask->due_date?->format('M j, Y') ?? '—' }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Type</label>
                            @if(auth()->user()->isAdmin())
                            <select wire:change="updateTaskField('type', $event.target.value)"
                                    class="w-full bg-gray-800 border border-gray-700 text-white text-sm rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-indigo-500">
                                <option value="bug" {{ $selectedTask->type == 'bug' ? 'selected' : '' }}>🐛 Bug</option>
                                <option value="feature" {{ $selectedTask->type == 'feature' ? 'selected' : '' }}>✨ Feature</option>
                                <option value="improvement" {{ $selectedTask->type == 'improvement' ? 'selected' : '' }}>📈 Improvement</option>
                                <option value="chore" {{ $selectedTask->type == 'chore' ? 'selected' : '' }}>🔧 Chore</option>
                                <option value="question" {{ $selectedTask->type == 'question' ? 'selected' : '' }}>❓ Question</option>
                            </select>
                            @else
                            <span class="text-sm text-gray-300 capitalize">{{ $selectedTask->type }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 mb-1 block">Reporter</label>
                            <span class="text-sm text-gray-300">{{ $selectedTask->reporter->name }}</span>
                        </div>
                    </div>

                    {{-- Categories --}}
                    @if($project->categories->isNotEmpty())
                    <div>
                        <label class="text-xs text-gray-500 mb-2 block">Tags</label>
                        <div class="flex flex-wrap gap-1.5">
                            @php $assignedIds = $selectedTask->categories->pluck('id')->toArray(); @endphp
                            @foreach($project->categories as $cat)
                            @if(auth()->user()->isAdmin())
                            <button wire:click="toggleTaskCategory({{ $cat->id }})"
                                    class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full border transition-all
                                           {{ in_array($cat->id, $assignedIds)
                                               ? 'text-white border-transparent'
                                               : 'text-gray-500 border-gray-700 hover:border-gray-500 hover:text-gray-300' }}"
                                    @if(in_array($cat->id, $assignedIds))
                                        style="background-color: {{ $cat->color }}; border-color: {{ $cat->color }};"
                                    @else
                                        style="border-color: {{ $cat->color }}44;"
                                    @endif>
                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                      style="background-color: {{ in_array($cat->id, $assignedIds) ? '#fff' : $cat->color }}"></span>
                                {{ $cat->name }}
                            </button>
                            @elseif(in_array($cat->id, $assignedIds))
                            <span class="flex items-center gap-1.5 text-xs px-2.5 py-1 rounded-full text-white border-transparent"
                                  style="background-color: {{ $cat->color }};">
                                {{ $cat->name }}
                            </span>
                            @endif
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Flags --}}
                    <div class="flex items-center gap-2">
                        @if(auth()->user()->isAdmin())
                        <button wire:click="toggleFlag('needs_discussion')"
                                class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full border transition-all
                                       {{ $selectedTask->needs_discussion
                                           ? 'bg-amber-500/20 text-amber-400 border-amber-500/40 hover:bg-amber-500/30'
                                           : 'bg-transparent text-gray-500 border-gray-700 hover:border-amber-600/50 hover:text-amber-500' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Needs Discussion
                        </button>
                        <button wire:click="toggleFlag('needs_action')"
                                class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full border transition-all
                                       {{ $selectedTask->needs_action
                                           ? 'bg-rose-500/20 text-rose-400 border-rose-500/40 hover:bg-rose-500/30'
                                           : 'bg-transparent text-gray-500 border-gray-700 hover:border-rose-600/50 hover:text-rose-500' }}">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Needs Action
                        </button>
                        @else
                        {{-- Viewer: display-only --}}
                        @if($selectedTask->needs_discussion)
                        <span class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/40">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            Needs Discussion
                        </span>
                        @endif
                        @if($selectedTask->needs_action)
                        <span class="flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/40">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Needs Action
                        </span>
                        @endif
                        @endif
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-1.5 block">Description</label>
                        @if(auth()->user()->isAdmin())
                        <textarea wire:model="editDescription"
                                  rows="4"
                                  class="w-full bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 resize-none focus:ring-1 focus:ring-indigo-500 focus:border-transparent"
                                  placeholder="Add a description..."></textarea>
                        @else
                        <div class="text-sm text-gray-400 bg-gray-800 rounded-lg px-3 py-2 min-h-16">
                            {{ $selectedTask->description ?: 'No description.' }}
                        </div>
                        @endif
                    </div>

                    {{-- Attachments --}}
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <label class="text-xs text-gray-500">
                                Attachments{{ $selectedTask->attachments->count() > 0 ? ' (' . $selectedTask->attachments->count() . ')' : '' }}
                            </label>
                            <span x-show="uploading" class="text-xs text-indigo-400 animate-pulse">Uploading…</span>
                            <span x-show="!uploading" class="text-xs text-gray-600">Paste, drag &amp; drop, or browse</span>
                        </div>

                        {{-- Thumbnail grid --}}
                        @if($selectedTask->attachments->isNotEmpty())
                        <div class="grid grid-cols-4 gap-2 mb-3">
                            @foreach($selectedTask->attachments as $att)
                            <div class="relative group/tile aspect-square">
                                @if(str_starts_with($att->mime_type ?? '', 'image/'))
                                <button type="button"
                                        @click.stop="preview = '{{ route('attachments.serve', $att) }}'"
                                        class="w-full h-full rounded-lg overflow-hidden bg-gray-800 ring-1 ring-gray-700 hover:ring-indigo-500 transition-all group relative">
                                    <img src="{{ route('attachments.serve', $att) }}"
                                         class="w-full h-full object-cover"
                                         alt="{{ $att->filename }}">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/></svg>
                                    </div>
                                </button>
                                @else
                                <a href="{{ route('attachments.serve', $att) }}" target="_blank"
                                   class="flex flex-col items-center justify-center w-full h-full rounded-lg bg-gray-800 ring-1 ring-gray-700 hover:ring-indigo-500 transition-all p-2 gap-1 group"
                                   title="{{ $att->filename }}">
                                    <svg class="w-6 h-6 text-gray-500 group-hover:text-gray-300 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    <span class="text-[10px] text-gray-500 truncate w-full text-center leading-tight">{{ $att->filename }}</span>
                                    <span class="text-[10px] text-gray-600">{{ $att->formattedSize() }}</span>
                                </a>
                                @endif
                                @if(auth()->user()->isAdmin())
                                <button wire:click="deleteAttachment({{ $att->id }})"
                                        wire:confirm="Delete this attachment?"
                                        type="button"
                                        class="absolute top-1 right-1 w-5 h-5 rounded-full bg-gray-950/80 text-gray-400 hover:text-red-400 hover:bg-gray-950 flex items-center justify-center opacity-0 group-hover/tile:opacity-100 transition-all z-10">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        {{-- Upload input --}}
                        @if(auth()->user()->isAdmin())
                        <input type="file" wire:model="attachment"
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.png,.jpg,.jpeg,.gif,.webp,.svg"
                               class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-xs file:bg-gray-700 file:text-gray-300 hover:file:bg-gray-600 cursor-pointer">
                        @if($attachment)
                        <button wire:click="uploadAttachment"
                                class="mt-1.5 text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-2.5 py-1 rounded-lg transition-colors">
                            Upload
                        </button>
                        @endif
                        <p class="text-xs text-gray-700 mt-1.5">Paste a screenshot anywhere in this modal to attach it</p>
                        @endif
                    </div>

                    {{-- Description save --}}
                    @if(auth()->user()->isAdmin())
                    <div class="flex items-center gap-2 -mt-2">
                        <button wire:click="saveDescription"
                                class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
                            Save &amp; close
                        </button>
                    </div>
                    @endif

                    {{-- Comments --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-3 block">Comments ({{ $selectedTask->comments->count() }})</label>
                        <div class="space-y-3 mb-4">
                            @foreach($selectedTask->comments as $comment)
                            <div class="flex gap-3">
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-semibold text-white flex-shrink-0 mt-0.5"
                                     style="background-color: {{ $comment->user->avatar_color }}">
                                    {{ $comment->user->initials() }}
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium text-gray-300">{{ $comment->user->name }}</span>
                                        <span class="text-xs text-gray-600">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-sm text-gray-400 bg-gray-800 rounded-lg px-3 py-2">{{ $comment->body }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="flex gap-3">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-semibold text-white flex-shrink-0 mt-1"
                                 style="background-color: {{ auth()->user()->avatar_color }}">
                                {{ auth()->user()->initials() }}
                            </div>
                            <div class="flex-1">
                                <textarea wire:model="commentBody" wire:keydown.ctrl.enter="addComment"
                                          rows="2" placeholder="Add a comment... (Ctrl+Enter to submit)"
                                          class="w-full bg-gray-800 border border-gray-700 text-gray-300 text-sm rounded-lg px-3 py-2 resize-none focus:ring-1 focus:ring-indigo-500 focus:border-transparent placeholder-gray-600"></textarea>
                                @error('commentBody') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                <button wire:click="addComment" class="mt-2 text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded-lg transition-colors">
                                    Post Comment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- EXPORT MODAL --}}
    @if($showExportModal)
    <div class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4"
         wire:click.self="closeExportModal">
        <div class="bg-gray-900 border border-gray-800 rounded-xl w-full max-w-sm shadow-2xl">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-800">
                <h2 class="text-sm font-semibold text-white">Export Tasks</h2>
                <button wire:click="closeExportModal"
                        class="text-gray-500 hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-gray-800">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-5 space-y-5">

                {{-- Format --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-2">Format</label>
                    <div class="flex gap-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="exportFormat" value="excel"
                                   class="text-indigo-500 bg-gray-800 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900">
                            <span class="text-sm text-gray-300">Excel (.xlsx)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="exportFormat" value="word"
                                   class="text-indigo-500 bg-gray-800 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900">
                            <span class="text-sm text-gray-300">Word (.docx)</span>
                        </label>
                    </div>
                </div>

                {{-- Types --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-2">
                        Types
                        <span class="text-gray-700 ml-1">— leave all unchecked for all</span>
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(['bug' => 'Bug', 'feature' => 'Feature', 'improvement' => 'Improvement', 'chore' => 'Chore', 'question' => 'Question'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="exportTypes" value="{{ $value }}"
                                   class="rounded text-indigo-500 bg-gray-800 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900">
                            <span class="text-sm text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Priorities --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-2">
                        Priorities
                        <span class="text-gray-700 ml-1">— leave all unchecked for all</span>
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach(['critical' => 'Critical', 'high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="exportPriorities" value="{{ $value }}"
                                   class="rounded text-indigo-500 bg-gray-800 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900">
                            <span class="text-sm text-gray-300">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Statuses --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-2">
                        Statuses
                        <span class="text-gray-700 ml-1">— leave all unchecked for all</span>
                    </label>
                    <div class="grid grid-cols-2 gap-1.5">
                        @foreach($project->statuses as $status)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" wire:model="exportStatuses" value="{{ $status->id }}"
                                   class="rounded text-indigo-500 bg-gray-800 border-gray-600 focus:ring-indigo-500 focus:ring-offset-gray-900">
                            <span class="flex items-center gap-1.5 text-sm text-gray-300">
                                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background-color: {{ $status->color }}"></span>
                                {{ $status->name }}
                            </span>
                        </label>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-5 py-4 border-t border-gray-800">
                <button wire:click="closeExportModal"
                        class="text-sm text-gray-400 hover:text-gray-200 transition-colors px-4 py-2">
                    Cancel
                </button>
                <button wire:click="doExport"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Download
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function kanban(wire) {
    return {
        updatePositions(event) {
            const columns = document.querySelectorAll('[data-status-id]');
            const positions = [];
            columns.forEach(col => {
                const statusId = parseInt(col.dataset.statusId);
                col.querySelectorAll('[data-task-id]').forEach((card, index) => {
                    positions.push({
                        id: parseInt(card.dataset.taskId),
                        status_id: statusId,
                        position: index,
                    });
                });
            });
            wire.call('updateTaskPositions', positions);
        }
    }
}
</script>
@endpush
