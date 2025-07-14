<x-filament-panels::page>
    <div class="space-y-8 animate-fade-in">
        {{-- Welcome Section - Enhanced with glassmorphism effect --}}
        <div class="relative rounded-2xl p-8 text-white overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 shadow-2xl">
            <div class="absolute inset-0 bg-white/10 backdrop-blur-sm"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-50"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl -translate-y-48 translate-x-48"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/10 rounded-full blur-3xl translate-y-32 -translate-x-32"></div>
            
            <div class="relative z-10">
                <div class="flex items-center justify-between">
                    <div class="space-y-3">
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-white to-white/80 bg-clip-text text-transparent">
                            Selamat Pagi, {{ auth()->user()->name }}! 👋
                        </h1>
                        <p class="text-white/90 max-w-2xl text-lg leading-relaxed">
                            Here's your personalized dashboard overview. Let's make today productive and achieve your goals!
                        </p>
                    </div>
                    <div class="hidden lg:block">
                        <div class="w-32 h-32 rounded-full bg-white/20 backdrop-blur-md flex items-center justify-center border border-white/30">
                            <x-heroicon-o-rocket-launch class="w-16 h-16 text-white/80" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics Cards - Enhanced with better spacing and animations --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- My Tasks Stats --}}
            <div class="group p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-2 hover:border-blue-200 dark:hover:border-blue-600">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">My Tasks</p>
                        <div class="flex items-baseline space-x-2">
                            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $myTasksStats['total'] }}</p>
                            <span class="text-sm text-gray-500 dark:text-gray-400">total</span>
                        </div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-o-clipboard-document-list class="w-7 h-7 text-white" />
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center text-sm font-medium text-green-600 dark:text-green-400">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            Done
                        </span>
                        <span class="text-sm font-bold text-green-700 dark:text-green-300">{{ $myTasksStats['done'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center text-sm font-medium text-blue-600 dark:text-blue-400">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                            Active
                        </span>
                        <span class="text-sm font-bold text-blue-700 dark:text-blue-300">{{ $myTasksStats['in_progress'] }}</span>
                    </div>
                    @if($myTasksStats['overdue'] > 0)
                        <div class="flex items-center justify-between p-2 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <span class="flex items-center text-sm font-medium text-red-600 dark:text-red-400">
                                <x-heroicon-s-exclamation-triangle class="w-4 h-4 mr-2" />
                                Overdue
                            </span>
                            <span class="text-sm font-bold text-red-700 dark:text-red-300">{{ $myTasksStats['overdue'] }}</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- My Projects Stats --}}
            <div class="group p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-2 hover:border-emerald-200 dark:hover:border-emerald-600">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">My Projects</p>
                        <div class="flex items-baseline space-x-2">
                            <p class="text-4xl font-bold text-gray-900 dark:text-white">{{ $myProjectsStats['total'] }}</p>
                            <span class="text-sm text-gray-500 dark:text-gray-400">total</span>
                        </div>
                    </div>
                    <div class="p-4 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                        <x-heroicon-o-folder class="w-7 h-7 text-white" />
                    </div>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="flex items-center text-sm font-medium text-emerald-600 dark:text-emerald-400">
                            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></div>
                            Active
                        </span>
                        <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{ $myProjectsStats['active'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="flex items-center text-sm font-medium text-gray-600 dark:text-gray-400">
                            <div class="w-2 h-2 bg-gray-500 rounded-full mr-2"></div>
                            Completed
                        </span>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $myProjectsStats['completed'] }}</span>
                    </div>
                </div>
            </div>
            
            {{-- Quick Actions --}}
            <div class="group p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-2">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Quick Actions</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Get things done faster</p>
                </div>
                <div class="space-y-3">
                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('create') }}" 
                       class="flex items-center justify-center w-full px-4 py-3 text-sm font-semibold text-white bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl hover:from-purple-700 hover:to-purple-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-all duration-200 transform hover:scale-105 shadow-lg">
                        <x-heroicon-o-plus-circle class="w-5 h-5 mr-2"/> 
                        Create New Task
                    </a>
                    <a href="{{ \App\Filament\Team\Resources\ProjectResource::getUrl('create') }}"
                       class="flex items-center justify-center w-full px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 transform hover:scale-105 border border-gray-200 dark:border-gray-600">
                        <x-heroicon-o-folder-plus class="w-5 h-5 mr-2"/> 
                        New Project
                    </a>
                </div>
            </div>

            {{-- Task Status Breakdown --}}
            <div class="group p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-xl hover:-translate-y-2">
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">Progress Overview</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Your task completion status</p>
                </div>
                <div class="space-y-4">
                    @php
                        $totalTasks = $myTasksStats['total'] > 0 ? $myTasksStats['total'] : 1;
                        $statuses = [
                            ['key' => 'todo', 'label' => 'To Do', 'color' => 'gray'],
                            ['key' => 'in_progress', 'label' => 'In Progress', 'color' => 'blue'],
                            ['key' => 'done', 'label' => 'Completed', 'color' => 'green']
                        ];
                    @endphp
                    
                    @foreach($statuses as $status)
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-semibold text-{{ $status['color'] }}-600 dark:text-{{ $status['color'] }}-400">
                                    {{ $status['label'] }}
                                </span>
                                <span class="text-sm font-bold text-{{ $status['color'] }}-800 dark:text-{{ $status['color'] }}-300">
                                    {{ $myTasksStats[$status['key']] }}
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="bg-{{ $status['color'] }}-500 h-2 rounded-full transition-all duration-1000 ease-out" 
                                     style="width: {{ ($myTasksStats[$status['key']] / $totalTasks) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Recent Tasks --}}
            <div class="lg:col-span-3 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-800">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Recent Tasks</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Your latest task activities</p>
                        </div>
                        <div class="p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                            <x-heroicon-o-clock class="w-5 h-5 text-gray-500 dark:text-gray-400" />
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentTasks as $task)
                        <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200 group">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('view', ['record' => $task]) }}" 
                                       class="text-base font-semibold text-gray-900 dark:text-gray-100 hover:text-purple-600 dark:hover:text-purple-400 transition-colors line-clamp-2">
                                        {{ $task->nama_task }}
                                    </a>
                                    <div class="flex items-center mt-2 space-x-4">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $task->project->nama_project }}</span>
                                        </p>
                                        @if($task->tanggal_deadline)
                                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                                                <x-heroicon-o-calendar class="w-3 h-3 mr-1" />
                                                {{ $task->tanggal_deadline->format('d M Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                               {{ match($task->status) {
                                                   'todo' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                                   'in_progress' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300',
                                                   'review' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300',
                                                   'done' => 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300',
                                                   'blocked' => 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300',
                                                   default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'
                                               } }}">
                                        {{ ucfirst(str_replace('_', ' ', $task->status)) }}
                                    </span>
                                    
                                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('view', ['record' => $task]) }}"
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 bg-purple-50 dark:bg-purple-900/30 rounded-full hover:bg-purple-100 dark:hover:bg-purple-900/50 transition-all duration-200">
                                        View
                                        <x-heroicon-o-arrow-right class="w-3 h-3 ml-1" />
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <x-heroicon-o-circle-stack class="w-10 h-10 text-gray-400"/>
                            </div>
                            <h4 class="text-lg font-semibold mb-2">No recent tasks</h4>
                            <p class="text-sm">When you get assigned to a task, it will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Upcoming Deadlines --}}
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-orange-50 dark:from-red-900/20 dark:to-orange-900/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Upcoming Deadlines</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Don't miss these!</p>
                        </div>
                        <div class="p-2 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-orange-500" />
                        </div>
                    </div>
                </div>
                
                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($upcomingDeadlines as $task)
                        <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-all duration-200">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex-1">
                                    <a href="{{ \App\Filament\Team\Resources\TaskResource::getUrl('view', ['record' => $task]) }}" 
                                       class="text-sm font-semibold text-gray-800 dark:text-gray-200 hover:text-purple-600 dark:hover:text-purple-400 line-clamp-2">
                                        {{ $task->nama_task }}
                                    </a>
                                    <p class="text-xs mt-1 font-medium {{ $task->tanggal_deadline->isToday() ? 'text-red-600 dark:text-red-400' : ($task->tanggal_deadline->isTomorrow() ? 'text-orange-600 dark:text-orange-400' : 'text-gray-500 dark:text-gray-400') }}">
                                        {{ $task->tanggal_deadline->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="px-2 py-1 rounded-lg text-xs font-bold {{ $task->tanggal_deadline->isToday() ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300' : ($task->tanggal_deadline->isTomorrow() ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/50 dark:text-orange-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300') }}">
                                        {{ $task->tanggal_deadline->format('M d') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-16 text-center text-gray-500 dark:text-gray-400">
                            <div class="w-20 h-20 mx-auto mb-4 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                <x-heroicon-o-calendar-days class="w-10 h-10 text-gray-400"/>
                            </div>
                            <h4 class="text-lg font-semibold mb-2">No upcoming deadlines</h4>
                            <p class="text-sm">You're all caught up!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Custom Styles --}}
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }
        
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .group:hover .group-hover\:scale-110 {
            transform: scale(1.1);
        }
    </style>
</x-filament-panels::page>