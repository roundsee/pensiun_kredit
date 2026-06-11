<?php

namespace App\Services;

class FinancialCalculatorService
{
    /**
     * Calculate the amount based on formula type.
     */
    public function calculateAmount(string $formulaType, float $amount, float $baseValue): float
    {
        return match ($formulaType) {
            'percentage' => ($baseValue * $amount) / 100,
            'fixed' => $amount,
            'custom' => $this->customFormula($amount, $baseValue),
            default => throw new \InvalidArgumentException('Invalid formula type'),
        };
    }

    /**
     * Validate the component amount.
     */
    public function validateComponent(float $inputAmount, float $calculatedAmount, string $formulaType): bool|string
    {
        if ($formulaType === 'custom') {
            // Custom validation logic can be added here
            return true;
        }
        if (abs($inputAmount - $calculatedAmount) > 0.01) {
            return "Input amount does not match calculated amount.";
        }
        return true;
    }

    /**
     * Example custom formula (can be extended as needed).
     */
    protected function customFormula(float $amount, float $baseValue): float
    {
        // Implement custom logic here
        return $amount; // Default: just return the amount
    }
}
