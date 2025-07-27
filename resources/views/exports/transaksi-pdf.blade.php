<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laporan Transaksi - {{ $judulPeriode }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            max-width: 210mm;
            margin: 0 auto;
            padding: 15mm;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20px;
            color: #1e40af;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
        .header h2 {
            font-size: 16px;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .header .meta {
            font-size: 10px;
            color: #6b7280;
        }
        
        .summary {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .summary h3 {
            color: #1e40af;
            margin-bottom: 12px;
            font-size: 14px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 5px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px dotted #cbd5e1;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-item.highlight {
            background: #dbeafe;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #93c5fd;
            font-weight: bold;
        }
        
        .amount-positive {
            color: #16a34a;
            font-weight: bold;
        }
        
        .amount-negative {
            color: #dc2626;
            font-weight: bold;
        }
        
        .amount-neutral {
            color: #1e40af;
            font-weight: bold;
        }
        
        .transactions {
            margin-top: 25px;
        }
        
        .transactions h3 {
            color: #1e40af;
            margin-bottom: 15px;
            font-size: 14px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
        }
        
        .transaction {
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-bottom: 12px;
            overflow: hidden;
            break-inside: avoid;
        }
        
        .transaction-header {
            background: #f9fafb;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .transaction-header .left {
            flex: 1;
        }
        
        .transaction-header .right {
            text-align: right;
        }
        
        .transaction-number {
            font-weight: bold;
            color: #1e40af;
            font-size: 12px;
        }
        
        .transaction-date {
            color: #6b7280;
            font-size: 10px;
        }
        
        .transaction-type {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .type-pemasukan {
            background: #dcfce7;
            color: #166534;
        }
        
        .type-pengeluaran {
            background: #fee2e2;
            color: #991b1b;
        }
        
        .transaction-body {
            padding: 12px;
        }
        
        .transaction-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #374151;
        }
        
        .transaction-amount {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        
        .transaction-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
            font-size: 10px;
            color: #6b7280;
        }
        
        .budget-info {
            padding: 6px 8px;
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 4px;
            font-size: 10px;
            margin-bottom: 8px;
        }
        
        .budget-info.outside {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .items-list {
            background: #f8fafc;
            border-radius: 4px;
            padding: 8px;
            margin-top: 8px;
        }
        
        .items-list h5 {
            font-size: 10px;
            color: #374151;
            margin-bottom: 6px;
            font-weight: bold;
        }
        
        .item {
            display: flex;
            justify-content: space-between;
            padding: 3px 0;
            border-bottom: 1px dotted #cbd5e1;
            font-size: 9px;
        }
        
        .item:last-child {
            border-bottom: none;
        }
        
        .item-detail {
            flex: 1;
        }
        
        .item-amount {
            font-weight: bold;
            color: #374151;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 10mm;
            }
            
            .transaction {
                break-inside: avoid;
            }
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>LAPORAN TRANSAKSI</h1>
        <h2>{{ $judulPeriode }}</h2>
        <div class="meta">
            Dicetak pada: {{ $now->format('d F Y H:i:s') }} | 
            Total Transaksi: {{ $jumlahTransaksi }} | 
            Status: Selesai
        </div>
    </div>

    <!-- Summary -->
    <div class="summary">
        <h3>📊 Ringkasan Keuangan</h3>
        <div class="summary-grid">
            <div>
                <div class="summary-item">
                    <span>💰 Total Pemasukan:</span>
                    <span class="amount-positive">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</span>
                </div>
                <div class="summary-item">
                    <span>💸 Total Pengeluaran:</span>
                    <span class="amount-negative">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</span>
                </div>
                <div class="summary-item highlight">
                    <span>📈 Saldo Bersih:</span>
                    <span class="{{ $saldoBersih >= 0 ? 'amount-positive' : 'amount-negative' }}">
                        Rp {{ number_format($saldoBersih, 0, ',', '.') }}
                    </span>
                </div>
            </div>
            <div>
                <div class="summary-item">
                    <span>📥 Transaksi Pemasukan:</span>
                    <span>{{ $transaksiPemasukan }} transaksi</span>
                </div>
                <div class="summary-item">
                    <span>📤 Transaksi Pengeluaran:</span>
                    <span>{{ $transaksiPengeluaran }} transaksi</span>
                </div>
                <div class="summary-item">
                    <span>🎯 Dengan Budget Plan:</span>
                    <span>{{ $transaksiDenganBudget }} transaksi</span>
                </div>
                <div class="summary-item">
                    <span>⚠️ Diluar Budget Plan:</span>
                    <span>{{ $transaksiDiluarBudget }} transaksi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="transactions">
        <h3>📋 Detail Transaksi ({{ $transaksis->count() }})</h3>
        
        @forelse($transaksis as $transaksi)
        <div class="transaction">
            <div class="transaction-header">
                <div class="left">
                    <div class="transaction-number">{{ $transaksi->nomor_transaksi }}</div>
                    <div class="transaction-date">{{ $transaksi->tanggal_transaksi->format('d F Y') }}</div>
                </div>
                <div class="right">
                    <span class="transaction-type type-{{ $transaksi->jenis_transaksi }}">
                        {{ ucfirst($transaksi->jenis_transaksi) }}
                    </span>
                </div>
            </div>
            
            <div class="transaction-body">
                <div class="transaction-title">{{ $transaksi->nama_transaksi }}</div>
                
                <div class="transaction-amount {{ $transaksi->jenis_transaksi === 'pemasukan' ? 'amount-positive' : 'amount-negative' }}">
                    {{ $transaksi->jenis_transaksi === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($transaksi->total_amount, 0, ',', '.') }}
                </div>
                
                <div class="transaction-meta">
                    <div>👤 Dibuat oleh: {{ $transaksi->createdBy->name }}</div>
                    <div>💳 Metode: {{ ucfirst($transaksi->metode_pembayaran ?? 'Tidak specified') }}</div>
                </div>
                
                @if($transaksi->jenis_transaksi === 'pengeluaran')
                    <div class="budget-info {{ $transaksi->budgetAllocation ? '' : 'outside' }}">
                        🎯 Budget: {{ $transaksi->budgetAllocation?->category_name ?? 'Diluar Budget Plan' }}
                    </div>
                @endif
                
                @if($transaksi->deskripsi)
                    <div style="margin: 8px 0; font-size: 10px; color: #6b7280;">
                        📝 {{ $transaksi->deskripsi }}
                    </div>
                @endif
                
                @if($transaksi->items && $transaksi->items->count() > 0)
                    <div class="items-list">
                        <h5>📦 Items ({{ $transaksi->items->count() }}):</h5>
                        @foreach($transaksi->items as $item)
                            <div class="item">
                                <div class="item-detail">
                                    {{ $item->nama_item }} 
                                    ({{ $item->kuantitas }} {{ $item->satuan ?? 'pcs' }} × Rp {{ number_format($item->harga_satuan, 0, ',', '.') }})
                                </div>
                                <div class="item-amount">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        @empty
        <div class="no-data">
            📭 Tidak ada transaksi selesai untuk periode {{ $judulPeriode }}
        </div>
        @endforelse
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Keuangan</p>
        <p>{{ config('app.name', 'Laravel') }} | {{ $now->format('Y') }}</p>
    </div>
</body>
</html>