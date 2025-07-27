<?php

namespace App\Exports;

use App\Models\Transaksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TransaksiReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents, WithCustomStartCell
{
    protected $periode;
    protected $judulPeriode;
    protected $transaksis;

    public function __construct($periode)
    {
        $this->periode = $periode;
        $now = now();
        
        if ($periode === 'bulan_ini') {
            $this->judulPeriode = $now->format('F Y');
        } else {
            $this->judulPeriode = 'Tahun ' . $now->year;
        }
    }

    public function startCell(): string
    {
        return 'A8'; // Data mulai dari baris ke-8 untuk memberi ruang header
    }

    public function collection()
    {
        $now = now();
        
        $query = Transaksi::with(['items', 'budgetAllocation.category', 'budgetAllocation.subcategory', 'createdBy'])
            ->where('status', 'completed');
        
        if ($this->periode === 'bulan_ini') {
            $query->whereMonth('tanggal_transaksi', $now->month)
                  ->whereYear('tanggal_transaksi', $now->year);
        } else {
            $query->whereYear('tanggal_transaksi', $now->year);
        }
        
        $this->transaksis = $query->orderBy('tanggal_transaksi', 'desc')->get();
        return $this->transaksis;
    }

    public function headings(): array
    {
        return [
            'No. Transaksi',
            'Tanggal',
            'Jenis',
            'Nama Transaksi',
            'Total Amount',
            'Budget Allocation',
            'Metode Pembayaran',
            'Dibuat Oleh',
            'Status',
            'Detail Items',
            'Deskripsi'
        ];
    }

    public function map($transaksi): array
    {
        // Format items detail dengan line breaks
        $itemsDetail = '';
        if ($transaksi->items && $transaksi->items->count() > 0) {
            $items = [];
            foreach ($transaksi->items as $item) {
                $items[] = "• " . $item->nama_item . 
                          " (" . $item->kuantitas . " " . ($item->satuan ?? 'pcs') . ")" .
                          "\n  @ Rp " . number_format($item->harga_satuan, 0, ',', '.') .
                          " = Rp " . number_format($item->subtotal, 0, ',', '.');
            }
            $itemsDetail = implode("\n", $items);
        } else {
            $itemsDetail = '-';
        }

        return [
            $transaksi->nomor_transaksi,
            $transaksi->tanggal_transaksi->format('d/m/Y'),
            ucfirst($transaksi->jenis_transaksi),
            $transaksi->nama_transaksi,
            number_format($transaksi->total_amount, 0, ',', '.'),
            $transaksi->budgetAllocation?->category_name ?? 'Diluar Budget Plan',
            ucfirst($transaksi->metode_pembayaran ?? '-'),
            $transaksi->createdBy->name ?? '-',
            'SELESAI',
            $itemsDetail,
            $transaksi->deskripsi ?? '-'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,  // No. Transaksi
            'B' => 12,  // Tanggal
            'C' => 12,  // Jenis
            'D' => 25,  // Nama Transaksi
            'E' => 15,  // Total Amount
            'F' => 20,  // Budget Allocation
            'G' => 15,  // Metode Pembayaran
            'H' => 15,  // Dibuat Oleh
            'I' => 10,  // Status
            'J' => 30,  // Detail Items
            'K' => 20,  // Deskripsi
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header table row (baris 8)
            8 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '2563EB']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // ===== HEADER SECTION =====
                // Logo/Company Name
                $sheet->setCellValue('A1', config('app.name', 'Sistem Manajemen Keuangan'));
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '1E40AF'],
                    ],
                ]);
                
                // Report Title
                $sheet->setCellValue('A2', 'LAPORAN TRANSAKSI');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '374151'],
                    ],
                ]);
                
                // Period
                $sheet->setCellValue('A3', 'Periode: ' . $this->judulPeriode);
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '6B7280'],
                    ],
                ]);
                
                // Report Info
                $currentDate = now()->format('d F Y H:i:s');
                $sheet->setCellValue('A4', 'Tanggal Cetak: ' . $currentDate);
                $sheet->setCellValue('A5', 'Status: Transaksi Selesai');
                $sheet->setCellValue('A6', 'Total Transaksi: ' . $this->transaksis->count() . ' transaksi');
                
                $sheet->getStyle('A4:A6')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '9CA3AF'],
                    ],
                ]);

                // Right side header info
                $totalPemasukan = $this->transaksis->where('jenis_transaksi', 'pemasukan')->sum('total_amount');
                $totalPengeluaran = $this->transaksis->where('jenis_transaksi', 'pengeluaran')->sum('total_amount');
                $saldoBersih = $totalPemasukan - $totalPengeluaran;
                
                $sheet->setCellValue('H1', 'RINGKASAN KEUANGAN');
                $sheet->setCellValue('H2', 'Total Pemasukan: Rp ' . number_format($totalPemasukan, 0, ',', '.'));
                $sheet->setCellValue('H3', 'Total Pengeluaran: Rp ' . number_format($totalPengeluaran, 0, ',', '.'));
                $sheet->setCellValue('H4', 'Saldo Bersih: Rp ' . number_format($saldoBersih, 0, ',', '.'));
                
                $sheet->getStyle('H1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '1E40AF'],
                    ],
                ]);
                
                $sheet->getStyle('H2:H4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                // Color coding for saldo
                if ($saldoBersih >= 0) {
                    $sheet->getStyle('H4')->getFont()->getColor()->setRGB('16A34A'); // Green
                } else {
                    $sheet->getStyle('H4')->getFont()->getColor()->setRGB('DC2626'); // Red
                }

                // Header border
                $sheet->getStyle('A1:K6')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '2563EB'],
                        ],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F8FAFC']
                    ],
                ]);

                // ===== DATA SECTION =====
                $highestRow = $sheet->getHighestRow();
                $dataStartRow = 9; // Data dimulai dari baris 9
                
                // Style all data rows
                if ($highestRow >= $dataStartRow) {
                    $dataRange = 'A' . $dataStartRow . ':K' . $highestRow;
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'CCCCCC'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_TOP,
                            'wrapText' => true,
                        ],
                        'font' => [
                            'size' => 10,
                        ],
                    ]);

                    // Style amount column (E)
                    $sheet->getStyle('E' . $dataStartRow . ':E' . $highestRow)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_RIGHT,
                        ],
                        'font' => [
                            'bold' => true,
                        ],
                    ]);

                    // Color coding for transaction types
                    for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                        $cellValue = $sheet->getCell('C' . $row)->getValue();
                        if (strtolower($cellValue) === 'pemasukan') {
                            $sheet->getStyle('C' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'DCFCE7']
                                ],
                                'font' => [
                                    'color' => ['rgb' => '166534'],
                                    'bold' => true,
                                ],
                            ]);
                        } elseif (strtolower($cellValue) === 'pengeluaran') {
                            $sheet->getStyle('C' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'FEE2E2']
                                ],
                                'font' => [
                                    'color' => ['rgb' => '991B1B'],
                                    'bold' => true,
                                ],
                            ]);
                        }
                    }

                    // Style budget allocation column
                    for ($row = $dataStartRow; $row <= $highestRow; $row++) {
                        $cellValue = $sheet->getCell('F' . $row)->getValue();
                        if ($cellValue === 'Diluar Budget Plan') {
                            $sheet->getStyle('F' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color' => ['rgb' => 'FEF3C7']
                                ],
                                'font' => [
                                    'color' => ['rgb' => 'B45309'],
                                    'bold' => true,
                                ],
                            ]);
                        }
                    }
                }

                // ===== FOOTER SECTION =====
                $footerRow = $highestRow + 3;
                
                // Footer border line
                $sheet->getStyle('A' . ($footerRow - 1) . ':K' . ($footerRow - 1))->applyFromArray([
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '2563EB'],
                        ],
                    ],
                ]);

                // Footer content
                $sheet->setCellValue('A' . $footerRow, 'Laporan ini dibuat secara otomatis oleh sistem');
                $sheet->setCellValue('A' . ($footerRow + 1), config('app.name', 'Sistem Keuangan') . ' © ' . date('Y'));
                
                // Right side footer
                $sheet->setCellValue('H' . $footerRow, 'Halaman 1 dari 1');
                $sheet->setCellValue('H' . ($footerRow + 1), 'Dicetak oleh: ' . auth()->user()->name);

                $sheet->getStyle('A' . $footerRow . ':K' . ($footerRow + 1))->applyFromArray([
                    'font' => [
                        'size' => 9,
                        'color' => ['rgb' => '9CA3AF'],
                        'italic' => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color' => ['rgb' => 'F9FAFB']
                    ],
                ]);

                $sheet->getStyle('H' . $footerRow . ':H' . ($footerRow + 1))->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    ],
                ]);

                // Auto-fit row heights
                for ($row = 1; $row <= $footerRow + 1; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(-1);
                }

                // Freeze header row
                $sheet->freezePane('A9');

                // Set print area and page setup
                $sheet->getPageSetup()->setPrintArea('A1:K' . ($footerRow + 1));
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }

    public function title(): string
    {
        return 'Laporan ' . $this->judulPeriode;
    }
}