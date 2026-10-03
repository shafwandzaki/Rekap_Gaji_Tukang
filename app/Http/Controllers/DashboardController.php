<?php

namespace App\Http\Controllers;

use App\Models\Proyek;
use App\Models\Rekap;

class DashboardController extends Controller
{
    public function index()
    {
        // Hanya proyek yang masih punya rekap
        $jumlahProyek = Proyek::has('rekaps')->count();

        // Jumlah seluruh total rekap
        $totalGaji = Rekap::sum('total');

        return view('dashboard', compact('jumlahProyek', 'totalGaji'));
    }
}
