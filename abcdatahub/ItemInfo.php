<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ItemInfo
 *
 * @author Tymoteusz Sokołowski
 */

class ItemInfo
{
    public $Barcodes; //  array of strings np. [0] => 885370315165 [1] => 885370593327
    public $ItemIndex; // C1058016
    public $Name; // np Xbox 360 250GB
    public $SupplierItemIndex; // np RKH-00050

    function __construct($obj)
    {

        $this->Barcodes = new Barcodes($obj->Barcodes); //  array of strings np. [0] => 885370315165 [1] => 885370593327
        $this->ItemIndex = $obj->ItemIndex; // C1058016
        $this->Name = $obj->Name; // np Xbox 360 250GB
        $this->SupplierItemIndex = $obj->SupplierItemIndex; // np RKH-00050

    }
}
