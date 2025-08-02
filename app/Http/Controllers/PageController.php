<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Menampilkan halaman "Tentang Kami".
     */
    public function about(): View
    {
        // Di sini Anda bisa mengambil data dari database jika perlu,
        // misalnya data anggota tim, dll.
        return view('pages.about');
    }
}