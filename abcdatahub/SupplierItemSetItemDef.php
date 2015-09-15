<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItemSetItemDef
 *
 * @author Tymoteusz Sokołowski
 */

class SupplierItemSetItemDef extends ItemInfo
{
    public $Brand;
    public $Category;
    public $Description;

    public function __construct($Barcodes, $ItemIndex, $Name, $SupplierItemIndex, $Brand, $Category, $Description)
    {
        $this->Barcodes = new Barcodes($Barcodes);

        $this->ItemIndex = $ItemIndex;
        $this->Name = $Name;
        $this->SupplierItemIndex = $SupplierItemIndex;
        $this->Brand = $Brand;
        $this->Category = $Category;
        $this->Description = $Description;
    }
}
