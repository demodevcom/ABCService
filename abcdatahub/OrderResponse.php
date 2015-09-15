<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of OrderResponse
 *
 * @author Tymoteusz Sokołowski
 */
class OrderResponse
{
    public $Comment; // string
    public $CurrencyCode;  // string
    public $DeliveryDate; // dateTime
    public $Lines; // ArrayOfOrderResponseLine
    public $OrderNo; // string
    public $ResponseDateTime; // dateTime
    public $ResponseType; // OrderResponse.OrderResponseTypeEnum
    public $SupplierOrderNo; // string

}
