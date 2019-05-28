<?php

namespace MortgageCalculator;

class Dti
{
    const PERCENT_PRECISION = 3;

    /**
     * Array with all borrower data for a loan
     *
     * @var array $borrowers
     */
    protected $borrowers;

    /**
     * Array with all coborrower data for a loan
     *
     * @var array $coborrowers
     */
    protected $coborrowers;

    /**
     * Keys for items to include when calculating the total monthly payment for the loan.
     *
     * @var array $liabilityItems
     */
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

    /**
     * Keys for items to include when calculating each borrowers total monthly income. Values are
     * the name of the key used to determine whether or not the item is to be used in the ratio.
     *
     * @var array $incomeItems
     */
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

    /**
     * DTI Front represents the Debt To Income ration for a specific
     * loan and does not consider debt related to other loans.
     *
     * @return string
     */
    public function getDtiFront()
    {
        $mortgageLiability = $this->getMortgageLiability();
        $totalIncome       = $this->getTotalIncome();

        return self::convertToPercent($mortgageLiability / $totalIncome);
    }

    /**
     * DTI Back represents the total Debt To Income ration for a specific
     * loan including debt related to other loans.
     *
     * @return string
     */
    public function getDtiBack()
    {
        $mortgageLiability = $this->getMortgageLiability();
        $otherLiability    = $this->getOtherLiability();
        $totalIncome       = $this->getTotalIncome();

        return self::convertToPercent(($mortgageLiability + $otherLiability) / $totalIncome);
    }

    /**
     * The total monthly payment for the mortgage being considered.  This includes bills such
     * as HOA dues, interest, tax insurance and utilities on the property.
     *
     * @return float
     */
    protected function getMortgageLiability()
    {
        $mortgageLiability = 0;

        foreach ($this->liabilityItems as $item) {
            $mortgageLiability += $this->borrowers['1']['hse_exp_details']['1']['proposed'][$item];
        }

        return $mortgageLiability;
    }

    /**
     * The sum of monthly liabilities for all borrowers on the application.
     *
     * @return float
     */
    protected function getOtherLiability()
    {
        return $this->getBorrowersLiabilities($this->borrowers) + $this->getBorrowersLiabilities($this->coborrowers);
    }

    /**
     * The sum of borrowers monthly payments for all items not related to the proposed mortgage.
     * This includes debts such as alimony, child care, child support, collections and payments on unrelated loans.
     *
     * @param array $borrowers
     * @return float
     */
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

    /**
     * The sum of monthly income for all borrowers on the application.
     *
     * @return float
     */
    protected function getTotalIncome()
    {
        return $this->getBorrowersIncome($this->borrowers) + $this->getBorrowersIncome($this->coborrowers);
    }

    /**
     * The sum of borrowers monthly income.  This includes income such as alimony, child care,
     * bonuses, commissions and SSI/disability.  This does NOT include rental incomes.
     *
     * @param array $borrowers
     * @return float
     */
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

    /**
     * Convert float (ratio) to percentage string.
     *
     * @param float $value
     * @return float
     */
    protected static function convertToPercent($value)
    {
        return number_format(round($value * 100, self::PERCENT_PRECISION), self::PERCENT_PRECISION) . '%';
    }
}
