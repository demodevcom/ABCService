<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemsStockInfoAvaliableGetResponse
 *
 * @author Tymoteusz Sokołowski
 */
class SupplierItemsStockInfoAvaliableGetResponse
{
    public $SupplierItemsStockInfoAvaliableGetResult; // ArrayOfSupplierItemStockInfo SupplierItemsStockInfoAvaliableGetResult;

    function __construct($Response)
    {
        $this->SupplierItemsStockInfoAvaliableGetResult = new SupplierItemsStockInfoAvaliableGetResult($Response->SupplierItemStockInfo);
    }
}
