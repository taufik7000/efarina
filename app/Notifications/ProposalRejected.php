<?php

namespace App\Notifications;

use App\Models\ProjectProposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ProposalRejected extends Notification implements ShouldQueue
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
        return [
            'title' => 'Proposal Ditolak',
            'body' => "Proposal '{$this->proposal->judul_proposal}' ditolak. Silakan perbaiki dan ajukan kembali.",
            'icon' => 'heroicon-o-x-circle',
            'iconColor' => 'danger',
            'actions' => [
                [
                    'label' => 'Lihat Feedback',
                    'url' => "/team/project-proposals/{$this->proposal->id}",
                ],
                [
                    'label' => 'Buat Proposal Baru',
                    'url' => '/team/project-proposals/create',
                ]
            ],
            'data' => [
                'proposal_id' => $this->proposal->id,
                'proposal_title' => $this->proposal->judul_proposal,
                'reviewer_name' => $this->proposal->reviewedBy?->name,
                'rejection_reason' => $this->proposal->catatan_review,
                'type' => 'proposal_rejected',
            ]
        ];
    }
}