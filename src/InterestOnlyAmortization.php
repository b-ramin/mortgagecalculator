<?php

namespace MortgageCalculator;

class InterestOnlyAmortization extends Amortization
{
    /**
     * Calculate monthly payment.
     *
     * @return string
     */
    protected function calculatePayment()
    {
        $interestPerYear = $this->principal * $this->interestRate;

        return $interestPerYear / self::PERIODS_PER_YEAR;
    }
}
