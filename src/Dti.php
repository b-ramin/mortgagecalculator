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
        'base_income'        => 'baseInRatio',
        'overtime'           => 'overtimeInRatio',
        'bonuses'            => 'bonusesInRatio',
        'commissions'        => 'commissionsInRatio',
        'dividends_interest' => 'dividendsInRatio',
        'interest'           => 'interestInRatio',
        'childSupport'       => 'childSupportInRatio',
        'alimony'            => 'alimonyInRatio',
        'ssiDisability'      => 'ssiDisabilityInRatio',
        'retirement'         => 'retirementInRatio',
    ];

    protected $liabilityItems = [
        'first_mortgage',
        'other_financing',
        'hazard_insurance',
        'real_estate_taxes',
        'mortgage_insurance',
        'hoa',
        'other_expenses',
        'utilities',
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

            $this->dtiFront = $mortgageLiability / $totalIncome;
        }

        return self::convertToPercent($this->dtiFront);
    }

    public function getDtiBack()
    {
        if (!isset($this->dtiBack)) {
            $mortgageLiability = $this->getMortgageLiability();
            $otherLiability    = $this->getOtherLiability();
            $totalIncome       = $this->getTotalIncome();

            $this->dtiBack = ($mortgageLiability + $otherLiability) / $totalIncome;
        }

        return self::convertToPercent($this->dtiBack);
    }

    protected function getMortgageLiability()
    {
        if (!isset($this->mortgageLiability)) {
            foreach ($this->liabilityItems as $item) {
                $this->mortgageLiability += $this->borrowers['1']['hse_exp_details']['1']['proposed'][$item];
            }
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
                if ($liabilitiesDetail['in_ratio'] === '1' && $liabilitiesDetail['liability_type'] != 99) {
                    $totalLiabilities += $liabilitiesDetail['monthly_payment'];
                }
            }

            foreach ($borrower['reo_data'] as $liabilitiesDetail) {
                if ($liabilitiesDetail['useInRatio'] === 'YES' && $liabilitiesDetail['isSubjectProperty'] === 'NO') {
                    $totalLiabilities += abs($liabilitiesDetail['monthlyNetRentalIncome']);
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
