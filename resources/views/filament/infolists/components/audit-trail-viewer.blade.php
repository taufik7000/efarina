@php
    $histories = $getRecord()->transaksiHistories ?? [];
@endphp

<div class="flow-root">
    <ul role="list" class="-mb-8">
        @forelse ($histories->sortByDesc('created_at') as $history)
            <li>
                <div class="relative pb-8">
                    @if (!$loop->last)
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                    @endif
                    <div class="relative flex space-x-3 items-start">
                        {{-- Status Icon --}}
                        <div>
                            @php
                                $iconClass = 'h-5 w-5 text-white';
                                $iconBg = 'bg-primary-500';
                                $icon = 'heroicon-s-check';
                                
                                // Determine icon and color based on action type
                                if (str_contains(strtolower($history->notes ?? ''), 'dibuat') || 
                                    str_contains(strtolower($history->action ?? ''), 'dibuat')) {
                                    $iconBg = 'bg-blue-500';
                                    $icon = 'heroicon-s-plus';
                                } elseif (str_contains(strtolower($history->notes ?? ''), 'diperbarui') || 
                                          str_contains(strtolower($history->action ?? ''), 'diperbarui')) {
                                    $iconBg = 'bg-yellow-500';
                                    $icon = 'heroicon-s-pencil';
                                } elseif (str_contains(strtolower($history->notes ?? ''), 'status') || 
                                          str_contains(strtolower($history->action ?? ''), 'status')) {
                                    $iconBg = 'bg-purple-500';
                                    $icon = 'heroicon-s-arrow-path';
                                } elseif (str_contains(strtolower($history->notes ?? ''), 'pembayaran') || 
                                          str_contains(strtolower($history->action ?? ''), 'completed')) {
                                    $iconBg = 'bg-green-500';
                                    $icon = 'heroicon-s-banknotes';
                                } elseif (str_contains(strtolower($history->notes ?? ''), 'ditolak') || 
                                          str_contains(strtolower($history->action ?? ''), 'reject')) {
                                    $iconBg = 'bg-red-500';
                                    $icon = 'heroicon-s-x-mark';
                                } elseif (str_contains(strtolower($history->notes ?? ''), 'dihapus') || 
                                          str_contains(strtolower($history->action ?? ''), 'dihapus')) {
                                    $iconBg = 'bg-gray-500';
                                    $icon = 'heroicon-s-trash';
                                }
                            @endphp
                            
                            <span class="h-8 w-8 rounded-full {{ $iconBg }} flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                @if($icon === 'heroicon-s-plus')
                                    <x-heroicon-s-plus class="{{ $iconClass }}" />
                                @elseif($icon === 'heroicon-s-pencil')
                                    <x-heroicon-s-pencil class="{{ $iconClass }}" />
                                @elseif($icon === 'heroicon-s-arrow-path')
                                    <x-heroicon-s-arrow-path class="{{ $iconClass }}" />
                                @elseif($icon === 'heroicon-s-banknotes')
                                    <x-heroicon-s-banknotes class="{{ $iconClass }}" />
                                @elseif($icon === 'heroicon-s-x-mark')
                                    <x-heroicon-s-x-mark class="{{ $iconClass }}" />
                                @elseif($icon === 'heroicon-s-trash')
                                    <x-heroicon-s-trash class="{{ $iconClass }}" />
                                @else
                                    <x-heroicon-s-check class="{{ $iconClass }}" />
                                @endif
                            </span>
                        </div>

                        {{-- Content --}}
                        <div class="min-w-0 flex-1 pt-1.5">
                            {{-- Action Title --}}
                            <div class="flex items-center gap-2 mb-1">
                                @php
                                    $actionText = $history->action ?? 'UPDATE';
                                    $badgeColor = 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
                                    
                                    if (str_contains(strtolower($actionText), 'dibuat')) {
                                        $badgeColor = 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300';
                                    } elseif (str_contains(strtolower($actionText), 'status')) {
                                        $badgeColor = 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300';
                                    } elseif (str_contains(strtolower($actionText), 'completed')) {
                                        $badgeColor = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
                                    } elseif (str_contains(strtolower($actionText), 'reject')) {
                                        $badgeColor = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
                                    }
                                @endphp
                                
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $badgeColor }}">
                                    {{ $actionText }}
                                </span>
                                
                                {{-- Status Change Indicator --}}
                                @if($history->status_from && $history->status_to)
                                    <div class="flex items-center gap-1 text-xs">
                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-gray-600 dark:text-gray-400">
                                            {{ ucfirst($history->status_from) }}
                                        </span>
                                        <x-heroicon-s-arrow-right class="h-3 w-3 text-gray-400" />
                                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-300 rounded">
                                            {{ ucfirst($history->status_to) }}
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Detailed Information --}}
                            <div class="space-y-2">
                                {{-- Notes/Description --}}
                                @if($history->notes)
                                    <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                                        {{ $history->notes }}
                                    </p>
                                @endif

                                {{-- Actor Information --}}
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-s-user class="h-3 w-3" />
                                        <span class="font-medium">
                                            {{ $history->actionBy->name ?? $history->user->name ?? 'Sistem' }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center gap-1">
                                        <x-heroicon-s-clock class="h-3 w-3" />
                                        <span>{{ $history->created_at->format('d M Y, H:i:s') }}</span>
                                    </div>
                                    
                                    @if($history->ip_address)
                                        <div class="flex items-center gap-1">
                                            <x-heroicon-s-globe-alt class="h-3 w-3" />
                                            <span>{{ $history->ip_address }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- User Agent (if available) --}}
                                @if($history->user_agent)
                                    <details class="text-xs">
                                        <summary class="cursor-pointer text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            Detail Perangkat
                                        </summary>
                                        <div class="mt-1 p-2 bg-gray-50 dark:bg-gray-800 rounded text-gray-600 dark:text-gray-400 font-mono text-xs">
                                            {{ $history->user_agent }}
                                        </div>
                                    </details>
                                @endif

                                {{-- Additional contextual information --}}
                                @if($history->action_at && $history->action_at != $history->created_at)
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-medium">Waktu Aksi:</span> 
                                        {{ $history->action_at->format('d M Y, H:i:s') }}
                                    </div>
                                @endif
                            </div>

                            {{-- Separator line for better readability --}}
                            @if(!$loop->last)
                                <div class="mt-4 pt-2 border-t border-gray-100 dark:border-gray-700"></div>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li>
                <div class="text-center py-8">
                    <x-heroicon-o-document-text class="mx-auto h-12 w-12 text-gray-400" />
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Tidak ada riwayat untuk transaksi ini.
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Aktivitas akan muncul di sini saat transaksi diubah.
                    </p>
                </div>
            </li>
        @endforelse
    </ul>
</div>

{{-- Quick Stats (if there are histories) --}}
@if($histories->count() > 0)
    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-gray-500 dark:text-gray-400">Total Aktivitas:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-1">{{ $histories->count() }}</span>
            </div>
            <div>
                <span class="text-gray-500 dark:text-gray-400">Terakhir Update:</span>
                <span class="font-medium text-gray-900 dark:text-white ml-1">
                    {{ $histories->sortByDesc('created_at')->first()->created_at->diffForHumans() }}
                </span>
            </div>
        </div>
    </div>
@endif