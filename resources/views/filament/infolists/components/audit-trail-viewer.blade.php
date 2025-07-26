@php
    $histories = $getRecord()->transaksiHistories ?? [];
@endphp

<div class="flow-root">
    <ul role="list" class="-mb-8">
        @forelse ($histories as $history)
            <li>
                <div class="relative pb-8">
                    @if (!$loop->last)
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                    @endif
                    <div class="relative flex space-x-3 items-start">
                        <div>
                            <span class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center ring-8 ring-white dark:ring-gray-900">
                                <x-heroicon-s-check class="h-5 w-5 text-white" />
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1.5">
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $history->action }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                oleh {{ $history->user->name ?? 'Sistem' }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $history->created_at->format('d M Y, H:i:s') }}
                            </p>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li>
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">Tidak ada riwayat untuk transaksi ini.</p>
            </li>
        @endforelse
    </ul>
</div>