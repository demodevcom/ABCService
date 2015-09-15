<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemStockInfo
 *
 * @author Tymoteusz Sokołowski
 */
class SupplierItemStockInfo
{
    public $Price; //decimal
    public $StockInfoDetails; //ArrayOfSupplierItemStockInfo.ItemStockInfoDetail
    public $SupplierItemIndex;

    function __construct($Price, $StockInfoDetails, $SupplierItemIndex)
    {
        $this->Price = $Price;  //decimal
        $this->StockInfoDetails = array($StockInfoDetails); //ArrayOfSupplierItemStockInfo.ItemStockInfoDetail
        $this->SupplierItemIndex = $SupplierItemIndex;
    }

    function addDetails($StockInfoDetails)
    {
        $this->StockInfoDetails[] = $StockInfoDetails;
    }
}
