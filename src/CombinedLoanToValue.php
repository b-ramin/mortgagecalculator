<?php

namespace MortgageCalculator;

class CombinedLoanToValue extends LoanToValue
{
    protected function getLoanAmount()
    {
        if (empty($this->subFinanceAmount)) {
            return parent::getLoanAmount();
        } else {
            return $this->totalLoanAmount + $this->subFinanceAmount;
        }
    }

    protected function getValue()
    {
        if (empty($this->subFinanceAmount)) {
            $value = parent::getValue();
        } else {
            $value = self::firstNonEmpty([$this->purchasePrice, $this->estimatedValue, $this->originalLoanAmount]);
        }

        return $value;
    }

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
