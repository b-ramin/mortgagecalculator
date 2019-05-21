<?php

namespace MortgageCalculator;

class Dti extends Calculator
{
    private $debt;

    private $income;

    private $dti;

    public function calculate()
    {
        $this->calculateDebt();
        $this->calculateIncome();

        $this->dti = $this->debt / $this->income;

        return $this->formatPercent($this->dti);
    }

    private function formatPercent(float $number)
    {
        return round($number * 100, 3) . '%';
    }

    private function calculateDebt()
    {
        $this->debt = 5;
    }

    private function calculateIncome()
    {
        $this->income = 5;
    }
}
