<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisburseLoanRequest;
use App\Http\Requests\InstallmentPaymentRequest;
use App\Http\Requests\LenderSettlementRequest;
use App\Http\Requests\LoanProposalRequest;
use App\Http\Requests\VoidJournalRequest;
use App\Models\Loan;
use App\Models\Product;
use App\Services\ChannelingJournalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LoanOperationController extends Controller
{
    public function __construct(private readonly ChannelingJournalService $journalService)
    {
    }

    public function propose(LoanProposalRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $product = Product::query()->with(['fields', 'financials.account'])->findOrFail($validated['product_id']);

        $debtorData = (array) ($validated['debtor_data'] ?? []);
        $submissionData = (array) ($validated['submission_data'] ?? []);
        $providedFinancialData = (array) ($validated['financial_data'] ?? []);

        $errors = [];
        foreach ($product->fields as $field) {
            if (!$field->is_required) {
                continue;
            }

            if ($field->group === 'informasi_debitur' && blank($debtorData[$field->field_name] ?? null)) {
                $errors['debtor_data.' . $field->field_name] = ['Field wajib untuk produk ini.'];
            }

            if ($field->group === 'data_pengajuan' && blank($submissionData[$field->field_name] ?? null)) {
                $errors['submission_data.' . $field->field_name] = ['Field wajib untuk produk ini.'];
            }
        }

        $financialSnapshot = [];
        foreach ($product->financials as $financial) {
            $submitted = $providedFinancialData[$financial->item_name] ?? null;
            $isIncluded = (bool) $financial->is_included_in_simulation;

            if ($isIncluded && blank($submitted)) {
                $submitted = $financial->default_value;
            }

            $financialSnapshot[$financial->item_name] = [
                'value' => (float) $submitted,
                'configured_default_value' => (float) $financial->default_value,
                'account_id' => $financial->account_id,
                'account_code' => $financial->account?->code,
                'account_name' => $financial->account?->name,
                'calculation_type' => $financial->calculation_type,
                'transaction_type' => $financial->transaction_type,
                'is_deducted_at_disbursement' => (bool) $financial->is_deducted_at_disbursement,
                'is_included_in_simulation' => $isIncluded,
            ];
        }

        if (!empty($errors)) {
            return response()->json([
                'message' => 'Data pengajuan belum sesuai konfigurasi produk.',
                'errors' => $errors,
            ], 422);
        }

        $loan = Loan::query()->create([
            'loan_number' => $validated['loan_number'],
            'product_id' => $validated['product_id'],
            'nasabah_id' => $validated['nasabah_id'],
            'lender_id' => $validated['lender_id'],
            'amount_plafond' => $validated['amount_plafond'],
            'interest_rate' => $validated['interest_rate'],
            'provision_fee' => (float) ($validated['provision_fee'] ?? ($financialSnapshot['provisi']['value'] ?? 0)),
            'admin_fee' => (float) ($validated['admin_fee'] ?? ($financialSnapshot['administrasi']['value'] ?? 0)),
            'status' => 'propose',
            'debtor_data' => $debtorData,
            'submission_data' => $submissionData,
            'financial_data' => $financialSnapshot,
        ]);

        return response()->json([
            'message' => 'Pengajuan pinjaman tersimpan.',
            'loan' => $loan,
        ], 201);
    }

    public function disburse(DisburseLoanRequest $request, Loan $loan): JsonResponse
    {
        $loan->loadMissing('product');

        $cashTransferred = (float) $request->input('cash_transferred');
        $fundedByLender = (float) $request->input('funded_by_lender', 0);
        $provisionIncome = (float) $request->input('provision_income', $this->getFinancialValue($loan, 'provisi'));
        $adminIncome = (float) $request->input('admin_income', $this->getFinancialValue($loan, 'administrasi'));
        $disbursementTotal = round($cashTransferred + $fundedByLender + $provisionIncome + $adminIncome, 2);

        if (abs($disbursementTotal - (float) $loan->amount_plafond) > 0.01) {
            return response()->json([
                'message' => 'Total komponen pencairan harus sama dengan amount_plafond pinjaman.',
                'expected' => (float) $loan->amount_plafond,
                'actual' => $disbursementTotal,
            ], 422);
        }

        DB::transaction(function () use ($request, $loan, $cashTransferred, $fundedByLender, $provisionIncome, $adminIncome): void {
            $loan->update([
                'status' => 'active',
                'disbursed_at' => $request->input('transaction_date', now()->toDateString()),
            ]);

            $this->journalService->postDisbursement(
                loan: $loan,
                cashTransferred: $cashTransferred,
                fundedByLender: $fundedByLender,
                provisionIncome: $provisionIncome,
                adminIncome: $adminIncome,
                transactionDate: $request->input('transaction_date'),
                reference: $request->input('reference', 'DISB-' . $loan->loan_number)
            );
        });

        return response()->json(['message' => 'Pencairan berhasil diposting.']);
    }

    public function receiveInstallment(InstallmentPaymentRequest $request, Loan $loan): JsonResponse
    {
        DB::transaction(function () use ($request, $loan): void {
            $this->journalService->postInstallmentPayment(
                loan: $loan,
                principalAmount: (float) $request->input('principal_amount'),
                interestAmount: (float) $request->input('interest_amount'),
                transactionDate: $request->input('transaction_date'),
                reference: $request->input('reference', 'ANGS-' . $loan->loan_number . '-' . now()->format('YmdHis'))
            );
        });

        return response()->json(['message' => 'Pembayaran angsuran berhasil diposting.']);
    }

    public function settleToLender(LenderSettlementRequest $request, Loan $loan): JsonResponse
    {
        DB::transaction(function () use ($request, $loan): void {
            $this->journalService->postSettlementToLender(
                loan: $loan,
                principalSettle: (float) $request->input('principal_settle'),
                interestSettle: (float) $request->input('interest_settle'),
                transactionDate: $request->input('transaction_date'),
                reference: $request->input('reference', 'SETL-' . $loan->loan_number . '-' . now()->format('YmdHis'))
            );
        });

        return response()->json(['message' => 'Setoran ke pendana berhasil diposting.']);
    }

    public function voidJournal(VoidJournalRequest $request): JsonResponse
    {
        $reversedCount = $this->journalService->reverseByReference(
            reference: $request->input('reference'),
            transactionDate: $request->input('transaction_date'),
            reason: $request->input('reason'),
            reversalReference: $request->input('reversal_reference')
        );

        return response()->json([
            'message' => 'Jurnal berhasil di-void dengan reversal.',
            'reversed_count' => $reversedCount,
        ]);
    }

    private function getFinancialValue(Loan $loan, string $itemName): float
    {
        $snapshot = (array) ($loan->financial_data ?? []);
        $row = (array) ($snapshot[$itemName] ?? []);

        return (float) ($row['value'] ?? 0);
    }
}
