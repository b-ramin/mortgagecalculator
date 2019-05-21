<?php

namespace MortgageCalculator;

class Amortization
{
    private $principal;

    private $periods;

    private $periodInterestRate;

    public $payment;

    public $schedule;

    public function __construct(float $principal, int $periods, float $interestRate, int $periodsPerYear = 12)
    {
        $this->principal          = $principal;
        $this->periods            = $periods;
        $this->periodInterestRate = $interestRate / $periodsPerYear;

        $this->calculateTermPayment();
        $this->buildSchedule();
    }

    public function calculateTermPayment()
    {
        $discountFactor = $this->discountFactor($this->periodInterestRate, $this->periods);

        $this->payment = $this->principal / $discountFactor;
    }

    public function buildSchedule()
    {
        for ($i = 1; $i <= $this->periods; $i++) {
            $principal = $principal ?? $this->principal;

            $interest  = $principal * $this->periodInterestRate;
            $principal -= $interest;

            $this->schedule[] = [
                'term'              => $i,
                'interest_payment'  => $interest,
                'principal_payment' => $this->payment - $interest,
            ];
        }
    }

    private function discountFactor($i, $n)
    {
        $numerator   = pow(1 + $i, $n) - 1;
        $denominator = $i * pow(1 + $i, $n);

        return $numerator / $denominator;
    }
}
