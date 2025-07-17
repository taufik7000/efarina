<?php
// app/Http/Controllers/SecureFileController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\PengajuanAnggaran;
use App\Models\Project;

class SecureFileController extends Controller
{
    /**
     * Serve bukti realisasi files securely
     */
    public function serveBuktiRealisasi(Request $request, $projectId, $pengajuanId, $filename)
    {
        // 1. Check if user is authenticated
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        $user = Auth::user();

        // 2. Find the pengajuan anggaran
        $pengajuan = PengajuanAnggaran::where('id', $pengajuanId)
            ->where('project_id', $projectId)
            ->first();

        if (!$pengajuan) {
            abort(404, 'Pengajuan anggaran not found');
        }

        // 3. Check user permissions
        if (!$this->canAccessBuktiRealisasi($user, $pengajuan)) {
            abort(403, 'Access denied');
        }

        // 4. Construct file path
        $filePath = "bukti-realisasi/project-{$projectId}/pengajuan-{$pengajuanId}/{$filename}";

        // 5. Check if file exists
        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found');
        }

        // 6. Get file info
        $file = Storage::disk('local')->get($filePath);
        $mimeType = Storage::disk('local')->mimeType($filePath);

        // 7. Return file with proper headers
        return response($file)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=3600')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    /**
     * Check if user can access bukti realisasi
     */
    private function canAccessBuktiRealisasi($user, $pengajuan): bool
    {
        // Admin dan redaksi bisa akses semua
        if ($user->hasRole(['admin', 'redaksi', 'direktur', 'keuangan'])) {
            return true;
        }

        $project = $pengajuan->project;
        if (!$project) {
            return false;
        }

        // Project Manager bisa akses
        if ($project->project_manager_id === $user->id) {
            return true;
        }

        // Creator pengajuan bisa akses
        if ($pengajuan->created_by === $user->id) {
            return true;
        }

        // Team members bisa akses
        if (is_array($project->team_members) && in_array($user->id, $project->team_members)) {
            return true;
        }

        // Creator project bisa akses
        if ($project->created_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Download file (force download)
     */
    public function downloadBuktiRealisasi(Request $request, $projectId, $pengajuanId, $filename)
    {
        // Same permission checks as serveBuktiRealisasi
        if (!Auth::check()) {
            abort(401, 'Unauthorized');
        }

        $user = Auth::user();
        $pengajuan = PengajuanAnggaran::where('id', $pengajuanId)
            ->where('project_id', $projectId)
            ->first();

        if (!$pengajuan || !$this->canAccessBuktiRealisasi($user, $pengajuan)) {
            abort(403, 'Access denied');
        }

        $filePath = "bukti-realisasi/project-{$projectId}/pengajuan-{$pengajuanId}/{$filename}";

        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('local')->download($filePath, $filename);
    }
}