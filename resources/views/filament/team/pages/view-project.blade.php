{{-- resources/views/filament/team/pages/view-project.blade.php --}}
<x-filament-panels::page>
    @php
        $user = auth()->user();
        $tasks = $record->tasks()->with(['assignedTo', 'createdBy'])->get();
        $taskStats = [
            'total' => $tasks->count(),
            'todo' => $tasks->where('status', 'todo')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'review' => $tasks->where('status', 'review')->count(),
            'done' => $tasks->where('status', 'done')->count(),
            'blocked' => $tasks->where('status', 'blocked')->count(),
        ];
    @endphp

    <div class="space-y-6">
        {{-- Header Info --}}
<div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 relative overflow-hidden">
    <!-- Background Pattern -->
    <div class="absolute inset-0 bg-gradient-to-br from-slate-50 to-gray-50 opacity-50"></div>
    <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-blue-500/5 to-indigo-500/5 rounded-full -translate-y-48 translate-x-48"></div>
    
    <div class="relative z-10">
        <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-8">
            <!-- Bagian Kiri - Informasi Utama -->
            <div class="flex-1 space-y-6">
                <div class="space-y-3">
                    <h1 class="text-4xl font-bold text-gray-900 leading-tight">
                        {{ $record->nama_project }}
                    </h1>
                    <div class="h-1 w-16 bg-gradient-to-r from-blue-600 to-indigo-600 rounded-full"></div>
                </div>
                
                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                    <div class="flex items-center space-x-4 bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                            <x-heroicon-o-user class="h-6 w-6 text-blue-600" />
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm font-medium">Penanggungjawab</div>
                            <div class="text-gray-900 font-semibold">{{ $record->projectManager->name ?? 'Belum ditentukan' }}</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4 bg-gray-50 rounded-xl p-4 border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="flex-shrink-0 w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <x-heroicon-o-calendar class="h-6 w-6 text-emerald-600" />
                        </div>
                        <div>
                            <div class="text-gray-500 text-sm font-medium">Periode Project</div>
                            <div class="text-gray-900 font-semibold text-sm">
                                {{ $record->tanggal_mulai?->format('d M Y') }} - {{ $record->tanggal_selesai?->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bagian Kanan - Progress & Status -->
            <div class="flex flex-col items-center lg:items-end space-y-6">
                <!-- Progress Circle -->
                <div class="relative">
                    <div class="w-32 h-32 relative">
                        <svg class="w-32 h-32 transform -rotate-90" viewBox="0 0 100 100">
                            <circle cx="50" cy="50" r="45" stroke="#f1f5f9" stroke-width="4" fill="none" />
                            <circle cx="50" cy="50" r="45" stroke="url(#progressGradient)" stroke-width="4" fill="none" 
                                    stroke-linecap="round" stroke-dasharray="283" 
                                    stroke-dashoffset="{{ 283 - (283 * $record->progress_percentage / 100) }}" 
                                    class="transition-all duration-1000 ease-out" />
                            <defs>
                                <linearGradient id="progressGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" style="stop-color:#3b82f6;stop-opacity:1" />
                                    <stop offset="100%" style="stop-color:#6366f1;stop-opacity:1" />
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-900">{{ $record->progress_percentage }}%</div>
                                <div class="text-gray-500 text-sm font-medium">Progress</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Status Badge -->
                <div class="text-center lg:text-right">
                    <div class="text-gray-500 text-sm font-medium mb-2">Status Project</div>
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold 
                               bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-lg
                               hover:shadow-xl transition-shadow">
                        <div class="w-2 h-2 bg-white rounded-full mr-2"></div>
                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex justify-between items-center mb-3">
                <span class="text-gray-600 font-medium">Progress Keseluruhan</span>
                <span class="text-gray-900 font-semibold">{{ $record->progress_percentage }}% Complete</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 h-3 rounded-full transition-all duration-1000 ease-out shadow-sm relative" 
                     style="width: {{ $record->progress_percentage }}%">
                    <div class="absolute inset-0 bg-white/20 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Project Details Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-6">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                            <x-heroicon-o-document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Detail Project</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Left Column --}}
                        <div class="space-y-6">
                            {{-- Deskripsi --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                    <x-heroicon-o-document-text class="h-4 w-4 mr-2 text-blue-500" />
                                    Deskripsi Project
                                </h4>
                                <div class="prose dark:prose-invert max-w-none">
                                    <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">{{ $record->deskripsi }}</p>
                                </div>
                            </div>

                            {{-- Tujuan Utama --}}
                            @if($record->tujuan_utama)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                        <x-heroicon-o-trophy class="h-4 w-4 mr-2 text-yellow-500" />
                                        Tujuan Utama
                                    </h4>
                                    <div class="prose dark:prose-invert max-w-none">
                                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">{{ $record->tujuan_utama }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Target Audience --}}
                            @if($record->target_audience)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                        <x-heroicon-o-users class="h-4 w-4 mr-2 text-purple-500" />
                                        Target Audience
                                    </h4>
                                    <div class="prose dark:prose-invert max-w-none">
                                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">{{ $record->target_audience }}</p>
                                    </div>
                                </div>
                            @endif

                            {{-- Expected Outcomes --}}
                            @if($record->expected_outcomes)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                        <x-heroicon-o-light-bulb class="h-4 w-4 mr-2 text-orange-500" />
                                        Expected Outcomes
                                    </h4>
                                    <div class="prose dark:prose-invert max-w-none">
                                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed text-sm">{{ $record->expected_outcomes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right Column --}}
                        <div class="space-y-6">
                            {{-- Target Metrics --}}
                            @if($record->target_metrics && count($record->target_metrics) > 0)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                        <x-heroicon-o-chart-bar class="h-4 w-4 mr-2 text-green-500" />
                                        Target Metrics
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach($record->target_metrics as $metric)
                                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex-1">
                                                        <h5 class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ $metric['metric'] ?? 'N/A' }}</h5>
                                                        <p class="text-sm font-bold text-green-600 dark:text-green-400 mt-1">{{ $metric['target'] ?? 'N/A' }}</p>
                                                    </div>
                                                    @if(isset($metric['timeframe']))
                                                        <div class="text-right">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200">
                                                                {{ $metric['timeframe'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Deliverables --}}
                            @if($record->deliverables && count($record->deliverables) > 0)
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3 flex items-center">
                                        <x-heroicon-o-document-check class="h-4 w-4 mr-2 text-indigo-500" />
                                        Deliverables
                                    </h4>
                                    <div class="space-y-3">
                                        @foreach($record->deliverables as $deliverable)
                                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-3">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <h5 class="text-xs font-medium text-gray-900 dark:text-gray-100">{{ $deliverable['title'] ?? 'N/A' }}</h5>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            Type: <span class="font-medium">{{ ucfirst($deliverable['type'] ?? 'N/A') }}</span>
                                                        </p>
                                                        @if(isset($deliverable['description']) && !empty($deliverable['description']))
                                                            <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $deliverable['description'] }}</p>
                                                        @endif
                                                    </div>
                                                    @if(isset($deliverable['quantity']))
                                                        <div class="ml-2">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                                {{ $deliverable['quantity'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tasks Section --}}
@if($this->canViewTasks())
    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tasks</h3>
            
            {{-- Tombol Create Task dengan Validasi --}}
            @if($this->canCreateTask())
                <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('create', ['project_id' => $record->id]) }}" 
                   class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <x-heroicon-o-plus class="h-4 w-4 mr-1" />
                    Tambah Task
                </a>
            @elseif($record->status === 'draft' || $record->status === 'planning')
                <div class="text-sm text-yellow-600 bg-yellow-100 px-3 py-2 rounded-lg">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4 inline mr-1" />
                    Project harus disetujui dulu sebelum bisa menambah task
                </div>
            @elseif($record->status === 'completed')
                <div class="text-sm text-gray-600 bg-gray-100 px-3 py-2 rounded-lg">
                    <x-heroicon-o-lock-closed class="h-4 w-4 inline mr-1" />
                    Project sudah selesai, tidak bisa menambah task
                </div>
            @else
                <div class="text-sm text-gray-600 bg-gray-100 px-3 py-2 rounded-lg">
                    <x-heroicon-o-no-symbol class="h-4 w-4 inline mr-1" />
                    Tidak bisa menambah task
                </div>
            @endif
        </div>

        {{-- Task Statistics --}}
        @php
            $tasks = $record->tasks;
            $taskStats = [
                'total' => $tasks->count(),
                'todo' => $tasks->where('status', 'todo')->count(),
                'in_progress' => $tasks->where('status', 'in_progress')->count(),
                'review' => $tasks->where('status', 'review')->count(),
                'done' => $tasks->where('status', 'done')->count(),
                'blocked' => $tasks->where('status', 'blocked')->count(),
            ];
        @endphp

        @if($taskStats['total'] > 0)
            {{-- Task Stats Grid --}}
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $taskStats['total'] }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Total</div>
                </div>
                <div class="bg-blue-100 dark:bg-blue-800 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ $taskStats['todo'] }}</div>
                    <div class="text-sm text-blue-500 dark:text-blue-400">To Do</div>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-800 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-300">{{ $taskStats['in_progress'] }}</div>
                    <div class="text-sm text-yellow-500 dark:text-yellow-400">In Progress</div>
                </div>
                <div class="bg-purple-100 dark:bg-purple-800 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-purple-600 dark:text-purple-300">{{ $taskStats['review'] }}</div>
                    <div class="text-sm text-purple-500 dark:text-purple-400">Review</div>
                </div>
                <div class="bg-green-100 dark:bg-green-800 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-300">{{ $taskStats['done'] }}</div>
                    <div class="text-sm text-green-500 dark:text-green-400">Done</div>
                </div>
                <div class="bg-red-100 dark:bg-red-800 rounded-lg p-4 text-center">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-300">{{ $taskStats['blocked'] }}</div>
                    <div class="text-sm text-red-500 dark:text-red-400">Blocked</div>
                </div>
            </div>

            {{-- Tasks List --}}
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($tasks->take(5) as $task)
                    <div class="py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('view', ['record' => $task]) }}" 
                                       class="hover:text-blue-600">
                                        {{ $task->nama_task }}
                                    </a>
                                </h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $task->deskripsi }}</p>
                                <div class="flex items-center space-x-4 mt-2">
                                    <span class="text-xs text-gray-500">
                                        Assigned: {{ $task->assignedTo?->name ?? 'Unassigned' }}
                                    </span>
                                    @if($task->tanggal_deadline)
                                        <span class="text-xs text-gray-500">
                                            Due: {{ $task->tanggal_deadline->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                       {{ match($task->status) {
                                           'todo' => 'bg-blue-100 text-blue-800',
                                           'in_progress' => 'bg-yellow-100 text-yellow-800', 
                                           'review' => 'bg-purple-100 text-purple-800',
                                           'done' => 'bg-green-100 text-green-800',
                                           'blocked' => 'bg-red-100 text-red-800',
                                           default => 'bg-gray-100 text-gray-800'
                                       } }}">
                                    {{ ucfirst($task->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($tasks->count() > 5)
                <div class="mt-4 text-center">
                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('index', ['tableFilters[project][value]' => $record->id]) }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat semua {{ $tasks->count() }} tasks →
                    </a>
                </div>
            @endif
        @else
            {{-- No Tasks State --}}
            <div class="text-center py-8">
                <x-heroicon-o-clipboard-document-list class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Belum Ada Task</h4>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    @if($this->canCreateTask())
                        Mulai dengan menambahkan task pertama untuk project ini.
                    @else
                        Task akan muncul di sini setelah project disetujui.
                    @endif
                </p>
            </div>
        @endif
    </div>
@endif


            </div>

            {{-- Right Column - Actions & Info --}}
            <div class="space-y-6">
                {{-- Actions Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tindakan</h3>
                    <div class="space-y-3">
                        {{-- Tindakan untuk Redaksi/Admin saat status 'draft' --}}
                        @if ($this->canApprove())
                            <button wire:click="approveAction" 
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm">
                                <x-heroicon-o-check-circle class="h-4 w-4 mr-2" />
                                Setujui Project
                            </button>
                        @endif

                        @if ($this->canReject())
                            <button wire:click="rejectAction" 
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700 transition-colors shadow-sm">
                                <x-heroicon-o-x-circle class="h-4 w-4 mr-2" />
                                Tolak Project
                            </button>
                        @endif

                        {{-- Tindakan untuk Project Manager saat status 'planning' --}}
                        @if ($this->canStartProject())
                            <button wire:click="startProjectAction" 
                                class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-3 text-sm font-medium text-white hover:bg-blue-700 transition-colors shadow-sm">
                                <x-heroicon-o-play class="h-4 w-4 mr-2" />
                                    Mulai Project
                            </button>
                        @endif
                        
                        {{-- Tindakan untuk Project Manager saat status 'in_progress' atau 'review' --}}
                        @if ($this->canCompleteProject())
                            <button wire:click="completeProjectAction" 
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm">
                                <x-heroicon-o-check-badge class="h-4 w-4 mr-2" />
                                Selesaikan Project
                            </button>
                        @endif
                        
                        {{-- Tombol Edit --}}
                        @if($this->canEdit())
                            <a href="{{ \App\Filament\Team\Resources\ProjectResource::getUrl('edit', ['record' => $record]) }}" 
                               class="w-full inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                <x-heroicon-o-pencil class="h-4 w-4 mr-2" />
                                Edit Project
                            </a>
                        @endif
                    </div>
                </div>

{{-- Budget Summary Card --}}
@if($record->hasBudget())
    <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Ringkasan Anggaran</h3>
        
        {{-- Budget Overview --}}
        <div class="space-y-4 mb-6">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Total Anggaran Disetujui:</span>
                <span class="font-semibold text-green-600">Rp {{ number_format($record->total_approved_budget, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Total Terpakai:</span>
                <span class="font-semibold text-orange-600">Rp {{ number_format($record->total_used_budget, 0, ',', '.') }}</span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500">Sisa Anggaran:</span>
                <span class="font-semibold {{ $record->remaining_budget >= 0 ? 'text-blue-600' : 'text-red-600' }}">
                    Rp {{ number_format($record->remaining_budget, 0, ',', '.') }}
                </span>
            </div>
        </div>
         {{-- Budget Usage Progress Bar --}}
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Penggunaan Anggaran</span>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $record->budget_usage_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                <div class="h-3 rounded-full transition-all duration-300 {{ $record->budget_usage_percentage > 90 ? 'bg-red-500' : ($record->budget_usage_percentage > 75 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                     style="width: {{ min($record->budget_usage_percentage, 100) }}%"></div>
            </div>
        </div>

        {{-- Approved Budget Requests List dengan Tombol Realisasi --}}
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Pengajuan Anggaran Disetujui</h4>
            <div class="space-y-2">
                @foreach($record->getApprovedBudgetRequests() as $pengajuan)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $pengajuan->judul_pengajuan }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $pengajuan->createdBy->name }} • {{ $pengajuan->keuangan_approved_at?->format('d M Y') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-green-600">Rp {{ number_format($pengajuan->total_anggaran, 0, ',', '.') }}</p>
                                @if($pengajuan->kategori)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200">
                                        {{ ucfirst($pengajuan->kategori) }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress Realisasi --}}
                        @php
                            $totalRealized = collect($pengajuan->detail_items)->sum(function ($item) {
                                return $item['realisasi']['total_actual'] ?? 0;
                            });
                            $realizationPercentage = $pengajuan->total_anggaran > 0 ? round(($totalRealized / $pengajuan->total_anggaran) * 100, 2) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="flex justify-between text-xs text-gray-500 mb-1">
                                <span>Realisasi: Rp {{ number_format($totalRealized, 0, ',', '.') }}</span>
                                <span>{{ $realizationPercentage }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $realizationPercentage >= 100 ? 'bg-green-500' : 'bg-blue-500' }}" 
                                     style="width: {{ min($realizationPercentage, 100) }}%"></div>
                            </div>
                        </div>

                        {{-- Detail Items dengan Tombol Realisasi --}}
                        @if(count($pengajuan->detail_items) > 0)
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-3">
                                <h5 class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Detail Items:</h5>
                                <div class="space-y-2">
                                    @foreach($pengajuan->detail_items as $index => $item)
                                        <div class="flex justify-between items-center bg-white dark:bg-gray-600 rounded px-3 py-2">
                                            <div class="flex-1">
                                                <p class="text-xs font-medium text-gray-800 dark:text-gray-200">
                                                    {{ $item['item_name'] ?? $item['nama_item'] ?? 'Item' }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ $item['quantity'] ?? $item['kuantitas'] ?? 1 }}x 
                                                    @ Rp {{ number_format($item['unit_price'] ?? $item['harga_satuan'] ?? 0, 0, ',', '.') }}
                                                </p>
                                            </div>
                                            
                                            <div class="text-right flex items-center space-x-2">
                                                <div>
                                                    <p class="text-xs font-semibold text-gray-800 dark:text-gray-200">
                                                        Rp {{ number_format($item['total_price'] ?? 0, 0, ',', '.') }}
                                                    </p>
                                                    @if(isset($item['realisasi']) && $item['realisasi']['status'] === 'realized')
                                                        <p class="text-xs text-green-600">
                                                            ✓ Rp {{ number_format($item['realisasi']['total_actual'], 0, ',', '.') }}
                                                        </p>
                                                    @endif
                                                </div>
                                                
                                                {{-- Tombol Input Realisasi - Hanya untuk PM --}}
                                                @if($this->canInputRealization() && (!isset($item['realisasi']) || $item['realisasi']['status'] !== 'realized'))
                                                    <button 
                                                        wire:click="inputRealizationModal({{ $pengajuan->id }}, {{ $index }})"
                                                        class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-1 rounded text-xs font-medium transition-colors">
                                                        Input Realisasi
                                                    </button>
                                                @elseif(isset($item['realisasi']) && $item['realisasi']['status'] === 'realized')
                                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                                                        Sudah Direalisasi
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Action Button --}}
        @if(auth()->user()->can('create', \App\Models\PengajuanAnggaran::class))
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                <a href="{{ \App\Filament\Team\Resources\PengajuanAnggaranResource::getUrl('create', ['project_id' => $record->id]) }}" 
                   class="w-full inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                    <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                    Ajukan Anggaran Tambahan
                </a>
            </div>
        @endif
    </div>
@else
    {{-- No Budget Card --}}
    <div class="rounded-xl bg-gray-50 p-6 border border-gray-200 dark:bg-gray-700 dark:border-gray-600">
        <div class="text-center">
            <x-heroicon-o-currency-dollar class="w-12 h-12 mx-auto mb-3 text-gray-400" />
            <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Belum Ada Anggaran</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Project ini belum memiliki pengajuan anggaran yang disetujui</p>
            
            @if(auth()->user()->can('create', \App\Models\PengajuanAnggaran::class))
                <a href="{{ \App\Filament\Team\Resources\PengajuanAnggaranResource::getUrl('create', ['project_id' => $record->id]) }}" 
                   class="inline-flex items-center px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                    <x-heroicon-o-plus class="h-3 w-3 mr-1" />
                    Ajukan Anggaran
                </a>
            @endif
        </div>
    </div>
@endif

                {{-- Team Members Card --}}
                @if($record->team_members && count($record->team_members) > 0)
                    <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tim Project</h3>
                        <div class="space-y-3">
                            @foreach($record->getTeamMemberUsers() as $member)
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-blue-600">{{ substr($member->name, 0, 1) }}</span>
                                    </div>
                                    <span class="text-gray-900 dark:text-gray-100">{{ $member->name }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>


@if($showRealizationModal && $selectedPengajuan)
    <div class="fixed inset-0 z-50 overflow-y-auto" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Input Realisasi Anggaran</h3>
                    <button wire:click="closeRealizationModal" class="text-gray-400 hover:text-gray-600">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                @if($selectedPengajuan && isset($selectedPengajuan->detail_items[$selectedItemIndex]))
                    @php $selectedItem = $selectedPengajuan->detail_items[$selectedItemIndex]; @endphp
                    
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <p class="text-sm font-medium">{{ $selectedItem['item_name'] ?? $selectedItem['nama_item'] ?? 'Item' }}</p>
                        <p class="text-xs text-gray-500">
                            Budget: {{ $selectedItem['quantity'] ?? $selectedItem['kuantitas'] ?? 1 }}x @ Rp {{ number_format($selectedItem['unit_price'] ?? $selectedItem['harga_satuan'] ?? 0, 0, ',', '.') }}
                            = Rp {{ number_format($selectedItem['total_price'] ?? 0, 0, ',', '.') }}
                        </p>
                    </div>

                    <form wire:submit.prevent="submitRealization" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Realisasi</label>
                            <input type="date" wire:model="tanggal_realisasi" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendor/Supplier</label>
                            <input type="text" wire:model="vendor" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" 
                                   placeholder="Nama vendor/supplier" required>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qty Aktual</label>
                                <input type="number" wire:model.live="qty_actual" step="0.01" min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Aktual</label>
                                <input type="number" wire:model.live="harga_actual" step="0.01" min="0"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100" required>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Aktual</label>
                            <input type="number" wire:model="total_actual" step="0.01" min="0" readonly
                                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm dark:bg-gray-600 dark:border-gray-600 dark:text-gray-100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Bukti</label>
                            <input type="file" wire:model="bukti_files" multiple accept="image/*,.pdf"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">JPG, PNG, PDF - Max 5MB per file</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan</label>
                            <textarea wire:model="catatan_realisasi" rows="3"
                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                                      placeholder="Catatan tambahan (opsional)"></textarea>
                        </div>

                        <div class="flex justify-end space-x-2 pt-4">
                            <button type="button" wire:click="closeRealizationModal"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                Simpan Realisasi
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
@endif
</x-filament-panels::page>