<?php

namespace App\Notifications;

use App\Models\ProjectProposal;
use Filament\Notifications\DatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewProposalSubmitted extends Notification implements ShouldQueue
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
            'title' => 'Proposal Baru Masuk',
            'body' => "Proposal '{$this->proposal->judul_proposal}' dari {$this->proposal->createdBy->name} menunggu review.",
            'icon' => 'heroicon-o-light-bulb',
            'iconColor' => 'warning',
            'actions' => [
                [
                    'label' => 'Review Sekarang',
                    'url' => "/redaksi/project-proposals/{$this->proposal->id}", // Panel redaksi
                ]
            ],
            'data' => [
                'proposal_id' => $this->proposal->id,
                'proposal_title' => $this->proposal->judul_proposal,
                'creator_name' => $this->proposal->createdBy->name,
                'creator_id' => $this->proposal->created_by,
                'priority' => $this->proposal->prioritas,
                'type' => 'new_proposal',
            ]
        ];
    }
}