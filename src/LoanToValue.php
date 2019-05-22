<?php

namespace MortgageCalculator;

class LoanToValue
{
    public $ltv;

    public $cltv;

    public function __construct(float $estimatedValue, float $totalLoanAmount, float $subFinanceAmount)
    {
        $this->ltv  = self::convertToPercent($totalLoanAmount / $estimatedValue);
        $this->cltv = self::convertToPercent(($totalLoanAmount + $subFinanceAmount) / $estimatedValue);
    }

    private static function convertToPercent($value)
    {
        return round($value * 100, 3);
    }
}
