@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2 text-gray-800">Dashboard Statistik Platinum</h1>
        <span class="badge bg-primary p-2">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-primary border-4 h-100 p-2">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Plafond (Minggu Ini)</h6>
                    <h4 class="text-primary fw-bold mb-0">Rp {{ number_format($totalPlafondMingguan, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-success border-4 h-100 p-2">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Total Plafond (Bulan Ini)</h6>
                    <h4 class="text-success fw-bold mb-0">Rp {{ number_format($totalPlafondBulanan, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-warning border-4 h-100 p-2">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Pengajuan (Mng / Bln)</h6>
                    <h4 class="text-warning fw-bold mb-0">
                        {{ $jumlahPengajuanMingguan }} <span class="text-muted fs-6">/ {{ $jumlahPengajuanBulanan }} Data</span>
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm border-start border-info border-4 h-100 p-2">
                <div class="card-body">
                    <h6 class="text-muted text-uppercase small fw-bold">Pencairan Dropping (Mng / Bln)</h6>
                    <h4 class="text-info fw-bold mb-0">
                        {{ $jumlahPencairanMingguan }} <span class="text-muted fs-6">/ {{ $jumlahPencairanBulanan }} Data</span>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 fw-bold text-secondary">
                    Grafik Tren Pengajuan & Dropping Pencairan (7 Hari Terakhir)
                </div>
                <div class="card-body">
                    <div style="height: 350px;">
                        <canvas id="statistikChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('statistikChart').getContext('2d');
        
        // Data dari Controller Laravel dikonversi ke JSON JavaScript
        const labels = {!! json_encode($chartLabels) !!};
        const dataPengajuan = {!! json_encode($chartDataPengajuan) !!};
        const dataPencairan = {!! json_encode($chartDataPencairan) !!};

        new Chart(ctx, {
            type: 'line', // Menggunakan grafik garis agar tren terlihat naik/turun dengan jelas
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Jumlah Pengajuan Baru',
                        data: dataPengajuan,
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: 'Jumlah Pencairan (Dropping)',
                        data: dataPencairan,
                        borderColor: '#0dcaf0',
                        backgroundColor: 'rgba(13, 202, 240, 0.1)',
                        borderWidth: 3,
                        tension: 0.3,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Skala grafik lompat 1 angka (bulat) karena menghitung jumlah berkas
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    });
</script>
@endsection