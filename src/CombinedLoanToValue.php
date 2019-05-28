<?php

namespace MortgageCalculator;

class CombinedLoanToValue extends LoanToValue
{
    /**
     * Determine the total loan amount to be used for the calculation. For cltv, the loan amount is
     * totalLoanAmount plus subFinanceAmount.  totalLoanAmount includes financed closing costs.
     *
     * @return float
     */
    protected function getLoanAmount()
    {
        return $this->totalLoanAmount + $this->subFinanceAmount;
    }

    /**
     * Determine the property value to be used for the calculation. For cltv, if subFinanceAmount is not
     * empty, the value falls back from most accurate to least accurate depending on availability.
     *
     * @return float
     */
    protected function getValue()
    {
        if (empty($this->subFinanceAmount)) {
            return parent::getValue();
        }

        return self::firstNonEmpty([$this->purchasePrice, $this->estimatedValue, $this->originalLoanAmount]);

    }

    /**
     * Determine the first non-empty value passed in an array.
     *
     * @param $array
     * @return float|null
     */
    private static function firstNonEmpty($array)
    {
        foreach ($array as $value) {
            if (!empty($value)) {
                return $value;
            }
        }

        return null;
    }
}
