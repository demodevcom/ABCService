<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ArrayOfSupplierItemStockInfo
 *
 * @author Tymoteusz Sokołowski
 */
class ArrayOfSupplierItemStockInfo
{
    public $SupplierItemStockInfo; //SupplierItemStockInfo

    function __construct($Price, $StockInfoDetails, $SupplierItemIndex)
    {
        $this->SupplierItemStockInfo = array();
        $this->SupplierItemStockInfo[] = new SupplierItemStockInfo($Price, $StockInfoDetails, $SupplierItemIndex);
    }
}
