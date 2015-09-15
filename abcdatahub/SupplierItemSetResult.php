<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemSetResult
 *
 * @author Tymoteusz Sokołowski
 */

class SupplierItemSetResult
{
    public $CallResult; //CallResult
    public $Item; // ItemInfo
    public $ResultType; // SupplierItemSetResult.SupplierItemSetResultType

    function __construct($obj)
    {
        if (isset($obj->SupplierItemSetResult)) {
            $obj = $obj->SupplierItemSetResult;
        }

        $this->CallResult = new CallResult($obj->CallResult);
        $this->Item = new ItemInfo($obj->Item);
        $this->ResultType = $obj->ResultType;
    }
}
