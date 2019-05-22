<?php

namespace MortgageCalculator;

const PERCENT_PRECISION = 3;

class LoanToValue
{
    protected $originalLoanAmount;
    protected $estimatedValue;
    protected $purchasePrice;
    protected $totalLoanAmount;
    protected $subFinanceAmount;

    public function __construct(
        float $originalLoanAmount,
        float $estimatedValue,
        float $purchasePrice = null,
        float $totalLoanAmount = null,
        float $subFinanceAmount = null
    )
    {
        $this->originalLoanAmount = $originalLoanAmount;
        $this->estimatedValue     = $estimatedValue;
        $this->purchasePrice      = $purchasePrice;
        $this->totalLoanAmount    = $totalLoanAmount;
        $this->subFinanceAmount   = $subFinanceAmount;
    }

    public function calculate()
    {
        $loanAmount = $this->getLoanAmount();
        $value      = $this->getValue();

        return self::convertToPercent($loanAmount / $value);
    }

    protected function getLoanAmount()
    {
        return $this->originalLoanAmount;
    }

    protected function getValue()
    {
        return $this->estimatedValue;
    }

    protected static function convertToPercent($value)
    {
        return number_format(round($value * 100, PERCENT_PRECISION), PERCENT_PRECISION);
    }
}
