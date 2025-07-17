{{-- resources/views/filament/team/columns/project-members.blade.php --}}
@php
    // Dapatkan team members dari record
    $teamMemberIds = $getRecord()->team_members ?? [];
    $projectManager = $getRecord()->projectManager;
    
    // Gabungkan PM dengan team members, hindari duplikasi
    $allMemberIds = collect($teamMemberIds);
    if ($projectManager && !$allMemberIds->contains($projectManager->id)) {
        $allMemberIds->prepend($projectManager->id);
    }
    
    // Ambil data user
    $teamMembers = \App\Models\User::whereIn('id', $allMemberIds->toArray())->get();
    
    $maxDisplay = 3; // Maksimal avatar yang ditampilkan
    $remainingCount = $teamMembers->count() - $maxDisplay;
@endphp

<div class="flex items-center space-x-1">
    @if($teamMembers->isEmpty())
        <span class="text-xs text-gray-500 dark:text-gray-400 italic">No members</span>
    @else
        <div class="flex items-center -space-x-2">
            @foreach($teamMembers->take($maxDisplay) as $index => $member)
                @php
                    // Warna avatar berdasarkan index atau role
                    $colors = [
                        'from-blue-500 to-blue-600',
                        'from-green-500 to-green-600', 
                        'from-purple-500 to-purple-600',
                        'from-orange-500 to-orange-600',
                        'from-pink-500 to-pink-600',
                        'from-indigo-500 to-indigo-600'
                    ];
                    $colorClass = $colors[$index % count($colors)];
                    
                    // Cek apakah ini adalah PM
                    $isProjectManager = $projectManager && $member->id === $projectManager->id;
                @endphp
                
                <div class="relative group">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br {{ $colorClass }} flex items-center justify-center text-white text-sm font-bold ring-2 ring-white dark:ring-gray-800 hover:ring-blue-200 transition-all duration-200 cursor-pointer {{ $isProjectManager ? 'ring-yellow-400' : '' }}"
                         title="{{ $member->name }}{{ $isProjectManager ? ' (PM)' : '' }}">
                        {{ strtoupper(substr($member->name, 0, 1)) }}
                        
                        @if($isProjectManager)
                            {{-- Crown icon untuk PM --}}
                            <div class="absolute -top-1 -right-1 w-3 h-3 bg-yellow-400 rounded-full flex items-center justify-center">
                                <svg class="w-2 h-2 text-yellow-800" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Tooltip --}}
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        {{ $member->name }}
                        @if($isProjectManager)
                            <div class="text-yellow-300 font-semibold">Project Manager</div>
                        @endif
                        @if($member->email)
                            <div class="text-gray-300">{{ $member->email }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
            
            {{-- Sisa member count --}}
            @if($remainingCount > 0)
                <div class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-500 flex items-center justify-center text-gray-700 dark:text-gray-200 text-xs font-semibold ring-2 ring-white dark:ring-gray-800 cursor-pointer hover:bg-gray-400 dark:hover:bg-gray-400 transition-colors shadow-sm"
                     title="{{ $remainingCount }} more member{{ $remainingCount > 1 ? 's' : '' }}">
                    <span class="text-xs">+{{ $remainingCount }}</span>
                </div>
            @endif
        </div>
    @endif
</div>