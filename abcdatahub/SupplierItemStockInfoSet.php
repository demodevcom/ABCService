<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemStockInfoSet
 *
 * @author Tymoteusz Sokołowski
 */
class SupplierItemStockInfoSet
{
    public $SupplierItemStockInfos;

    function __construct($Price, $StockInfoDetails, $SupplierItemIndex)
    {
        $this->SupplierItemStockInfo = new ArrayOfSupplierItemStockInfo($Price, $StockInfoDetails, $SupplierItemIndex);
    }

}
