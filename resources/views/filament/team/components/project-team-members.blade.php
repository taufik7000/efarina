{{-- resources/views/filament/team/components/project-team-members.blade.php --}}
@php
    $teamMembers = $getRecord()->team_members_users ?? collect();
    $maxDisplay = 3;
    $remainingCount = $teamMembers->count() - $maxDisplay;
@endphp

<div class="flex items-center space-x-2">
    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Team:</span>
    
    @if($teamMembers->isEmpty())
        <span class="text-xs text-gray-500 dark:text-gray-400 italic">No team members</span>
    @else
        <div class="flex items-center -space-x-1">
            @foreach($teamMembers->take($maxDisplay) as $member)
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-xs font-bold ring-2 ring-white dark:ring-gray-800 relative group"
                     title="{{ $member->name }}">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                    
                    {{-- Tooltip --}}
                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10">
                        {{ $member->name }}
                        @if($member->jabatan)
                            <br>{{ $member->jabatan->nama_jabatan }}
                        @endif
                    </div>
                </div>
            @endforeach
            
            @if($remainingCount > 0)
                <div class="w-6 h-6 rounded-full bg-gray-400 dark:bg-gray-600 flex items-center justify-center text-white text-xs font-bold ring-2 ring-white dark:ring-gray-800"
                     title="{{ $remainingCount }} more member{{ $remainingCount > 1 ? 's' : '' }}">
                    +{{ $remainingCount }}
                </div>
            @endif
        </div>
    @endif
</div>