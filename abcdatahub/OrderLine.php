<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of OrderLine
 *
 * @author Tymoteusz Sokołowski
 */
class OrderLine
{
    public $Attributes;
    public $Item;
    public $LineNo; // int
    public $Note;
    public $Price;
    public $Qty; // quantity
    public $WholeQtyRequired; // boolean

    function __construct($obj)
    {
        if (isset($obj->Attributes)) $this->Attributes = new ArrayOfAttribute ($obj->Attributes);
        $this->Item = new ItemInfo($obj->Item);
        $this->LineNo = $obj->LineNo; // int
        $this->Note = $obj->Note;
        $this->Price = new Price($obj->Price);
        $this->Qty = $obj->Qty; // quantity

        //if (INBROWSER===true) echo('<h1>Sztuk '.print_r($this->Qty,true).'</h1>'.NLB);
        $this->WholeQtyRequired = $obj->WholeQtyRequired; // boolean
    }
}
