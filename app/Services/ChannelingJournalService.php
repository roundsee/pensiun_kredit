<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChannelingJournalService
{
    private const ACCOUNT_BANK = '1102';
    private const ACCOUNT_PIUTANG_PLAFOND = '1204';
    private const ACCOUNT_PIUTANG_REIMBURSE = '1205';
    private const ACCOUNT_HUTANG_POKOK_PENDANA = '2104';
    private const ACCOUNT_HUTANG_BUNGA_PENDANA = '2105';
    private const ACCOUNT_PENDAPATAN_SHARING_BUNGA = '4104';
    private const ACCOUNT_PENDAPATAN_PROVISI = '4101';
    private const ACCOUNT_PENDAPATAN_ADMIN = '4102';

    /**
     * Jurnal saat pencairan channeling/back-to-back loan.
     */
    public function postDisbursement(
        Loan $loan,
        float $cashTransferred,
        float $fundedByLender,
        float $provisionIncome = 0,
        float $adminIncome = 0,
        Carbon|string|null $transactionDate = null,
        ?string $reference = null
    ): void {
        $date = $this->resolveDate($transactionDate);
        $plafond = round($cashTransferred + $fundedByLender + $provisionIncome + $adminIncome, 2);

        DB::transaction(function () use ($loan, $date, $reference, $plafond, $cashTransferred, $fundedByLender, $provisionIncome, $adminIncome) {
            $this->createEntry($date, self::ACCOUNT_PIUTANG_PLAFOND, $plafond, 0, $loan, $reference, 'Pencairan pinjaman channeling');

            if ($cashTransferred > 0) {
                $this->createEntry($date, self::ACCOUNT_BANK, 0, $cashTransferred, $loan, $reference, 'Transfer pencairan ke nasabah');
            }

            if ($fundedByLender > 0) {
                $this->createEntry($date, self::ACCOUNT_HUTANG_POKOK_PENDANA, 0, $fundedByLender, $loan, $reference, 'Pengakuan hutang pokok ke pendana');
            }

            if ($provisionIncome > 0) {
                $this->createEntry($date, self::ACCOUNT_PENDAPATAN_PROVISI, 0, $provisionIncome, $loan, $reference, 'Pendapatan provisi pinjaman');
            }

            if ($adminIncome > 0) {
                $this->createEntry($date, self::ACCOUNT_PENDAPATAN_ADMIN, 0, $adminIncome, $loan, $reference, 'Pendapatan administrasi pinjaman');
            }
        });
    }

    /**
     * Jurnal saat pendana mereimburse kas koperasi.
     */
    public function postReimbursement(
        Loan $loan,
        float $amount,
        Carbon|string|null $transactionDate = null,
        ?string $reference = null
    ): void {
        $date = $this->resolveDate($transactionDate);

        DB::transaction(function () use ($loan, $date, $reference, $amount) {
            $this->createEntry($date, self::ACCOUNT_BANK, $amount, 0, $loan, $reference, 'Dana reimburse dari pendana');
            $this->createEntry($date, self::ACCOUNT_PIUTANG_REIMBURSE, 0, $amount, $loan, $reference, 'Pelunasan piutang reimburse pendana');
        });
    }

    /**
     * Split jurnal otomatis saat nasabah membayar angsuran.
     */
    public function postInstallmentPayment(
        Loan $loan,
        float $principalAmount,
        float $interestAmount,
        Carbon|string|null $transactionDate = null,
        ?string $reference = null
    ): void {
        $loan->loadMissing('lender');
        $date = $this->resolveDate($transactionDate);

        $shareTotal = (float) $loan->lender->share_lender + (float) $loan->lender->share_koperasi;
        if (abs($shareTotal - 100) > 0.01) {
            throw new InvalidArgumentException('Total share lender dan koperasi harus 100%.');
        }

        $interestForLender = round($interestAmount * ((float) $loan->lender->share_lender / 100), 2);
        $interestForCoop = round($interestAmount - $interestForLender, 2);
        $cashIn = round($principalAmount + $interestAmount, 2);

        DB::transaction(function () use ($loan, $date, $reference, $cashIn, $principalAmount, $interestForLender, $interestForCoop) {
            $this->createEntry($date, self::ACCOUNT_BANK, $cashIn, 0, $loan, $reference, 'Penerimaan angsuran nasabah');
            $this->createEntry($date, self::ACCOUNT_PIUTANG_PLAFOND, 0, $principalAmount, $loan, $reference, 'Pengurangan piutang pokok nasabah');
            $this->createEntry($date, self::ACCOUNT_HUTANG_BUNGA_PENDANA, 0, $interestForLender, $loan, $reference, 'Bagian bunga milik pendana');
            $this->createEntry($date, self::ACCOUNT_PENDAPATAN_SHARING_BUNGA, 0, $interestForCoop, $loan, $reference, 'Bagian bunga milik koperasi');
        });
    }

    /**
     * Jurnal saat koperasi menyetor pokok+bunga ke pendana.
     */
    public function postSettlementToLender(
        Loan $loan,
        float $principalSettle,
        float $interestSettle,
        Carbon|string|null $transactionDate = null,
        ?string $reference = null
    ): void {
        $date = $this->resolveDate($transactionDate);
        $cashOut = round($principalSettle + $interestSettle, 2);

        DB::transaction(function () use ($loan, $date, $reference, $principalSettle, $interestSettle, $cashOut) {
            $this->createEntry($date, self::ACCOUNT_HUTANG_POKOK_PENDANA, $principalSettle, 0, $loan, $reference, 'Setoran pokok ke pendana');
            $this->createEntry($date, self::ACCOUNT_HUTANG_BUNGA_PENDANA, $interestSettle, 0, $loan, $reference, 'Setoran bunga ke pendana');
            $this->createEntry($date, self::ACCOUNT_BANK, 0, $cashOut, $loan, $reference, 'Pembayaran ke pendana');
        });
    }

    /**
     * Membuat jurnal balik berdasarkan nomor referensi untuk kebutuhan void/koreksi.
     */
    public function reverseByReference(
        string $reference,
        Carbon|string|null $transactionDate = null,
        ?string $reason = null,
        ?string $reversalReference = null
    ): int {
        $date = $this->resolveDate($transactionDate);

        return DB::transaction(function () use ($reference, $date, $reason, $reversalReference) {
            $entries = JournalEntry::query()
                ->where('reference', $reference)
                ->where('posting_status', 'posted')
                ->get();

            if ($entries->isEmpty()) {
                throw new InvalidArgumentException('Jurnal dengan reference tersebut tidak ditemukan atau sudah dibalik.');
            }

            $reverseRef = $reversalReference ?: 'REV-' . $reference;

            foreach ($entries as $entry) {
                JournalEntry::create([
                    'transaction_date' => $date->toDateString(),
                    'account_id' => $entry->account_id,
                    'debit' => $entry->credit,
                    'credit' => $entry->debit,
                    'nasabah_id' => $entry->nasabah_id,
                    'loan_id' => $entry->loan_id,
                    'lender_id' => $entry->lender_id,
                    'reference' => $reverseRef,
                    'posting_status' => 'posted',
                    'reversed_from_id' => $entry->id,
                    'void_reason' => $reason,
                    'description' => 'Reversal: ' . $entry->description,
                ]);

                $entry->update([
                    'posting_status' => 'reversed',
                    'void_reason' => $reason,
                ]);
            }

            return $entries->count();
        });
    }

    private function createEntry(
        Carbon $date,
        string $accountCode,
        float $debit,
        float $credit,
        Loan $loan,
        ?string $reference,
        string $description
    ): JournalEntry {
        return JournalEntry::create([
            'transaction_date' => $date->toDateString(),
            'account_id' => $this->getAccountId($accountCode),
            'debit' => round($debit, 2),
            'credit' => round($credit, 2),
            'nasabah_id' => $loan->nasabah_id,
            'loan_id' => $loan->id,
            'lender_id' => $loan->lender_id,
            'reference' => $reference,
            'posting_status' => 'posted',
            'description' => $description,
        ]);
    }

    private function getAccountId(string $code): int
    {
        $accountId = Account::query()->where('code', $code)->value('id');

        if (!$accountId) {
            throw new InvalidArgumentException("Akun dengan kode {$code} tidak ditemukan.");
        }

        return (int) $accountId;
    }

    private function resolveDate(Carbon|string|null $date): Carbon
    {
        if ($date instanceof Carbon) {
            return $date;
        }

        if (is_string($date)) {
            return Carbon::parse($date);
        }

        return now();
    }
}
