<?php

namespace MortgageCalculator;

class InterestOnlyAmortization extends Amortization
{
    protected function calculatePayment()
    {
        $interestPerYear = $this->principal * $this->interestRate;

        $this->payment = self::roundToCents($interestPerYear / self::PERIODS_PER_YEAR);
    }
}
