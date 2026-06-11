<?php

namespace Database\Seeders;

use App\Models\JournalEntry;
use App\Models\Loan;
use App\Services\ChannelingJournalService;
use Illuminate\Database\Seeder;

class ChannelingJournalSeeder extends Seeder
{
    public function run(): void
    {
        $loan = Loan::query()->with('lender')->first();
        if (!$loan) {
            return;
        }

        $service = app(ChannelingJournalService::class);

        if (!JournalEntry::query()->where('reference', 'DEMO-CHN-DISB-001')->exists()) {
            $service->postDisbursement(
                loan: $loan,
                cashTransferred: 49350000,
                fundedByLender: 0,
                provisionIncome: 500000,
                adminIncome: 150000,
                transactionDate: now()->subDays(8),
                reference: 'DEMO-CHN-DISB-001'
            );
        }

        if (!JournalEntry::query()->where('reference', 'DEMO-CHN-RMB-001')->exists()) {
            JournalEntry::query()->create([
                'transaction_date' => now()->subDays(7)->toDateString(),
                'account_id' => \App\Models\Account::query()->where('code', '1205')->value('id'),
                'debit' => 49350000,
                'credit' => 0,
                'nasabah_id' => $loan->nasabah_id,
                'loan_id' => $loan->id,
                'lender_id' => $loan->lender_id,
                'reference' => 'DEMO-CHN-RMB-001',
                'description' => 'Pengakuan piutang reimburse pendana',
            ]);
            $service->postReimbursement(
                loan: $loan,
                amount: 49350000,
                transactionDate: now()->subDays(6),
                reference: 'DEMO-CHN-RMB-001'
            );
        }

        if (!JournalEntry::query()->where('reference', 'DEMO-CHN-ANGS-001')->exists()) {
            $service->postInstallmentPayment(
                loan: $loan,
                principalAmount: 1000000,
                interestAmount: 100000,
                transactionDate: now()->subDays(2),
                reference: 'DEMO-CHN-ANGS-001'
            );
        }

        if (!JournalEntry::query()->where('reference', 'DEMO-CHN-STL-001')->exists()) {
            $service->postSettlementToLender(
                loan: $loan,
                principalSettle: 1000000,
                interestSettle: 70000,
                transactionDate: now()->toDateString(),
                reference: 'DEMO-CHN-STL-001'
            );
        }
    }
}
