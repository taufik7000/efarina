{{-- resources/views/filament/redaksi/pages/view-project-proposal.blade.php --}}

<x-filament-panels::page>
    <div class="proposal-view-wrapper space-y-6">
        {{-- Header Card with Gradient --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-600 via-purple-600 to-pink-600 p-8 text-white shadow-2xl">
            <div class="absolute inset-0 bg-black/20"></div>
            <div class="relative z-10">
                <div class="flex items-start justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-full bg-white/20 p-3 backdrop-blur-sm">
                                <x-heroicon-o-light-bulb class="h-8 w-8" />
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold">{{ $record->judul_proposal }}</h1>
                                <p class="text-white/80">Proposal ID: #{{ $record->id }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-4">
                            {{-- Status Badge --}}
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-500/20 text-yellow-200 border-yellow-400/30',
                                    'approved' => 'bg-green-500/20 text-green-200 border-green-400/30',
                                    'rejected' => 'bg-red-500/20 text-red-200 border-red-400/30',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-medium backdrop-blur-sm {{ $statusColors[$record->status] ?? 'bg-gray-500/20 text-gray-200 border-gray-400/30' }}">
                                @if($record->status === 'pending')
                                    <x-heroicon-m-clock class="mr-2 h-4 w-4" />
                                    Menunggu Review
                                @elseif($record->status === 'approved')
                                    <x-heroicon-m-check-circle class="mr-2 h-4 w-4" />
                                    Disetujui
                                @elseif($record->status === 'rejected')
                                    <x-heroicon-m-x-circle class="mr-2 h-4 w-4" />
                                    Ditolak
                                @endif
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

                            {{-- Category Badge --}}
                            <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1 text-sm font-medium text-white backdrop-blur-sm">
                                {{ ucfirst($record->kategori) }}
                            </span>
                        </div>
                    </div>

                    {{-- Creator Info --}}
                    <div class="text-right">
                        <div class="flex items-center space-x-3">
                            <div class="h-12 w-12 rounded-full bg-white/20 flex items-center justify-center backdrop-blur-sm">
                                <span class="text-lg font-bold">{{ substr($record->createdBy->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-semibold">{{ $record->createdBy->name }}</p>
                                <p class="text-sm text-white/70">{{ $record->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column - Main Content --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Description Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                            <x-heroicon-o-document-text class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Deskripsi Project</h3>
                    </div>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $record->deskripsi }}</p>
                    </div>
                </div>

                {{-- Objectives Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                            <x-heroicon-o-trophy class="h-5 w-5 text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tujuan Utama</h3>
                    </div>
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $record->tujuan_utama ?: $record->tujuan_project }}</p>
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
                    <div class="prose dark:prose-invert max-w-none">
                        <p class="text-gray-700 dark:text-gray-300 leading-relaxed">{{ $record->target_audience }}</p>
                    </div>
                </div>
                @endif

                {{-- Target Metrics Card --}}
                @if($record->target_metrics && count($record->target_metrics) > 0)
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/50">
                            <x-heroicon-o-chart-bar class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Target Metrics</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($record->target_metrics as $metric)
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $metric['metric'] ?? '' }}</h4>
                            <p class="text-lg font-bold text-blue-600 dark:text-blue-400">{{ $metric['target'] ?? '' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $metric['timeframe'] ?? '' }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Deliverables Card --}}
                @if($record->deliverables && count($record->deliverables) > 0)
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-orange-100 p-2 dark:bg-orange-900/50">
                            <x-heroicon-o-inbox-stack class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Deliverables</h3>
                    </div>
                    <div class="space-y-3">
                        @foreach($record->deliverables as $deliverable)
                        <div class="flex items-start space-x-3 rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <span class="text-2xl">
                                @switch($deliverable['type'] ?? '')
                                    @case('article') 📝 @break
                                    @case('video') 🎥 @break
                                    @case('podcast') 🎙️ @break
                                    @case('infographic') 📊 @break
                                    @case('report') 📋 @break
                                    @case('ebook') 📚 @break
                                    @case('webinar') 💻 @break
                                    @case('campaign') 📢 @break
                                    @default ❓
                                @endswitch
                            </span>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $deliverable['title'] ?? '' }}</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $deliverable['quantity'] ?? '' }}</p>
                                @if(!empty($deliverable['description']))
                                <p class="text-sm text-gray-500 dark:text-gray-500 mt-1">{{ $deliverable['description'] }}</p>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Review Section (if reviewed) --}}
                @if($record->reviewed_by && $record->catatan_review)
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center space-x-3 mb-4">
                        <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/50">
                            @if($record->status === 'approved')
                                <x-heroicon-o-check-circle class="h-5 w-5 text-green-600 dark:text-green-400" />
                            @else
                                <x-heroicon-o-x-circle class="h-5 w-5 text-red-600 dark:text-red-400" />
                            @endif
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            {{ $record->status === 'approved' ? 'Catatan Approval' : 'Feedback Penolakan' }}
                        </h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                            <span>Oleh: <span class="font-medium">{{ $record->reviewedBy->name }}</span></span>
                            <span>•</span>
                            <span>{{ $record->reviewed_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-700/50">
                            <p class="text-gray-700 dark:text-gray-300">{{ $record->catatan_review }}</p>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Project Link (if created) --}}
                @if($record->hasProject())
                <div class="rounded-xl bg-gradient-to-r from-green-50 to-emerald-50 p-6 border border-green-200 dark:from-green-900/20 dark:to-emerald-900/20 dark:border-green-700">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/50">
                                <x-heroicon-o-rocket-launch class="h-5 w-5 text-green-600 dark:text-green-400" />
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-green-900 dark:text-green-100">Project Telah Dibuat</h3>
                                <p class="text-sm text-green-700 dark:text-green-300">Proposal ini telah diubah menjadi project aktif</p>
                            </div>
                        </div>
                        <a href="/redaksi/projects/{{ $record->project_id }}" 
                           class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                            <x-heroicon-m-arrow-top-right-on-square class="mr-2 h-4 w-4" />
                            Lihat Project
                        </a>
                    </div>
                </div>
                @endif
            </div>

            {{-- Right Column - Sidebar --}}
            <div class="space-y-6">
                {{-- Quick Stats --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Informasi Detail</h3>
                    <div class="space-y-4">
                        {{-- Estimasi Durasi --}}
                        @if($record->estimasi_durasi_hari)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-calendar-days class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Estimasi Durasi</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->estimasi_durasi_hari }} hari</span>
                        </div>
                        @endif

                        {{-- Estimasi Budget --}}
                        @if($record->estimasi_budget)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-banknotes class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Estimasi Budget</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Rp {{ number_format($record->estimasi_budget, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        {{-- Created Date --}}
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <x-heroicon-m-clock class="h-4 w-4 text-gray-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Dibuat</span>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $record->created_at->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Quick Actions (for pending proposals) --}}
                @if($record->status === 'pending')
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        {{-- Quick Approve Button --}}
                        <button type="button" 
                                wire:click="mountAction('approve')"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-3 text-sm font-medium text-white hover:bg-green-700 transition-colors shadow-sm">
                            <x-heroicon-m-check class="mr-2 h-4 w-4" />
                            Setujui Proposal
                        </button>

                        {{-- Quick Reject Button --}}
                        <button type="button" 
                                wire:click="mountAction('reject')"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-3 text-sm font-medium text-white hover:bg-red-700 transition-colors shadow-sm">
                            <x-heroicon-m-x-mark class="mr-2 h-4 w-4" />
                            Tolak Proposal
                        </button>

                        {{-- Edit Button --}}
                        <a href="{{ route('filament.redaksi.resources.project-proposals.edit', $record) }}" 
                           class="w-full inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <x-heroicon-m-pencil class="mr-2 h-4 w-4" />
                            Edit/Review
                        </a>
                    </div>
                </div>
                @endif

                {{-- Timeline Card --}}
                <div class="rounded-xl bg-white p-6 shadow-lg border border-gray-100 dark:bg-gray-800 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Timeline</h3>
                    <div class="space-y-4">
                        {{-- Created --}}
                        <div class="flex items-start space-x-3">
                            <div class="mt-1 h-2 w-2 rounded-full bg-blue-500"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Proposal Dibuat</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->created_at->format('d M Y, H:i') }}</p>
                            </div>
                        </div>

                        {{-- Reviewed --}}
                        @if($record->reviewed_at)
                        <div class="flex items-start space-x-3">
                            <div class="mt-1 h-2 w-2 rounded-full {{ $record->status === 'approved' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $record->status === 'approved' ? 'Disetujui' : 'Ditolak' }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $record->reviewed_at->format('d M Y, H:i') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">oleh {{ $record->reviewedBy->name }}</p>
                            </div>
                        </div>
                        @endif

                        {{-- Project Created --}}
                        @if($record->hasProject())
                        <div class="flex items-start space-x-3">
                            <div class="mt-1 h-2 w-2 rounded-full bg-purple-500"></div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Project Dibuat</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Project ID: #{{ $record->project_id }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>