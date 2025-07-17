<?php

namespace App\Observers;

use App\Models\ProjectProposal;
use App\Models\User;
use App\Notifications\NewProposalSubmitted;
use App\Notifications\ProposalApproved;
use App\Notifications\ProposalRejected;
use Filament\Notifications\Notification;

class ProjectProposalObserver
{
    public function created(ProjectProposal $proposal): void
    {
        // Notify semua user dengan role redaksi saat ada proposal baru
        $this->notifyRedaksiNewProposal($proposal);
    }

    public function updated(ProjectProposal $proposal): void
    {
        // Cek jika status berubah dari pending ke approved/rejected
        $originalStatus = $proposal->getOriginal('status');
        $currentStatus = $proposal->status;

        if ($originalStatus === 'pending' && $currentStatus === 'approved') {
            $this->notifyProposalApproved($proposal);
        }

        if ($originalStatus === 'pending' && $currentStatus === 'rejected') {
            $this->notifyProposalRejected($proposal);
        }
    }

    private function notifyRedaksiNewProposal(ProjectProposal $proposal): void
    {
        try {
            \Log::info('Starting notification process for proposal: ' . $proposal->id);

            // Ambil semua user redaksi
            $redaksiUsers = $this->getRedaksiUsers();
            
            \Log::info('Found redaksi users: ' . $redaksiUsers->count());

            foreach ($redaksiUsers as $user) {
                // Skip jika yang buat proposal adalah redaksi sendiri
                if ($user->id === $proposal->created_by) {
                    \Log::info('Skipping creator: ' . $user->name);
                    continue;
                }

                \Log::info('Sending notification to: ' . $user->name);

                // Kirim notification menggunakan Laravel notification (bukan Filament)
                $user->notify(new NewProposalSubmitted($proposal));
                
                \Log::info('Notification queued for: ' . $user->name);

                // Kirim juga realtime notification dengan URL ke panel redaksi
                Notification::make()
                    ->title('Proposal Baru Masuk')
                    ->body("Proposal '{$proposal->judul_proposal}' dari {$proposal->createdBy->name}")
                    ->icon('heroicon-o-light-bulb')
                    ->iconColor('warning')
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('review')
                            ->label('Review')
                            ->url($this->getProposalUrl($proposal, 'redaksi'))
                    ])
                    ->sendToDatabase($user);
                    
                \Log::info('Realtime notification sent to: ' . $user->name);
            }

        } catch (\Exception $e) {
            \Log::error('Error in notifyRedaksiNewProposal: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    private function notifyProposalApproved(ProjectProposal $proposal): void
    {
        $creator = $proposal->createdBy;
        
        // Kirim database notification ke pembuat proposal
        $creator->notify(new ProposalApproved($proposal));

        // Kirim realtime notification ke panel team
        Notification::make()
            ->title('Proposal Disetujui! 🎉')
            ->body("Proposal '{$proposal->judul_proposal}' telah disetujui")
            ->icon('heroicon-o-check-circle')
            ->iconColor('success')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view')
                    ->label('Lihat Detail')
                    ->url($this->getProposalUrl($proposal, 'team'))
            ])
            ->sendToDatabase($creator);
    }

    private function notifyProposalRejected(ProjectProposal $proposal): void
    {
        $creator = $proposal->createdBy;
        
        // Kirim database notification ke pembuat proposal
        $creator->notify(new ProposalRejected($proposal));

        // Kirim realtime notification ke panel team
        Notification::make()
            ->title('Proposal Ditolak')
            ->body("Proposal '{$proposal->judul_proposal}' ditolak. Lihat feedback untuk perbaikan.")
            ->icon('heroicon-o-x-circle')
            ->iconColor('danger')
            ->actions([
                \Filament\Notifications\Actions\Action::make('view_feedback')
                    ->label('Lihat Feedback')
                    ->url($this->getProposalUrl($proposal, 'team'))
            ])
            ->sendToDatabase($creator);
    }

    private function getRedaksiUsers(): \Illuminate\Support\Collection
    {
        // Untuk Spatie Laravel Permission
        try {
            // Method 1: Menggunakan role() method dari Spatie
            $redaksiByRole = User::role('redaksi')->get();
            \Log::info('Redaksi by Spatie role method: ' . $redaksiByRole->count());

            if ($redaksiByRole->count() > 0) {
                return $redaksiByRole;
            }

            // Method 2: Fallback - cek dengan hasRole
            $allUsers = User::all();
            $redaksiUsers = $allUsers->filter(function ($user) {
                return $user->hasRole('redaksi');
            });
            \Log::info('Redaksi by hasRole filter: ' . $redaksiUsers->count());

            if ($redaksiUsers->count() > 0) {
                return $redaksiUsers;
            }

            // Method 3: Coba dengan nama role berbeda
            $redaksiVariations = ['redaksi', 'Redaksi', 'REDAKSI', 'admin', 'Admin'];
            foreach ($redaksiVariations as $roleName) {
                $users = User::role($roleName)->get();
                \Log::info("Checking role '{$roleName}': " . $users->count() . " users found");
                
                if ($users->count() > 0) {
                    return $users;
                }
            }

            // Method 4: Debug - tampilkan semua role yang ada
            $allRoles = \Spatie\Permission\Models\Role::all();
            \Log::info('Available roles in database: ' . $allRoles->pluck('name')->toArray());

            // Fallback - return empty collection
            \Log::warning('No redaksi users found with any method');
            return collect();

        } catch (\Exception $e) {
            \Log::error('Error getting redaksi users: ' . $e->getMessage());
            return collect();
        }
    }

    private function getProposalUrl(ProjectProposal $proposal, $panel = 'redaksi'): string
    {
        // URL sesuai dengan panel yang benar
        $baseUrl = config('app.url');
        
        // Untuk redaksi panel
        if ($panel === 'redaksi') {
            return "{$baseUrl}/redaksi/project-proposals/{$proposal->id}/view";
        }
        
        // Untuk team panel
        if ($panel === 'team') {
            return "{$baseUrl}/team/project-proposals/{$proposal->id}";
        }
        
        // Fallback
        return "{$baseUrl}/admin/project-proposals/{$proposal->id}";
    }
}