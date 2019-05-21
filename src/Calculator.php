<?php

namespace MortgageCalculator;

abstract class Calculator
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    abstract function calculate();
}
