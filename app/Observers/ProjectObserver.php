<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Transaksi;
use Illuminate\Support\Str;

class ProjectObserver
{
    /**
     * Handle the Project "created" event.
     */
    public function created(Project $project): void
    {
        // Ensure approval status is set to pending
        $project->update([
            'redaksi_approval_status' => 'pending',
            'keuangan_approval_status' => 'pending',
        ]);

        // Auto-create transaksi draft saat project dibuat dengan proposal budget
        if ($project->proposal_budget > 0) {
            $this->createProjectProposalTransaction($project);
        }
    }

    /**
     * Handle the Project "updated" event.
     */
    public function updated(Project $project): void
    {
        // Handle redaksi approval
        if ($project->redaksi_approval_status === 'approved' && 
            $project->getOriginal('redaksi_approval_status') !== 'approved') {
            $this->handleRedaksiApproval($project);
        }

        // Handle redaksi rejection
        if ($project->redaksi_approval_status === 'rejected' && 
            $project->getOriginal('redaksi_approval_status') !== 'rejected') {
            $this->handleRedaksiRejection($project);
        }

        // Handle keuangan approval
        if ($project->keuangan_approval_status === 'approved' && 
            $project->getOriginal('keuangan_approval_status') !== 'approved') {
            $this->handleKeuanganApproval($project);
        }

        // Handle keuangan rejection
        if ($project->keuangan_approval_status === 'rejected' && 
            $project->getOriginal('keuangan_approval_status') !== 'rejected') {
            $this->handleKeuanganRejection($project);
        }
    }

    /**
     * Create project proposal transaction
     */
    private function createProjectProposalTransaction(Project $project): void
    {
        Transaksi::create([
            'nomor_transaksi' => $this->generateTransactionNumber($project),
            'jenis_transaksi' => 'pengeluaran',
            'tanggal_transaksi' => now(),
            'nama_transaksi' => 'Proposal Anggaran: ' . $project->nama_project,
            'deskripsi' => $project->proposal_description ?: 'Anggaran untuk project ' . $project->nama_project,
            'total_amount' => $project->proposal_budget,
            'status' => 'draft',
            'workflow_type' => 'project_proposal',
            'project_id' => $project->id,
            'created_by' => $project->created_by,
        ]);
    }

    /**
     * Handle redaksi approval
     */
    private function handleRedaksiApproval(Project $project): void
    {
        $transaksi = Transaksi::where('project_id', $project->id)
                             ->where('workflow_type', 'project_proposal')
                             ->first();
        
        if ($transaksi) {
            $transaksi->update([
                'status' => 'pending',
                'redaksi_approved_by' => $project->redaksi_approved_by,
                'redaksi_approved_at' => $project->redaksi_approved_at,
                'redaksi_notes' => $project->redaksi_notes,
            ]);
        }
    }

    /**
     * Handle redaksi rejection
     */
    private function handleRedaksiRejection(Project $project): void
    {
        $transaksi = Transaksi::where('project_id', $project->id)
                             ->where('workflow_type', 'project_proposal')
                             ->first();
        
        if ($transaksi) {
            $transaksi->update([
                'status' => 'rejected',
                'redaksi_approved_by' => $project->redaksi_approved_by,
                'redaksi_approved_at' => $project->redaksi_approved_at,
                'redaksi_notes' => $project->redaksi_notes,
                'catatan_approval' => 'Ditolak oleh redaksi: ' . $project->redaksi_notes,
            ]);
        }
    }

    /**
     * Handle keuangan approval
     */
    private function handleKeuanganApproval(Project $project): void
    {
        $transaksi = Transaksi::where('project_id', $project->id)
                             ->where('workflow_type', 'project_proposal')
                             ->first();
        
        if ($transaksi) {
            $transaksi->update([
                'status' => 'approved',
                'approved_by' => $project->keuangan_approved_by,
                'approved_at' => $project->keuangan_approved_at,
                'catatan_approval' => $project->keuangan_notes,
            ]);
        }
    }

    /**
     * Handle keuangan rejection
     */
    private function handleKeuanganRejection(Project $project): void
    {
        $transaksi = Transaksi::where('project_id', $project->id)
                             ->where('workflow_type', 'project_proposal')
                             ->first();
        
        if ($transaksi) {
            $transaksi->update([
                'status' => 'rejected',
                'approved_by' => $project->keuangan_approved_by,
                'approved_at' => $project->keuangan_approved_at,
                'catatan_approval' => 'Ditolak oleh keuangan: ' . $project->keuangan_notes,
            ]);
        }
    }

    /**
     * Generate unique transaction number
     */
    private function generateTransactionNumber(Project $project): string
    {
        $prefix = 'PRJ-' . $project->id . '-';
        $suffix = now()->format('YmdHis');
        
        return $prefix . $suffix;
    }
}