<?php

namespace MortgageCalculator;

class Dti
{
    const PERCENT_PRECISION = 3;

    protected $borrowers;
    protected $coborrowers;

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

    public function __construct($appData)
    {
        $this->borrowers   = $appData['borrowers'];
        $this->coborrowers = $appData['coborrowers'];
    }

    public function getDtiFront()
    {
        $mortgageLiability = $this->getMortgageLiability();
        $totalIncome       = $this->getTotalIncome();

        return self::convertToPercent($mortgageLiability / $totalIncome);
    }

    public function getDtiBack()
    {
        $mortgageLiability = $this->getMortgageLiability();
        $otherLiability    = $this->getOtherLiability();
        $totalIncome       = $this->getTotalIncome();

        return self::convertToPercent(($mortgageLiability + $otherLiability) / $totalIncome);
    }

    protected function getMortgageLiability()
    {
        $mortgageLiability = 0;

        foreach ($this->liabilityItems as $item) {
            $mortgageLiability += $this->borrowers['1']['hse_exp_details']['1']['proposed'][$item];
        }

        return $mortgageLiability;
    }

    protected function getOtherLiability()
    {
        return $this->getBorrowersLiabilities($this->borrowers) + $this->getBorrowersLiabilities($this->coborrowers);
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

            foreach ($borrower['reo_data'] as $propertyDetail) {
                if ($propertyDetail['useInRatio'] === 'YES' && $propertyDetail['isSubjectProperty'] === 'NO') {
                    $totalLiabilities += abs($propertyDetail['monthlyNetRentalIncome']);
                }
            }
        }

        return $totalLiabilities;
    }

    protected function getTotalIncome()
    {
        return $this->getBorrowersIncome($this->borrowers) + $this->getBorrowersIncome($this->coborrowers);
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
                    if (isset($income[$value]) && $income[$value] === 'YES') {
                        $totalIncome += $income[$key];
                    }
                }
            }
        }

        return $totalIncome;
    }

    protected static function convertToPercent($value)
    {
        return number_format(round($value * 100, self::PERCENT_PRECISION), self::PERCENT_PRECISION) . '%';
    }
}
