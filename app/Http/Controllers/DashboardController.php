<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DataSimulasi; // Sesuaikan dengan nama model aslimu
use App\Models\DataSimulasiPelengkap;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $startOfWeek = $now->copy()->startOfWeek();
        $endOfWeek = $now->copy()->endOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // --- 1. STATISTIK PLAFOND (Dari tabel DataSimulasi) ---
        $totalPlafondMingguan = DataSimulasi::whereBetween('tgl_permohonan', [$startOfWeek, $endOfWeek])->sum('plafond');
        $totalPlafondBulanan = DataSimulasi::whereBetween('tgl_permohonan', [$startOfMonth, $endOfMonth])->sum('plafond');

        // --- 2. STATISTIK PENGAJUAN (Count DataSimulasi) ---
        $jumlahPengajuanMingguan = DataSimulasi::whereBetween('tgl_permohonan', [$startOfWeek, $endOfWeek])->count();
        $jumlahPengajuanBulanan = DataSimulasi::whereBetween('tgl_permohonan', [$startOfMonth, $endOfMonth])->count();

        // --- 3. STATISTIK PENCAIRAN / DROPPING (Count DataSimulasiPelengkap) ---
        $jumlahPencairanMingguan = DataSimulasiPelengkap::whereBetween('tanggal_dropping', [$startOfWeek, $endOfWeek])->count();
        $jumlahPencairanBulanan = DataSimulasiPelengkap::whereBetween('tanggal_dropping', [$startOfMonth, $endOfMonth])->count();

        // --- 4. DATA UNTUK CHART (Tren Pengajuan 7 Hari Terakhir) ---
        $chartLabels = [];
        $chartDataPengajuan = [];
        $chartDataPencairan = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->translatedFormat('l, d M'); // Format: Senin, 12 Jun

            // Hitung pengajuan per hari
            $chartDataPengajuan[] = DataSimulasi::whereDate('tgl_permohonan', $date)->count();

            // Hitung pencairan per hari
            $chartDataPencairan[] = DataSimulasiPelengkap::whereDate('tanggal_dropping', $date)->count();
        }

        return view('dashboard', compact(
            'totalPlafondMingguan', 'totalPlafondBulanan',
            'jumlahPengajuanMingguan', 'jumlahPengajuanBulanan',
            'jumlahPencairanMingguan', 'jumlahPencairanBulanan',
            'chartLabels', 'chartDataPengajuan', 'chartDataPencairan'
        ));
    }
}
