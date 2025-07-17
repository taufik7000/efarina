{{-- Menggunakan layout standar dari Filament --}}
<x-filament-panels::page>
    <div class="project-view-wrapper space-y-6">
        {{-- Header Card dengan Gradient --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-700 via-gray-900 to-black p-8 text-white shadow-2xl">
            <div class="absolute inset-0 bg-black/30"></div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="rounded-full bg-white/20 p-3 backdrop-blur-sm">
                                <x-heroicon-o-briefcase class="h-8 w-8" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold">{{ $record->nama_project }}</h1>
                                <p class="text-white/80">Project ID: #{{ $record->id }}</p>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Status Badge untuk Project --}}
                            @php
                                $statusInfo = match($record->status) {
                                    'draft' => ['color' => 'bg-yellow-500/20 text-yellow-200 border-yellow-400/30', 'icon' => 'heroicon-m-pencil-square', 'text' => 'Draft'],
                                    'planning' => ['color' => 'bg-cyan-500/20 text-cyan-200 border-cyan-400/30', 'icon' => 'heroicon-m-clipboard-document-list', 'text' => 'Planning'],
                                    'in_progress' => ['color' => 'bg-blue-500/20 text-blue-200 border-blue-400/30', 'icon' => 'heroicon-m-cog', 'text' => 'In Progress'],
                                    'review' => ['color' => 'bg-purple-500/20 text-purple-200 border-purple-400/30', 'icon' => 'heroicon-m-magnifying-glass', 'text' => 'Review'],
                                    'completed' => ['color' => 'bg-green-500/20 text-green-200 border-green-400/30', 'icon' => 'heroicon-m-check-circle', 'text' => 'Completed'],
                                    'cancelled' => ['color' => 'bg-red-500/20 text-red-200 border-red-400/30', 'icon' => 'heroicon-m-x-circle', 'text' => 'Cancelled'],
                                    default => ['color' => 'bg-gray-500/20 text-gray-200 border-gray-400/30', 'icon' => 'heroicon-m-question-mark-circle', 'text' => 'Unknown'],
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-medium backdrop-blur-sm {{ $statusInfo['color'] }}">
                                <x-dynamic-component :component="$statusInfo['icon']" class="mr-2 h-4 w-4" />
                                {{ $statusInfo['text'] }}
                            </span>

                            {{-- Priority Badge --}}
                            @php
                                $priorityColors = [
                                    'low' => 'bg-gray-500/20 text-gray-200',
                                    'medium' => 'bg-blue-500/20 text-blue-200',
                                    'high' => 'bg-orange-500/20 text-orange-200',
                                    'urgent' => 'bg-red-500/20 text-red-200',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium backdrop-blur-sm {{ $priorityColors[$record->prioritas] ?? 'bg-gray-500/20 text-gray-200' }}">
                                {{ ucfirst($record->prioritas) }} Priority
                            </span>
                        </div>
                    </div>

                    {{-- Project Manager Info --}}
                    <div class="mt-4 text-left md:mt-0 md:text-right">
                         @if($record->projectManager)
                        <div class="flex items-center justify-start md:justify-end space-x-3">
                            <div>
                                <p class="font-semibold">{{ $record->projectManager->name }}</p>
                                <p class="text-sm text-white/70">Project Manager</p>
                            </div>
                            <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                                <span class="text-lg font-bold">
                                    {{
                                        strtoupper(implode('', array_map(function($word) {
                                            return mb_substr($word, 0, 1);
                                        }, array_slice(explode(' ', $record->projectManager->name), 0, 2))))
                                    }}
                                </span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Deskripsi Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                            <x-heroicon-o-document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Deskripsi Project</h3>
                    </div>
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                        <p>{{ $record->deskripsi }}</p>
                    </div>
                </div>

                {{-- Tujuan Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                            <x-heroicon-o-trophy class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tujuan & Hasil yang Diharapkan</h3>
                    </div>
                     <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed space-y-4">
                        @if($record->tujuan_utama)
                            <div>
                                <h4 class="font-semibold text-gray-600 dark:text-gray-400">Tujuan Utama</h4>
                                <p>{{ $record->tujuan_utama }}</p>
                            </div>
                        @endif
                        @if($record->expected_outcomes)
                           <div>
                                <h4 class="font-semibold text-gray-600 dark:text-gray-400">Hasil yang Diharapkan</h4>
                                <p>{{ $record->expected_outcomes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Target Audience Card --}}
                @if($record->target_audience)
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/50">
                            <x-heroicon-o-users class="h-5 w-5 text-purple-600 dark:text-purple-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Target Audience</h3>
                    </div>
                    <div class="prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 leading-relaxed">
                        <p>{{ $record->target_audience }}</p>
                    </div>
                </div>
                @endif

                {{-- Target Metrics Card --}}
                @if($record->target_metrics && count(array_filter($record->target_metrics)) > 0)
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-cyan-100 p-2 dark:bg-cyan-900/50">
                            <x-heroicon-o-chart-bar class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Target Metrics</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($record->target_metrics as $metric)
                         @if(!empty($metric['metric']))
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $metric['metric'] }}</h4>
                            <p class="text-xl font-bold text-cyan-600 dark:text-cyan-400 mt-1">{{ $metric['target'] }}</p>
                            @if(!empty($metric['timeframe']))
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $metric['timeframe'] }}</p>
                            @endif
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Deliverables Card --}}
                @if($record->deliverables && count(array_filter($record->deliverables)) > 0)
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/50">
                            <x-heroicon-o-inbox-stack class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Deliverables</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($record->deliverables as $deliverable)
                        @if(!empty($deliverable['title']))
                        <div class="flex items-start space-x-4 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <span class="text-2xl mt-1">
                                {{ match($deliverable['type'] ?? 'other') { 'article' => '📝', 'video' => '🎥', 'podcast' => '🎙️', 'infographic' => '📊', 'report' => '📋', 'ebook' => '📚', 'webinar' => '💻', 'campaign' => '📢', default => '❓' } }}
                            </span>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $deliverable['title'] }}</h4>
                                @if(!empty($deliverable['quantity']))
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $deliverable['quantity'] }}</p>
                                @endif
                                @if(!empty($deliverable['description']))
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">{{ $deliverable['description'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                
                {{-- Catatan Project --}}
                @if($record->catatan)
                 <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-yellow-100 p-2 dark:bg-yellow-900/50">
                            <x-heroicon-o-command-line class="h-5 w-5 text-yellow-600 dark:text-yellow-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Catatan Project</h3>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                        <p class="text-gray-700 dark:text-gray-300">{{ $record->catatan }}</p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column - Sidebar --}}
            <div class="space-y-6">
                 {{-- Quick Actions --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tindakan</h3>
                    <div class="space-y-3">
                        @php $user = auth()->user(); @endphp

                        {{-- Tindakan untuk Redaksi/Admin saat status 'draft' --}}
                        @if ($user->hasRole(['redaksi', 'admin']) && $record->status === 'draft')
                            {{ $this->getCachedAction('approve') }}
                            {{ $this->getCachedAction('reject') }}
                        @endif

                        {{-- Tindakan untuk Project Manager saat status 'planning' --}}
                        @if ($record->project_manager_id === $user->id && $record->status === 'planning')
                            {{ $this->getCachedAction('start_project') }}
                        @endif
                        
                        {{-- Tindakan untuk Project Manager saat status 'in_progress' atau 'review' --}}
                        @if ($record->project_manager_id === $user->id && in_array($record->status, ['in_progress', 'review']))
                            {{ $this->getCachedAction('complete_project') }}
                        @endif
                        
                        {{-- Tombol Edit, terlihat sesuai kondisi di resource --}}
                        @if(
                            $user->hasRole(['admin', 'redaksi']) ||
                            ($record->created_by === $user->id && $record->status === 'draft') ||
                            $record->project_manager_id === $user->id
                        )
                        <a href="{{ \App\Filament\Team\Resources\ProjectResource::getUrl('edit', ['record' => $record]) }}" 
                           class="w-full inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <x-heroicon-m-pencil class="mr-2 h-4 w-4" />
                            Edit Project
                        </a>
                        @endif
                    </div>
                </div>

                {{-- Detail Info --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informasi Detail</h3>
                    <div class="space-y-4">
                        {{-- Team Members --}}
                        @if($record->teamMembers && $record->teamMembers->count() > 0)
                         <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-users class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Anggota Tim</span>
                            </div>
                            <div class="flex -space-x-2">
                                @foreach($record->teamMembers->take(3) as $member)
                                <div class="h-7 w-7 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-xs font-bold text-gray-600 dark:text-gray-200 ring-2 ring-white dark:ring-gray-800">
                                     {{
                                        strtoupper(implode('', array_map(function($word) {
                                            return mb_substr($word, 0, 1);
                                        }, array_slice(explode(' ', $member->name), 0, 2))))
                                    }}
                                </div>
                                @endforeach
                                @if($record->teamMembers->count() > 3)
                                <div class="h-7 w-7 rounded-full bg-gray-300 dark:bg-gray-500 flex items-center justify-center text-xs font-bold text-gray-700 dark:text-gray-100 ring-2 ring-white dark:ring-gray-800">
                                    +{{ $record->teamMembers->count() - 3 }}
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        {{-- Tanggal Mulai --}}
                        @if($record->tanggal_mulai)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-calendar-days class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tanggal Mulai</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->tanggal_mulai->format('d M Y') }}</span>
                        </div>
                        @endif

                        {{-- Tanggal Selesai --}}
                         @if($record->tanggal_selesai)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-flag class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tanggal Selesai</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->tanggal_selesai->format('d M Y') }}</span>
                        </div>
                        @endif

                        {{-- Pembuat --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-user-circle class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Dibuat oleh</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->createdBy->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Timeline Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Riwayat Project</h3>
                    <div class="space-y-4">
                        {{-- Created --}}
                        <div class="flex items-start space-x-3">
                            <div class="mt-1 flex-shrink-0 h-2 w-2 rounded-full bg-blue-500"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Project Dibuat</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                         {{-- Started --}}
                        @if($record->status !== 'draft' && $record->status !== 'planning' && $record->tanggal_mulai)
                        <div class="flex items-start space-x-3">
                            <div class="mt-1 flex-shrink-0 h-2 w-2 rounded-full bg-green-500"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Project Dimulai</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->tanggal_mulai->format('d M Y') }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Completed --}}
                        @if($record->status === 'completed' && $record->tanggal_selesai)
                        <div class="flex items-start space-x-3">
                            <div class="mt-1 flex-shrink-0 h-2 w-2 rounded-full bg-purple-500"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Project Selesai</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->tanggal_selesai->format('d M Y') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>