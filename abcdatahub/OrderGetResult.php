<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of OrderGetResult
 *
 * @author Tymoteusz Sokołowski
 */

class OrderGetResult
{
    public $AllLinesRequired;
    public $CurrencyCode; //np  => 'PLN'
    public $DeliveryDate; // np  0001-01-01T00:00:00
    public $Language;  // => PL
    public $Lines; //  ArrayOfOrderResponseLine
    public $Note; //
    public $OrderDateTime; // ] => 2012-10-22T14:58:34.27
    public $OrderNo; // np. P656663
    public $OrderStatus; // powinno przyjść ze statusem ACCEPTED
    public $OrderType; //  B2C
    public $Parties; // ArrayOfBusinessParty 
    public $References; // ArrayOfReference
    public $SupplierOrderNo; // zaskoczka: to też ma być string :)

    function __construct($obj)
    {

        if (is_object($obj)) {
            $this->AllLinesRequired = $obj->AllLinesRequired;
            $this->CurrencyCode = $obj->CurrencyCode; //np  => 'PLN'
            $this->DeliveryDate = $obj->DeliveryDate; // np  0001-01-01T00:00:00
            $this->Language = $obj->Language;  // => PL
            $this->Lines = new  ArrayOfOrderLine($obj->Lines); //  ArrayOfOrderResponseLine
            $this->Note = $obj->Note; //
            $this->OrderDateTime = $obj->OrderDateTime; // ] => 2012-10-22T14:58:34.27
            $this->OrderNo = $obj->OrderNo; // np. P656663
            $this->OrderStatus = $obj->OrderStatus; // powinno przyjść ze statusem ACCEPTED
            $this->OrderType = $obj->OrderType; //  B2C
            $this->Parties = $obj->Parties; // ArrayOfBusinessParty
            $this->References = new ArrayOfReference($obj->References); // ArrayOfReference
            $this->SupplierOrderNo = $obj->SupplierOrderNo; // zaskoczka: to też ma być string :)

        }
    }
}

