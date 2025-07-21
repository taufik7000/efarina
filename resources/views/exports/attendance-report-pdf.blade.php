{{-- resources/views/exports/attendance-report-pdf.blade.php --}}

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kehadiran {{ $periode }}</title>
    <style>
        /* ADDED: @page rule to control PDF margins directly */
        @page {
            margin: 0.7cm; 
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9px; /* REDUCED: Base font size */
            line-height: 1.1;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 10px; /* REDUCED */
            border-bottom: 1px solid #333; /* REDUCED */
            padding-bottom: 5px; /* REDUCED */
        }
        
        .header h1 {
            font-size: 14px; /* REDUCED */
            margin-bottom: 3px; /* REDUCED */
            text-transform: uppercase;
        }
        
        .header .info {
            font-size: 10px; /* REDUCED */
        }

        .summary-section {
            margin-bottom: 10px; /* REDUCED */
        }
        
        .summary-section h3 {
            font-size: 11px; /* REDUCED */
            margin-bottom: 5px; /* REDUCED */
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px; /* REDUCED */
        }
        
        .summary-table td {
            border: 1px solid #ddd;
            padding: 4px; /* REDUCED */
            text-align: center;
            background-color: #f8f9fa;
            width: 12.5%;
        }
        
        .summary-table .label {
            font-size: 7px; /* REDUCED */
            color: #666;
            text-transform: uppercase;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        
        .summary-table .value {
            font-size: 12px; /* REDUCED */
            font-weight: bold;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5px; /* REDUCED */
        }
        
        .data-table th,
        .data-table td {
            border: 1px solid #333;
            padding: 2px; /* REDUCED */
            text-align: center;
            vertical-align: middle;
        }
        
        .data-table th {
            background-color: #333;
            color: white;
            font-weight: bold;
            font-size: 7px; /* REDUCED */
        }
        
        .name-cell {
            text-align: left;
        }
        
        .jabatan-cell {
            text-align: left;
            font-size: 6.5px; /* REDUCED */
        }
        
        .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 15px; /* REDUCED */
            display: table;
            width: 100%;
        }
        
        .footer-left, .footer-right {
            display: table-cell;
            vertical-align: top;
        }

        .footer-left { width: 60%; }
        .footer-right { width: 40%; text-align: center; }
        
        .signature-box {
            border: 1px solid #333;
            height: 45px; /* REDUCED */
            margin-top: 5px; /* REDUCED */
            position: relative;
        }
        
        .signature-label {
            position: absolute;
            bottom: 2px; /* REDUCED */
            left: 50%;
            transform: translateX(-50%);
            font-weight: bold;
            font-size: 7px; /* REDUCED */
        }
        
        .print-info {
            font-size: 6.5px; /* REDUCED */
            color: #666;
            line-height: 1.2;
        }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>Laporan Kehadiran Bulanan</h1>
        {{-- MODIFIED: Combined info lines to save space --}}
        <div class="info">
            Periode: <strong>{{ $periode }}</strong> | Dicetak: {{ now()->format('d F Y H:i:s') }}
        </div>
    </div>

    {{-- Summary Section --}}
    <div class="summary-section">
        <h3>Ringkasan Total</h3>
        <table class="summary-table">
            <tr>
                <td>
                    <span class="label">Hadir</span>
                    <span class="value">{{ $summary['hadir'] }}</span>
                </td>
                <td>
                    <span class="label">Terlambat</span>
                    <span class="value">{{ $summary['terlambat'] }}</span>
                </td>
                <td>
                    <span class="label">Cuti</span>
                    <span class="value">{{ $summary['cuti'] }}</span>
                </td>
                <td>
                    <span class="label">Sakit</span>
                    <span class="value">{{ $summary['sakit'] }}</span>
                </td>
                <td>
                    <span class="label">Izin</span>
                    <span class="value">{{ $summary['izin'] }}</span>
                </td>
                <td>
                    <span class="label">Kompensasi</span>
                    <span class="value">{{ $summary['kompensasi'] }}</span>
                </td>
                <td>
                    <span class="label">Absen</span>
                    <span class="value">{{ $summary['absen'] }}</span>
                </td>
                <td>
                    <span class="label">Total Karyawan</span>
                    <span class="value">{{ count($reportData) }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Data Table --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 25%;">Nama Karyawan</th>
                <th style="width: 15%;">Jabatan</th>
                <th style="width: 6%;">Hadir</th>
                <th style="width: 6%;">Telat</th>
                <th style="width: 6%;">Cuti</th>
                <th style="width: 6%;">Sakit</th>
                <th style="width: 6%;">Izin</th>
                <th style="width: 6%;">Komp</th>
                <th style="width: 6%;">Absen</th>
                <th style="width: 8%;">Kehadiran (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData as $index => $data)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="name-cell">{{ $data['name'] }}</td>
                    <td class="jabatan-cell">{{ $data['jabatan'] }}</td>
                    <td>{{ $data['hadir'] }}</td>
                    <td>{{ $data['terlambat'] }}</td>
                    <td>{{ $data['cuti'] }}</td>
                    <td>{{ $data['sakit'] }}</td>
                    <td>{{ $data['izin'] }}</td>
                    <td>{{ $data['kompensasi'] }}</td>
                    <td>{{ $data['absen'] }}</td>
                    <td><strong>{{ $data['attendance_rate'] }}%</strong></td>
                </tr>
            @endforeach
            
            {{-- Total Summary Row --}}
            <tr class="total-row">
                <td colspan="3"><strong>TOTAL KESELURUHAN</strong></td>
                <td><strong>{{ $summary['hadir'] }}</strong></td>
                <td><strong>{{ $summary['terlambat'] }}</strong></td>
                <td><strong>{{ $summary['cuti'] }}</strong></td>
                <td><strong>{{ $summary['sakit'] }}</strong></td>
                <td><strong>{{ $summary['izin'] }}</strong></td>
                <td><strong>{{ $summary['kompensasi'] }}</strong></td>
                <td><strong>{{ $summary['absen'] }}</strong></td>
                <td><strong>-</strong></td>
            </tr>
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <div class="footer-left">
            <div class="print-info">
                <div><strong>Keterangan:</strong></div>
                <div>- Laporan digenerate otomatis oleh sistem.</div>
                <div>- Data berdasarkan periode {{ $periode }}.</div>
            </div>
        </div>
        
        <div class="footer-right">
            <div>Mengetahui,</div>
            <div class="signature-box">
                <div class="signature-label">HRD Manager</div>
            </div>
        </div>
    </div>
</body>
</html>