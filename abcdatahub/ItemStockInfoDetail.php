<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ItemStockInfoDetail
 *
 * @author Tymoteusz Sokołowski
 */
class ItemStockInfoDetail
{
    public $Qty;
    public $StockId;

    function __construct($Qty, $StockId = 'H1MORELE') // na testach: $StockId='TESTH0MOR0'
    {
        $this->StockId = $StockId;
        $this->Qty = $Qty;
    }
}

