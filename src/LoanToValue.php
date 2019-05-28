<?php

namespace MortgageCalculator;

class LoanToValue
{
    const PERCENT_PRECISION = 3;

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

    /**
     * Divide loan amount by value and return result as formatted string.
     *
     * @return string
     */
    public function calculate()
    {
        $loanAmount = $this->getLoanAmount();
        $value      = $this->getValue();

        return self::convertToPercent($loanAmount / $value);
    }

    /**
     * Determine the total loan amount to be used for the calculation.
     * originalLoanAmount does not include financed closing costs.
     *
     * @return float
     */
    protected function getLoanAmount()
    {
        return $this->originalLoanAmount;
    }

    /**
     * Determine the property value to be used for the calculation.
     *
     * @return float
     */
    protected function getValue()
    {
        return $this->estimatedValue;
    }

    /**
     * Convert float (ratio) to percentage string.
     *
     * @param float $value
     * @return float
     */
    protected static function convertToPercent($value)
    {
        return number_format(round($value * 100, self::PERCENT_PRECISION), self::PERCENT_PRECISION);
    }
}
