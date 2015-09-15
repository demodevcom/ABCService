<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemSet
 *
 * @author Tymoteusz Sokołowski
 */
class SupplierItemSet
{
    public $ItemDef; //SupplierItem

    public function __construct($brand, $category, $description)
    {
        $this->ItemDef = new SupplierItem($brand, $category, $description);
    }
}
