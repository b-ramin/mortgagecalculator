<?php

namespace MortgageCalculator;

class Dti
{
    const PERCENT_PRECISION = 3;

    private $debt;

    private $income;

    private $dti;

    public function calculate()
    {
        $this->calculateDebt();
        $this->calculateIncome();

        $this->dti = $this->debt / $this->income;

        return self::convertToPercent($this->dti);
    }

    private function calculateDebt()
    {
        $this->debt = 5;
    }

    private function calculateIncome()
    {
        $this->income = 5;
    }

    protected static function convertToPercent($value)
    {
        return number_format(round($value * 100, self::PERCENT_PRECISION), self::PERCENT_PRECISION) . '%';
    }
}
