<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of Price
 *
 * @author Tymoteusz Sokołowski
 */
class Price
{
    public $PriceSubjectType; //[PriceSubjectType] => UNIT
    public $PriceTaxType; //[PriceTaxType] => NET
    public $PriceValue; //[PriceValue] => 679.0000

    function __construct($obj)
    {
        if (!empty($obj)) {

            $this->PriceSubjectType = $obj->PriceSubjectType;
            $this->PriceTaxType = $obj->PriceTaxType;
            $this->PriceValue = $obj->PriceValue;
        }
    }
}
