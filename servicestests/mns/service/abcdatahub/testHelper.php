<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of testHelper
 *
 * @author Tymoteusz Sokołowski
 */
class testHelper
{
    //put your code here


    public static function helperTestItem()
    {
        $hub = new \Mns\Service\AbcdataHub();

        $item = $hub->getProductsByEans(5901425361002, false);

        if (!empty($item)) {
            $item['Qty'] = 1; // przykładowa ilość 1
            $item['ABCDataPrice'] = 0.01; // przykładowa cena 1 grosz
            $item['abc_message_id'] = 1; // przykładowe id message'a
        }
        return $item;
    }

    public static function helperArrayItems()
    {
        $arr = array();
        $arr[] = self::helperTestItem();
        return $arr;
    }

    public static function fakeOrderLine()
    {
        $orderLine = new \stdClass();
        $orderLine->OrderLine->LineNo = 1; // przykładowe 1;
        $orderLine->OrderLine->Note = '';
        $orderLine->OrderLine->Price = self::fakePrice();
        $orderLine->OrderLine->Qty = 1; // przykładowa ilość 1
        $orderLine->OrderLine->Item = new ItemInfo(null);

        return $orderLine;
    }

    public static function fakePrice()
    {
        $prc = new \stdClass();
        $prc->PriceValue = 0.01; // przykładowa cena 1 grosz
        return new Price($prc);
    }


    public static function heleperOrderGetResult()
    {
        $obj = new \stdClass();
        $obj->AllLinesRequired = false;
        $obj->CurrencyCode = 'PLN';
        $obj->DeliveryDate = '0001-01-01T00:00:00';
        $obj->Language = 'PL';
        $obj->Lines = self::fakeOrderLine();
        $obj->Note = '';
        $obj->OrderDateTime = '2012-10-22T14:58:34.27';
        $obj->OrderNo = 'P656663';
        $obj->OrderStatus = 'ACCEPTED';
        $obj->OrderType = 'B2C';
        $obj->Parties = null;
        $obj->References = null;
        $obj->SupplierOrderNo = '';

        $ogr = new OrderGetResult($obj);
        return $ogr;
    }

}
