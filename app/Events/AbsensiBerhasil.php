<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AbsensiBerhasil implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Properti publik ini akan otomatis dikirim sebagai data event.
     */
    public string $nama;
    public string $jamMasuk;
    public ?string $fotoProfil; // Kita buat opsional

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, string $jamMasuk)
    {
        $this->nama = $user->name;
        $this->jamMasuk = $jamMasuk;
        // Anda bisa menambahkan logika untuk mengambil URL foto profil jika ada
        $this->fotoProfil = $user->profile_photo_url ?? null; 
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Kita akan menyiarkan event ini di channel publik bernama 'kiosk'.
        return [
            new Channel('kiosk'),
        ];
    }
}