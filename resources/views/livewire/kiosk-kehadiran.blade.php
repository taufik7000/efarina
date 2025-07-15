<div wire:poll.5s>
    {{-- Statistik Gamifikasi --}}
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-number">{{ $totalHadirTepatWaktu }}</div>
            <div class="stat-label">Tepat Waktu</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $persentaseTepatWaktu }}%</div>
            <div class="stat-label">Tingkat Kedisiplinan</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $totalHadirHariIni }}</div>
            <div class="stat-label">Total Hadir</div>
        </div>
    </div>

    {{-- Leaderboard Champions --}}
    <div class="leaderboard-section">
        <h3 class="leaderboard-title">
            🏆 LEADERBOARD CHAMPIONS 🏆
        </h3>
        <p class="leaderboard-subtitle">Karyawan Terdisiplin Hari Ini (Hadir Sebelum 08:00)</p>
        
        @forelse ($leaderboard as $index => $kehadiran)
            @php
                $rank = $index + 1;
                $badgeClass = '';
                $badgeIcon = '';
                
                if ($rank == 1) {
                    $badgeClass = 'gold';
                    $badgeIcon = '🥇';
                } elseif ($rank == 2) {
                    $badgeClass = 'silver';
                    $badgeIcon = '🥈';
                } elseif ($rank == 3) {
                    $badgeClass = 'bronze';
                    $badgeIcon = '🥉';
                } else {
                    $badgeClass = 'regular';
                    $badgeIcon = '⭐';
                }
            @endphp
            
            <div class="leaderboard-item {{ $badgeClass }}">
                <div class="rank">
                    <span class="rank-number">{{ $rank }}</span>
                    <span class="rank-badge">{{ $badgeIcon }}</span>
                </div>
                <div class="player-info">
                    <div class="player-name">{{ $kehadiran->pengguna->name }}</div>
                    <div class="player-time">
                        ⚡ {{ \Carbon\Carbon::parse($kehadiran->jam_masuk)->format('H:i:s') }}
                    </div>
                </div>
                @if($rank <= 3)
                    <div class="achievement">
                        <div class="achievement-text">CHAMPION</div>
                    </div>
                @endif
            </div>
        @empty
            <div class="no-champions">
                <div class="no-champions-icon">🎯</div>
                <div class="no-champions-text">Belum ada champion hari ini!</div>
                <div class="no-champions-subtitle">Jadilah yang pertama hadir sebelum 08:00</div>
            </div>
        @endforelse
    </div>

    {{-- Aktivitas Terbaru --}}
    <div class="recent-activity">
        <h4 class="activity-title">📊 Aktivitas Terbaru</h4>
        
        @forelse ($kehadiranHariIni->take(5) as $kehadiran)
            @php
                $sudahPulang = !is_null($kehadiran->jam_pulang);
                $isTepatWaktu = \Carbon\Carbon::parse($kehadiran->jam_masuk)->format('H:i:s') < '08:00:00';
                
                $bgColor = $sudahPulang ? '#f3f4f6' : ($isTepatWaktu ? '#ecfdf5' : '#fff7ed');
                $textColor = $sudahPulang ? '#6b7280' : ($isTepatWaktu ? '#065f46' : '#9a3412');
                $borderColor = $isTepatWaktu ? '#10b981' : '#f59e0b';
                
                $infoTeks = $sudahPulang 
                    ? 'Pulang pukul: ' . \Carbon\Carbon::parse($kehadiran->jam_pulang)->format('H:i:s')
                    : 'Masuk pukul: ' . \Carbon\Carbon::parse($kehadiran->jam_masuk)->format('H:i:s');
            @endphp

            <div class="activity-item" style="background-color: {{ $bgColor }}; border-left: 4px solid {{ $borderColor }};">
                <div class="activity-info">
                    <div class="activity-name">
                        {{ $kehadiran->pengguna->name }}
                        @if($isTepatWaktu && !$sudahPulang)
                            <span class="punctual-badge">⚡ TEPAT WAKTU</span>
                        @endif
                    </div>
                    <div class="activity-time" style="color: {{ $textColor }};">
                        {{ $infoTeks }}
                    </div>
                </div>
                <div class="activity-status">
                    @if($sudahPulang)
                        <span class="status-badge finished">✅ Selesai</span>
                    @elseif($isTepatWaktu)
                        <span class="status-badge champion">🏆 Champion</span>
                    @else
                        <span class="status-badge active">🔥 Aktif</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="no-activity">
                <p>Belum ada aktivitas hari ini.</p>
            </div>
        @endforelse
    </div>

    <style>
        /* Stats Container */
        .stats-container {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            justify-content: center;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            min-width: 80px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 1.8em;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 0.8em;
            opacity: 0.9;
        }

        /* Leaderboard Section */
        .leaderboard-section {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .leaderboard-title {
            text-align: center;
            font-size: 1.3em;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #d97706;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        }

        .leaderboard-subtitle {
            text-align: center;
            font-size: 0.9em;
            color: #92400e;
            margin-bottom: 15px;
        }

        .leaderboard-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            padding: 12px;
            border-radius: 10px;
            transition: transform 0.2s;
            animation: slideInLeft 0.5s ease-out;
        }

        .leaderboard-item:hover {
            transform: translateY(-2px);
        }

        .leaderboard-item.gold {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);
        }

        .leaderboard-item.silver {
            background: linear-gradient(135deg, #c0c0c0 0%, #e5e5e5 100%);
            box-shadow: 0 4px 15px rgba(192, 192, 192, 0.3);
        }

        .leaderboard-item.bronze {
            background: linear-gradient(135deg, #cd7f32 0%, #daa520 100%);
            box-shadow: 0 4px 15px rgba(205, 127, 50, 0.3);
        }

        .leaderboard-item.regular {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        }

        .rank {
            display: flex;
            align-items: center;
            margin-right: 15px;
            min-width: 60px;
        }

        .rank-number {
            font-size: 1.2em;
            font-weight: 700;
            margin-right: 8px;
        }

        .rank-badge {
            font-size: 1.5em;
        }

        .player-info {
            flex: 1;
        }

        .player-name {
            font-weight: 700;
            font-size: 1.1em;
            margin-bottom: 3px;
        }

        .player-time {
            font-size: 0.9em;
            color: #6b7280;
            font-weight: 600;
        }

        .achievement {
            text-align: center;
            padding: 5px 10px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.8);
        }

        .achievement-text {
            font-size: 0.8em;
            font-weight: 700;
            color: #dc2626;
        }

        .no-champions {
            text-align: center;
            padding: 30px;
            color: #6b7280;
        }

        .no-champions-icon {
            font-size: 3em;
            margin-bottom: 10px;
        }

        .no-champions-text {
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .no-champions-subtitle {
            font-size: 0.9em;
            opacity: 0.8;
        }

        /* Recent Activity */
        .recent-activity {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .activity-title {
            margin: 0 0 15px 0;
            font-size: 1.1em;
            color: #374151;
            text-align: center;
        }

        .activity-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 12px;
            border-radius: 8px;
            animation: fadeIn 0.5s ease-out;
        }

        .activity-info {
            flex: 1;
        }

        .activity-name {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .punctual-badge {
            background: #10b981;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            font-weight: 600;
            margin-left: 8px;
        }

        .activity-time {
            font-size: 0.9em;
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: 600;
        }

        .status-badge.champion {
            background: #fbbf24;
            color: #92400e;
        }

        .status-badge.active {
            background: #f59e0b;
            color: white;
        }

        .status-badge.finished {
            background: #6b7280;
            color: white;
        }

        .no-activity {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }

        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</div>