<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemsStockInfoAvaliableGetResult
 *
 * @author Tymoteusz Sokołowski
 */

class SupplierItemsStockInfoAvaliableGetResult
{
    public $SupplierItemStockInfo;

    function __construct($arr)
    {
        foreach ($arr as $v) {
            $this->SupplierItemStockInfo[] = new SupplierItemStockInfo($v->Price, $v->StockInfoDetails, $v->SupplierItemIndex);
        }
    }
}
