@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="card mb-3">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>List Payment</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('payment.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @selected((string) request('tahun') === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select">
                                <option value="">Semua</option>
                                @foreach($months as $month)
                                    <option value="{{ $month['value'] }}" @selected((string) request('bulan') === (string) $month['value'])>{{ $month['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('payment.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;"></th>
                                <th>Tanggal</th>
                                <th>Keterangan</th>
                                <th class="text-end">Jumlah</th>
                                <th>Payment Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $index => $payment)
                                @php
                                    $isExpanded = false;
                                @endphp
                                <tr>
                                    <td class="text-center align-middle">
                                        @if(!empty($payment['allocations']))
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#payment-alloc-{{ $index }}" aria-expanded="false" aria-controls="payment-alloc-{{ $index }}">&gt;</button>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($payment['tanggal'])->format('d-m-Y') }}</td>
                                    <td>{{ $payment['keterangan'] }}</td>
                                    <td class="text-end">{{ number_format($payment['jumlah'], 0, ',', '.') }}</td>
                                    <td>{{ $payment['payment_type'] }}</td>
                                </tr>
                                @if(!empty($payment['allocations']))
                                    <tr class="collapse" id="payment-alloc-{{ $index }}">
                                        <td colspan="5" class="bg-light">
                                            <div class="p-2">
                                                <table class="table table-sm mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Alokasi</th>
                                                            <th class="text-end">Jumlah</th>
                                                            <th>Direction</th>
                                                            <th>Reference</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($payment['allocations'] as $allocation)
                                                            <tr>
                                                                <td>{{ $allocation['label'] }}</td>
                                                                <td class="text-end">{{ number_format($allocation['amount'], 0, ',', '.') }}</td>
                                                                <td>{{ $allocation['direction'] }}</td>
                                                                <td>{{ $allocation['reference'] ?? '-' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Data payment tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
