<?php

namespace App\Http\Controllers;

use App\Models\JobVacancy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareerController extends Controller
{
    /**
     * Menampilkan daftar semua lowongan pekerjaan yang terbuka.
     */
    public function index(): View
    {
        $vacancies = JobVacancy::where('status', 'open')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->paginate(10);

        return view('career.index', compact('vacancies'));
    }

    /**
     * Menampilkan detail dari satu lowongan pekerjaan.
     */
    public function show(JobVacancy $jobVacancy): View
    {
        // Pastikan hanya lowongan yang terbuka yang bisa diakses
        if ($jobVacancy->status !== 'open' || $jobVacancy->published_at > now()) {
            abort(404);
        }

        return view('career.show', compact('jobVacancy'));
    }
}