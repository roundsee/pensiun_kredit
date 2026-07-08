@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="card mb-3">
                <div class="card-header fw-bold d-flex justify-content-between align-items-center">
                    <span>List Banpot</span>
                    <a href="{{ route('banpot.create') }}" class="btn btn-sm btn-primary">Import Banpot</a>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('banpot.index') }}" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select">
                                <option value="">Semua</option>
                                @foreach($months as $month)
                                    <option value="{{ $month['value'] }}" @selected((string) request('bulan') === (string) $month['value'])>{{ $month['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select">
                                <option value="">Semua</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" @selected((string) request('tahun') === (string) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Bank</label>
                            <select name="bank" class="form-select">
                                <option value="">Semua</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank }}" @selected(request('bank') === $bank)>{{ $bank }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Nama Debitur</label>
                            <input type="text" name="nama_debitur" value="{{ request('nama_debitur') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Instansi</label>
                            <input type="text" name="instansi" value="{{ request('instansi') }}" class="form-control" placeholder="PENGELOLA PENSIUN">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <input type="text" name="status" value="{{ request('status') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Nopen</label>
                            <input type="text" name="nopen" value="{{ request('nopen') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Customer ID</label>
                            <input type="text" name="customer_id" value="{{ request('customer_id') }}" class="form-control">
                        </div>
                        <div class="col-12 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Search</button>
                            <a href="{{ route('banpot.index') }}" class="btn btn-outline-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body table-responsive">
                    <table class="table table-sm table-striped table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Bank</th>
                                <th>Bulan</th>
                                <th>Tahun</th>
                                <th>NO</th>
                                <th>NO PK</th>
                                <th>NOPEN</th>
                                <th>NOREK</th>
                                <th>TOTAL</th>
                                <th>CABANG</th>
                                <th>STATUS</th>
                                <th>PLAFOND</th>
                                <th>SELISIH</th>
                                <th>GIRO MITRA</th>
                                <th>KETERANGAN</th>
                                <th>CUSTOMER ID</th>
                                <th>NAMA DEBITUR</th>
                                <th>JENIS TAGIHAN</th>
                                <th>PENGELOLA PENSIUN</th>
                                <th>PENDEBETAN ANGSURAN</th>
                                <th>NAMA MITRA CHANNELING</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($banpots as $banpot)
                                <tr>
                                    <td>{{ $banpot->bank }}</td>
                                    <td>{{ $banpot->bulan }}</td>
                                    <td>{{ $banpot->tahun }}</td>
                                    <td>{{ $banpot->no ?? '-' }}</td>
                                    <td>{{ $banpot->no_pk ?? '-' }}</td>
                                    <td>{{ $banpot->nopen ?? '-' }}</td>
                                    <td>{{ $banpot->norek ?? '-' }}</td>
                                    <td>{{ $banpot->total ?? '-' }}</td>
                                    <td>{{ $banpot->cabang ?? '-' }}</td>
                                    <td>{{ $banpot->status ?? '-' }}</td>
                                    <td>{{ $banpot->plafond ?? '-' }}</td>
                                    <td>{{ $banpot->selisih ?? '-' }}</td>
                                    <td>{{ $banpot->giro_mitra ?? '-' }}</td>
                                    <td>{{ $banpot->keterangan ?? '-' }}</td>
                                    <td>{{ $banpot->customer_id ?? '-' }}</td>
                                    <td>{{ $banpot->nama_debitur ?? '-' }}</td>
                                    <td>{{ $banpot->jenis_tagihan ?? '-' }}</td>
                                    <td>{{ $banpot->pengelola_pensiun ?? '-' }}</td>
                                    <td>{{ $banpot->pendebetan_angsuran ?? '-' }}</td>
                                    <td>{{ $banpot->nama_mitra_channeling ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="20" class="text-center text-muted">Data tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $banpots->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection