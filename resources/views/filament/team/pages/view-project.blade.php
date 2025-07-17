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
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl text-white p-6 shadow-lg">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h1 class="text-3xl font-bold mb-2">{{ $record->nama_project }}</h1>
                    <div class="flex items-center space-x-4 text-white/80">
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-user class="h-5 w-5" />
                            <span>PM: {{ $record->projectManager->name ?? 'Belum ditentukan' }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <x-heroicon-o-calendar class="h-5 w-5" />
                            <span>{{ $record->tanggal_mulai?->format('d M Y') }} - {{ $record->tanggal_selesai?->format('d M Y') }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white">
                                {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold">{{ $record->progress_percentage }}%</div>
                    <div class="text-white/70 text-sm">Progress</div>
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
                <div class="rounded-xl bg-white shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                                <x-heroicon-o-clipboard-document-list class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Project Tasks</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                {{ $taskStats['total'] }} Tasks
                            </span>
                        </div>
                        
                        {{-- Create Task Button --}}
                        @if(auth()->user()->can('create', \App\Models\Task::class))
                            <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('create', ['project_id' => $record->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                                Tambah Task
                            </a>
                        @endif
                    </div>

                    {{-- Task Stats Grid --}}
                    @if($taskStats['total'] > 0)
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-gray-600 dark:text-gray-300">{{ $taskStats['todo'] }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">To Do</div>
                                </div>
                                <div class="bg-blue-100 dark:bg-blue-800 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-blue-600 dark:text-blue-300">{{ $taskStats['in_progress'] }}</div>
                                    <div class="text-sm text-blue-500 dark:text-blue-400">In Progress</div>
                                </div>
                                <div class="bg-yellow-100 dark:bg-yellow-800 rounded-lg p-4 text-center">
                                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-300">{{ $taskStats['review'] }}</div>
                                    <div class="text-sm text-yellow-500 dark:text-yellow-400">Review</div>
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
                        </div>
                    @endif

                    {{-- Tasks List --}}
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($tasks as $task)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-3">
                                            <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('view', ['record' => $task]) }}" 
                                               class="text-lg font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400">
                                                {{ $task->nama_task }}
                                            </a>
                                            
                                            {{-- Status Badge --}}
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ match($task->status) {
                                                       'todo' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                       'in_progress' => 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-200',
                                                       'review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200',
                                                       'done' => 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-200',
                                                       'blocked' => 'bg-red-100 text-red-800 dark:bg-red-800 dark:text-red-200',
                                                       default => 'bg-gray-100 text-gray-800'
                                                   } }}">
                                                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                            </span>

                                            {{-- Priority Badge --}}
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                   {{ match($task->prioritas) {
                                                       'low' => 'bg-gray-100 text-gray-600',
                                                       'medium' => 'bg-blue-100 text-blue-600',
                                                       'high' => 'bg-orange-100 text-orange-600',
                                                       'urgent' => 'bg-red-100 text-red-600',
                                                       default => 'bg-gray-100 text-gray-600'
                                                   } }}">
                                                {{ ucfirst($task->prioritas) }}
                                            </span>
                                        </div>
                                        
                                        {{-- Task Info --}}
                                        <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                            @if($task->assignedTo)
                                                <div class="flex items-center space-x-1">
                                                    <x-heroicon-o-user class="h-4 w-4" />
                                                    <span>{{ $task->assignedTo->name }}</span>
                                                </div>
                                            @endif
                                            
                                            @if($task->tanggal_deadline)
                                                <div class="flex items-center space-x-1 {{ $task->tanggal_deadline->isPast() ? 'text-red-500' : '' }}">
                                                    <x-heroicon-o-calendar class="h-4 w-4" />
                                                    <span>{{ $task->tanggal_deadline->format('d M Y') }}</span>
                                                    @if($task->tanggal_deadline->isPast())
                                                        <span class="text-red-500 font-medium">(Overdue)</span>
                                                    @endif
                                                </div>
                                            @endif
                                            
                                            <div class="flex items-center space-x-1">
                                                <x-heroicon-o-chart-bar class="h-4 w-4" />
                                                <span>{{ $task->progress_percentage }}%</span>
                                            </div>
                                        </div>
                                        
                                        {{-- Progress Bar --}}
                                        @if($task->progress_percentage > 0)
                                            <div class="mt-2 w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                                <div class="bg-blue-500 h-2 rounded-full transition-all duration-300"
                                                     style="width: {{ $task->progress_percentage }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Actions --}}
                                    <div class="flex items-center space-x-2 ml-4">
                                        @if(auth()->user()->can('update', $task))
                                            <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('edit', ['record' => $task]) }}" 
                                               class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                                <x-heroicon-o-pencil class="h-4 w-4" />
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <x-heroicon-o-clipboard-document-list class="w-12 h-12 mx-auto mb-4 text-gray-300" />
                                <p class="text-lg font-medium">Belum ada task</p>
                                <p class="text-sm">Mulai dengan membuat task pertama untuk project ini!</p>
                                
                                @if(auth()->user()->can('create', \App\Models\Task::class))
                                    <div class="mt-4">
                                        <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('create', ['project_id' => $record->id]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                            <x-heroicon-o-plus class="h-4 w-4 mr-2" />
                                            Buat Task Pertama
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endforelse
                    </div>

                    {{-- View All Tasks Link --}}
                    @if($tasks->count() > 0)
                        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                            <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('index', ['tableFilters[project_id][value]' => $record->id]) }}" 
                               class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200 font-medium">
                                Lihat semua {{ $tasks->count() }} tasks di halaman Task →
                            </a>
                        </div>
                    @endif
                </div>


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

        {{-- Approved Budget Requests List --}}
        <div>
            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Pengajuan Anggaran Disetujui</h4>
            <div class="space-y-2">
                @foreach($record->getApprovedBudgetRequests() as $pengajuan)
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="flex justify-between items-start">
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

                {{-- Project Info Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informasi Project</h3>
                    <div class="space-y-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Budget:</span>
                            <p class="text-gray-900 dark:text-gray-100">Rp {{ number_format($record->budget, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Prioritas:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                   {{ match($record->prioritas) {
                                       'low' => 'bg-gray-100 text-gray-800',
                                       'medium' => 'bg-blue-100 text-blue-800',
                                       'high' => 'bg-orange-100 text-orange-800',
                                       'urgent' => 'bg-red-100 text-red-800',
                                       default => 'bg-gray-100 text-gray-800'
                                   } }}">
                                {{ ucfirst($record->prioritas) }}
                            </span>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Dibuat oleh:</span>
                            <p class="text-gray-900 dark:text-gray-100">{{ $record->createdBy->name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Tanggal dibuat:</span>
                            <p class="text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        @if($record->catatan)
                            <div>
                                <span class="text-sm font-medium text-gray-500">Catatan:</span>
                                <p class="text-gray-900 dark:text-gray-100 text-sm">{{ $record->catatan }}</p>
                            </div>
                        @endif
                    </div>
                </div>

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
</x-filament-panels::page>