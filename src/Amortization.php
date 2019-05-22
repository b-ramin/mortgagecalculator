<?php

namespace MortgageCalculator;

const PERIODS_PER_YEAR = 12;

class Amortization
{
    private $principal;

    private $periods;

    private $interestRate;

    private $interestOnly;

    private $periodInterestRate;

    public $payment;

    public $schedule;

    public function __construct(float $principal, int $periods, float $interestRate, $interestOnly = false)
    {
        $this->principal          = $principal;
        $this->periods            = $periods;
        $this->interestRate       = $interestRate;
        $this->interestOnly       = $interestOnly;
        $this->periodInterestRate = $interestRate / 100 / PERIODS_PER_YEAR;

        $this->calculatePayment($interestOnly);
        $this->buildSchedule();
    }

    private function calculatePayment()
    {
        if ($this->interestOnly) {
            $interestPerYear = $this->principal * $this->interestRate;
            $this->payment = self::roundToCents($interestPerYear / PERIODS_PER_YEAR);
        } else {
            $r = $this->periodInterestRate;

            $num = ($this->principal * $r);
            $den = 1 - pow((1 + $r), -1 * abs($this->periods));

            $this->payment = self::roundToCents($num / $den);
        }
    }

    private function buildSchedule()
    {
        for ($i = 1; $i <= $this->periods; $i++) {
            $principal = $principal ?? $this->principal;

            $interest  = $principal * $this->periodInterestRate;
            $principalPayment = $this->payment - $interest;

            $this->schedule[] = [
                'term'              => $i,
                'interest_payment'  => self::roundToCents($interest),
                'principal_payment' => self::roundToCents($principalPayment),
            ];

            $principal -= $principalPayment;
        }
    }

    public function getTotalPrincipal()
    {
        return array_sum(array_column($this->schedule, 'principal_payment'));
    }

    public function getTotalInterest()
    {
        return array_sum(array_column($this->schedule, 'interest_payment'));
    }

    public function totalLoanCost()
    {
        return $this->getTotalPrincipal() + $this->getTotalInterest();
    }

    private static function roundToCents($value)
    {
        return round($value, 2);
    }
}
