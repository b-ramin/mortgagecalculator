<?php

namespace MortgageCalculator;

class Dti
{
    const PERCENT_PRECISION = 3;

    protected $appData;
    protected $borrowers;
    protected $coborrowers;

    protected $mortgageLiability;
    protected $otherLiability;
    protected $totalIncome;

    private $dtiFront;
    private $dtiBack;

    protected $incomeItems = [
        'base_income'          => 'baseInRatio',
        'overtimeInRatio'      => 'overtime',
        'bonusesInRatio'       => 'bonuses',
        'commissionsInRatio'   => 'commissions',
        'dividendsInRatio'     => 'dividends_interest',
        'interestInRatio'      => 'interest',
//        '' => 'rental_income',
//        '' => 'rent_from_reo',
        'childSupportInRatio'  => 'childSupport',
        'alimonyInRatio'       => 'alimony',
        'ssiDisabilityInRatio' => 'ssiDisability',
        'retirementInRatio'    => 'retirement',
    ];

    public function __construct($appData)
    {
        $this->appData     = $appData;
        $this->borrowers   = $appData['borrowers'];
        $this->coborrowers = $appData['coborrowers'];
    }

    public function getDtiFront()
    {
        if (!isset($this->dtiFront)){
            $mortgageLiability = $this->getMortgageLiability();
            $totalIncome       = $this->getTotalIncome();

            $this->dtiFront    = $mortgageLiability / $totalIncome;
        }

        return self::convertToPercent($this->dtiFront);
    }

    public function getDtiBack()
    {
        if (!isset($this->dtiBack)) {
            $mortgageLiability = $this->getMortgageLiability();
            $otherLiability    = $this->getOtherLiability();
            $totalIncome       = $this->getTotalIncome();

            $this->dtiBack     = ($mortgageLiability + $otherLiability) / $totalIncome;
        }

        return self::convertToPercent($this->dtiBack);
    }

    protected function getMortgageLiability()
    {
        if (!isset($this->mortgageLiability)) {
            $proposed = $this->borrowers['1']['hse_exp_details']['1']['proposed'];

            $this->mortgageLiability = array_sum([
                $proposed['first_mortgage'],
                $proposed['other_financing'],
                $proposed['hazard_insurance'],
                $proposed['real_estate_taxes'],
                $proposed['mortgage_insurance'],
                $proposed['hoa'],
                $proposed['other_expenses'],
                $proposed['utilities'],
            ]);
        }

        return $this->mortgageLiability;
    }

    protected function getOtherLiability()
    {
        if (!isset($this->otherLiability)) {
            $this->otherLiability = $this->getBorrowersLiabilities($this->borrowers) + $this->getBorrowersLiabilities($this->coborrowers);
        }

        return $this->otherLiability;
    }

    protected function getTotalIncome()
    {
        if (!isset($this->totalIncome)) {
            $this->totalIncome = $this->getBorrowersIncome($this->borrowers) + $this->getBorrowersIncome($this->coborrowers);
        }

        return $this->totalIncome;
    }

    protected function getBorrowersIncome($borrowers)
    {
        $totalIncome = 0;

        foreach ($borrowers as $borrower) {

            foreach ($borrower['borrower_employers'] as $employer) {
                if ($employer['useInRatios'] === 'YES') {
                    $totalIncome += $employer['monthlyIncome'];
                }
            }

            foreach ($borrower['income_details'] as $income) {
                foreach ($this->incomeItems as $key => $value) {
                    if ($income[$value] === 'YES') {
                        $totalIncome += $income[$key];
                    }
                }
            }

        }

        return $totalIncome;
    }

    protected function getBorrowersLiabilities($borrowers)
    {
        $totalLiabilities = 0;

        foreach ($borrowers as $borrower) {
            foreach ($borrower['liabilities_details'] as $liabilitiesDetail) {
                if ($liabilitiesDetail['in_ratio'] === '1') {
                    $totalLiabilities += $liabilitiesDetail['monthly_payment'];
                }
            }
        }

        return $totalLiabilities;
    }

    protected static function convertToPercent($value)
    {
        return number_format(round($value * 100, self::PERCENT_PRECISION), self::PERCENT_PRECISION) . '%';
    }
}
