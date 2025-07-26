<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use App\Models\TransaksiItem;
use App\Models\Project;
use App\Models\BudgetAllocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransaksiController extends Controller
{
    /**
     * Get form data dengan role-based filtering
     */
    public function getFormData()
    {
        $user = auth()->user();
        
        // Projects - filter based on role
        $projectsQuery = Project::whereNotIn('status', ['completed', 'cancelled'])
            ->select('id', 'nama_project', 'status', 'project_manager_id', 'created_by');
            
        if (!$user->hasRole(['keuangan', 'direktur', 'admin'])) {
            $projectsQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('project_manager_id', $user->id);
            });
        }
        
        // Budget allocations - hanya untuk pengeluaran
        $budgetAllocations = BudgetAllocation::whereHas('budgetPlan', function($q) {
            $q->where('status', 'active');
        })
        ->whereRaw('allocated_amount > used_amount')
        ->with(['budgetSubcategory.budgetCategory'])
        ->select('id', 'budget_subcategory_id', 'allocated_amount', 'used_amount')
        ->get()
        ->map(function($allocation) {
            return [
                'id' => $allocation->id,
                'category_name' => $allocation->budgetSubcategory->budgetCategory->nama_kategori ?? 'Unknown',
                'subcategory_name' => $allocation->budgetSubcategory->nama_subkategori ?? 'Unknown',
                'remaining_amount' => $allocation->allocated_amount - $allocation->used_amount,
                'display_name' => ($allocation->budgetSubcategory->budgetCategory->nama_kategori ?? 'Unknown') . 
                               ' > ' . ($allocation->budgetSubcategory->nama_subkategori ?? 'Unknown') . 
                               ' (Sisa: Rp ' . number_format($allocation->allocated_amount - $allocation->used_amount) . ')'
            ];
        });

        return response()->json([
            'projects' => $projectsQuery->get(),
            'budget_allocations' => $budgetAllocations,
            'user_permissions' => [
                'can_approve_directly' => $user->hasRole(['direktur']),
                'can_create_any_type' => $user->hasRole(['keuangan', 'direktur', 'admin']),
                'default_status' => $user->hasRole(['direktur']) ? 'approved' : 'draft',
                'available_status' => $this->getAvailableStatus($user),
            ]
        ]);
    }
    
    /**
     * Get available status based on user role
     */
    private function getAvailableStatus(User $user): array
    {
        if ($user->hasRole(['direktur'])) {
            return [
                'draft' => 'Draft',
                'pending' => 'Menunggu Approval',
                'approved' => 'Menunggu Pembayaran',
                'completed' => 'Selesai'
            ];
        }
        
        if ($user->hasRole(['keuangan'])) {
            return [
                'draft' => 'Draft',
                'pending' => 'Menunggu Approval',
                'approved' => 'Menunggu Pembayaran'
            ];
        }
        
        return [
            'draft' => 'Draft',
            'pending' => 'Menunggu Approval'
        ];
    }

    /**
     * Store transaksi dengan enhanced validation untuk keuangan/direktur
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        
        // Role-based validation rules
        $rules = [
            'nama_transaksi' => 'required|string|max:255',
            'jenis_transaksi' => 'required|in:pemasukan,pengeluaran',
            'tanggal_transaksi' => 'required|date',
            'status' => 'required|in:' . implode(',', array_keys($this->getAvailableStatus($user))),
            'deskripsi' => 'nullable|string|max:1000',
            'project_id' => 'nullable|exists:projects,id',
            'budget_allocation_id' => 'nullable|exists:budget_allocations,id',
            'metode_pembayaran' => 'nullable|string|in:cash,transfer,debit,credit,e_wallet,cek',
            'nomor_referensi' => 'nullable|string|max:100',
            'total_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.nama_item' => 'required|string|max:255',
            'items.*.kuantitas' => 'required|integer|min:1',
            'items.*.harga_satuan' => 'required|numeric|min:0',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.deskripsi_item' => 'nullable|string|max:500',
        ];
        
        // Additional rules for keuangan/direktur
        if ($user->hasRole(['keuangan', 'direktur'])) {
            $rules['catatan_approval'] = 'nullable|string|max:1000';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate nomor transaksi
            $nomorTransaksi = $this->generateNomorTransaksi($request->jenis_transaksi);

            // Prepare transaksi data
            $transaksiData = [
                'nomor_transaksi' => $nomorTransaksi,
                'nama_transaksi' => $request->nama_transaksi,
                'jenis_transaksi' => $request->jenis_transaksi,
                'tanggal_transaksi' => $request->tanggal_transaksi,
                'status' => $request->status,
                'deskripsi' => $request->deskripsi,
                'project_id' => $request->project_id,
                'budget_allocation_id' => $request->budget_allocation_id,
                'metode_pembayaran' => $request->metode_pembayaran,
                'nomor_referensi' => $request->nomor_referensi,
                'total_amount' => $request->total_amount,
                'created_by' => $user->id,
            ];
            
            // Auto-approval untuk direktur
            if ($user->hasRole(['direktur']) && in_array($request->status, ['approved', 'completed'])) {
                $transaksiData['approved_by'] = $user->id;
                $transaksiData['approved_at'] = now();
                $transaksiData['catatan_approval'] = $request->catatan_approval ?? 'Disetujui langsung oleh direktur';
            }

            // Create transaksi
            $transaksi = Transaksi::create($transaksiData);

            // Create transaksi items
            foreach ($request->items as $itemData) {
                TransaksiItem::create([
                    'transaksi_id' => $transaksi->id,
                    'nama_item' => $itemData['nama_item'],
                    'kuantitas' => $itemData['kuantitas'],
                    'harga_satuan' => $itemData['harga_satuan'],
                    'subtotal' => $itemData['kuantitas'] * $itemData['harga_satuan'],
                    'satuan' => $itemData['satuan'] ?? 'pcs',
                    'deskripsi_item' => $itemData['deskripsi_item'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'data' => [
                    'id' => $transaksi->id,
                    'nomor_transaksi' => $transaksi->nomor_transaksi,
                    'total_amount' => $transaksi->total_amount,
                    'status' => $transaksi->status,
                    'auto_approved' => $user->hasRole(['direktur']) && $transaksi->approved_at !== null
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Generate nomor transaksi
     */
    private function generateNomorTransaksi($jenisTransaksi)
    {
        $prefix = $jenisTransaksi === 'pemasukan' ? 'TRX-IN' : 'TRX-OUT';
        $year = now()->format('Y');
        $month = now()->format('m');
        
        $counter = Transaksi::whereYear('tanggal_transaksi', now()->year)
                           ->whereMonth('tanggal_transaksi', now()->month)
                           ->where('jenis_transaksi', $jenisTransaksi)
                           ->count() + 1;
        
        return $prefix . '/' . $year . '/' . $month . '/' . str_pad($counter, 4, '0', STR_PAD_LEFT);
    }
}