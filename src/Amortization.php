<?php

namespace MortgageCalculator;

class Amortization
{
    const PERIODS_PER_YEAR = 12;

    const CURRENCY_PRECISION = 2;

    /**
     * Total loan amount
     *
     * @var float $principal
     */
    protected $principal;

    /**
     * Number of months to pay off loan
     *
     * @var int $periods
     */
    protected $periods;

    /**
     * Annual interest
     *
     * @var float $interestRate
     */
    protected $interestRate;

    /**
     * Monthly interest rate
     *
     * @var float|int $periodInterestRate
     */
    protected $periodInterestRate;

    /**
     * Amortization schedule
     *
     * @var $schedule
     */
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

    /**
     * Calculate and return monthly payment as a dollar string.
     *
     * @return string
     */
    public function getPayment()
    {
        $r = $this->periodInterestRate;

        $num = $this->principal * $r;
        $den = 1 - pow(1 + $r, -1 * abs($this->periods));

        return self::roundToCents($num / $den);
    }

    /**
     * Build out amortization schedule.
     *
     * @return void
     */
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

    /**
     * Return an array detailing the amount of money that when toward interest vs principal each month.
     *
     * @return array
     */
    public function getSchedule()
    {
        if (empty($this->schedule))
        {
            $this->buildSchedule();
        }

        return $this->schedule;
    }

    /**
     * Use the amortization schedule to return the total principal paid over the life of the loan.
     *
     * @return float|int
     */
    public function getTotalPrincipal()
    {
        return array_sum(array_column($this->getSchedule(), 'principal_payment'));
    }

    /**
     * Use the amortization schedule to return the total interest paid over the life of the loan.
     *
     * @return float|int
     */
    public function getTotalInterest()
    {
        return array_sum(array_column($this->getSchedule(), 'interest_payment'));
    }

    /**
     * Use the amortization schedule to return the total cost of the loan.
     *
     * @return float|int
     */
    public function totalLoanCost()
    {
        return $this->getTotalPrincipal() + $this->getTotalInterest();
    }

    /**
     * Round off dollar values to two decimal places (cents) and add dollar sign to create money string.
     *
     * @param float $value
     * @return float
     */
    protected static function roundToCents($value)
    {
        return number_format(round($value, self::CURRENCY_PRECISION), self::CURRENCY_PRECISION, '.', '');
    }
}
