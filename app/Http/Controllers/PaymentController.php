<?php

namespace App\Http\Controllers;

use App\Models\Banpot;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PaymentController extends Controller
{
    private const MONTHS = [
        ['value' => 1, 'label' => 'Januari'],
        ['value' => 2, 'label' => 'Februari'],
        ['value' => 3, 'label' => 'Maret'],
        ['value' => 4, 'label' => 'April'],
        ['value' => 5, 'label' => 'Mei'],
        ['value' => 6, 'label' => 'Juni'],
        ['value' => 7, 'label' => 'Juli'],
        ['value' => 8, 'label' => 'Agustus'],
        ['value' => 9, 'label' => 'September'],
        ['value' => 10, 'label' => 'Oktober'],
        ['value' => 11, 'label' => 'November'],
        ['value' => 12, 'label' => 'Desember'],
    ];

    public function index(Request $request)
    {
        $year = $request->query('tahun');
        $month = $request->query('bulan');

        $years = collect(range((int) now()->year - 5, (int) now()->year + 5))
            ->sortDesc()
            ->values()
            ->all();

        $payments = $this->buildPaymentRows($year, $month);

        return view('payments.index', [
            'payments' => $payments,
            'years' => $years,
            'months' => self::MONTHS,
            'selectedYear' => $year,
            'selectedMonth' => $month,
        ]);
    }

    private function buildPaymentRows(?string $year, ?string $month): array
    {
        $rows = [];

        $journalGroups = JournalEntry::query()
            ->with(['account'])
            ->when($year !== null && $year !== '', fn ($query) => $query->whereYear('transaction_date', (int) $year))
            ->when($month !== null && $month !== '', fn ($query) => $query->whereMonth('transaction_date', (int) $month))
            ->where('posting_status', 'posted')
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy(fn ($entry) => trim((string) ($entry->reference ?: ($entry->transaction_date?->toDateString() ?? '') . '-' . ($entry->loan_id ?? ''))));

        foreach ($journalGroups as $group) {
            $first = $group->first();
            $rows[] = [
                'source' => 'realisasi',
                'tanggal' => $first?->transaction_date?->toDateString() ?? '-',
                'keterangan' => $this->resolveJournalKeterangan($group),
                'jumlah' => $this->sumJournalAmount($group),
                'payment_type' => $this->resolveJournalType($group),
                'allocations' => $group->map(fn ($entry) => [
                    'label' => $entry->account?->name ?? 'Akun',
                    'amount' => (float) ($entry->debit > 0 ? $entry->debit : $entry->credit),
                    'direction' => $entry->debit > 0 ? 'Debet' : 'Kredit',
                    'reference' => $entry->reference,
                ])->values()->all(),
            ];
        }

        $banpotRows = Banpot::query()
            ->when($year !== null && $year !== '', fn ($query) => $query->where('tahun', (int) $year))
            ->when($month !== null && $month !== '', fn ($query) => $query->where('bulan', (int) $month))
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        foreach ($banpotRows as $banpot) {
            $amount = $this->parseCurrency($banpot->total) ?? $this->parseCurrency($banpot->plafond) ?? 0;

            $rows[] = [
                'source' => 'banpot',
                'tanggal' => sprintf('%04d-%02d-01', (int) $banpot->tahun, (int) $banpot->bulan),
                'keterangan' => trim((string) ($banpot->keterangan ?: 'Banpot') . ' - ' . ($banpot->nama_debitur ?: $banpot->nopen ?: 'Banpot')),
                'jumlah' => (float) $amount,
                'payment_type' => 'Banpot',
                'allocations' => [[
                    'label' => 'Detail Banpot',
                    'amount' => (float) $amount,
                    'direction' => 'Debet',
                    'reference' => 'NOPEN: ' . ($banpot->nopen ?? '-') . ' | Bank: ' . ($banpot->bank ?? '-'),
                ]],
            ];
        }

        usort($rows, fn ($a, $b) => strcmp($b['tanggal'], $a['tanggal']));

        return $rows;
    }

    private function resolveJournalKeterangan(Collection $group): string
    {
        $first = $group->first();

        if (!$first) {
            return 'Pembayaran';
        }

        if ($first->description) {
            return $first->description;
        }

        if ($first->reference) {
            return $first->reference;
        }

        return 'Pembayaran';
    }

    private function resolveJournalType(Collection $group): string
    {
        $first = $group->first();

        if (!$first) {
            return 'Realisasi';
        }

        if (str_contains(strtolower((string) $first->description), 'pencairan')
            || str_contains(strtolower((string) $first->description), 'angsuran')
            || str_contains(strtolower((string) $first->description), 'setoran')) {
            return 'Realisasi';
        }

        return 'Realisasi';
    }

    private function sumJournalAmount(Collection $group): float
    {
        return (float) $group->sum(fn ($entry) => (float) ($entry->debit > 0 ? $entry->debit : $entry->credit));
    }

    private function parseCurrency(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = preg_replace('/[^0-9,.-]/', '', (string) $value);

        if ($normalized === '' || $normalized === '-' || $normalized === '.') {
            return null;
        }

        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
