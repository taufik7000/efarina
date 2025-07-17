<?php

namespace App\Notifications;

use App\Models\ProjectProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProposalApproved extends Notification implements ShouldQueue
{
    use Queueable;

    protected ProjectProposal $proposal;

    public function __construct(ProjectProposal $proposal)
    {
        $this->proposal = $proposal;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $projectCreated = $this->proposal->hasProject() ? 'dan project telah dibuat' : '';
        
        return [
            'title' => 'Proposal Disetujui! 🎉',
            'body' => "Proposal '{$this->proposal->judul_proposal}' telah disetujui {$projectCreated}.",
            'icon' => 'heroicon-o-check-circle',
            'iconColor' => 'success',
            'actions' => [
                [
                    'label' => 'Lihat Proposal',
                    'url' => "/team/project-proposals/{$this->proposal->id}",
                ]
            ],
            'data' => [
                'proposal_id' => $this->proposal->id,
                'proposal_title' => $this->proposal->judul_proposal,
                'reviewer_name' => $this->proposal->reviewedBy?->name,
                'review_notes' => $this->proposal->catatan_review,
                'project_created' => $this->proposal->hasProject(),
                'project_id' => $this->proposal->project_id,
                'type' => 'proposal_approved',
            ]
        ];
    }
}