<?php

namespace MortgageCalculator;

class Amortization
{
    const PERIODS_PER_YEAR = 12;

    const CURRENCY_PRECISION = 2;

    protected $principal;

    protected $periods;

    protected $interestRate;

    protected $interestOnly;

    protected $periodInterestRate;

    public $payment;

    public $schedule;

    public function __construct(
        float $principal,
        int $periods,
        float $interestRate
    )
    {
        $this->principal          = $principal;
        $this->periods            = $periods;
        $this->interestRate       = $interestRate;
        $this->periodInterestRate = $interestRate / 100 / self::PERIODS_PER_YEAR;
    }

    private function calculatePayment()
    {
        $r = $this->periodInterestRate;

        $num = $this->principal * $r;
        $den = 1 - pow(1 + $r, -1 * abs($this->periods));

        $this->payment = self::roundToCents($num / $den);
    }

    private function buildSchedule()
    {
        for ($i = 1; $i <= $this->periods; $i++) {
            $principal = $principal ?? $this->principal;

            $interest  = $principal * $this->periodInterestRate;
            $principalPayment = $this->getPayment() - $interest;

            $this->schedule[] = [
                'term'              => $i,
                'interest_payment'  => self::roundToCents($interest),
                'principal_payment' => self::roundToCents($principalPayment),
            ];

            $principal -= $principalPayment;
        }
    }

    public function getPayment()
    {
        if (empty($this->payment))
        {
            $this->calculatePayment();
        }

        return $this->payment;
    }

    public function getSchedule()
    {
        if (empty($this->schedule))
        {
            $this->buildSchedule();
        }

        return $this->schedule;
    }

    public function getTotalPrincipal()
    {
        return array_sum(array_column($this->getSchedule(), 'principal_payment'));
    }

    public function getTotalInterest()
    {
        return array_sum(array_column($this->getSchedule(), 'interest_payment'));
    }

    public function totalLoanCost()
    {
        return $this->getTotalPrincipal() + $this->getTotalInterest();
    }

    protected static function roundToCents($value)
    {
        return number_format(round($value, self::CURRENCY_PRECISION), self::CURRENCY_PRECISION, '.', '');
    }
}
