<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of OrderResponseLine
 *
 * @author Tymoteusz Sokołowski
 */
class OrderResponseLine
{
    public $AcceptedQty; // int
    public $DeliveryDate; // dateTime
    public $DeliveryDateType; // OrderResponseLine.DeliveryDateTypeEnum
    public $Item; // ItemInfo
    public $LineNo; // int
    public $Notice; // string
    public $OrderedQty; // int
    public $Price; // PriceInfo

    /**
     * Konstruktor
     *
     * @param array $item
     * @param object $abc_full_order
     * @param int $line_no
     * @param string $notice
     *
     * @assert (null, null, 1, '' ) == false
     */
    function __construct($item = null, $abc_full_order = null, $lineNo = -1, $notice = '')
    {
        if (is_object($abc_full_order)) {
            if (is_array($item)) {

                $product_id = $item['product_id'];
                $arr = $abc_full_order->Lines->ArrayOfOrderLine;

                $lineNoInternal = $item['lineNoInternal'];
                $lineNo = $item['lineNo'];

                $response = $arr[$lineNoInternal];
                $response->Item->SupplierItemIndex = $product_id;
                $this->Item = $response->Item;

                $this->OrderedQty = $response->Qty;
                if ((empty($notice)) || ($notice == 'ok')) {
                    $this->Notice = $response->Note;
                    $this->AcceptedQty = $response->Qty;
                } else {
                    $this->Notice = $notice;
                    $this->AcceptedQty = 0; // ta linia zamówienia jest błędna - nic nie sprzedajemy
                }

                $this->Price = $response->Price;
                $datetime = new \DateTime();
                $data = $datetime->format('c');
                $this->DeliveryDate = $data;

                $this->DeliveryDateType = 'CONFIRMED';
                $this->LineNo = $lineNo;
            } else {

                #pusta linia - odrzucona

                $arr = $abc_full_order->Lines->ArrayOfOrderLine;

                $arr = $this->keys4Lines($arr);

                $response = $arr[$lineNo];

                $response->Item->SupplierItemIndex = '';
                $this->Item = $response->Item;

                $this->OrderedQty = $response->Qty;

                $this->Notice = $response->Note;
                $this->AcceptedQty = 0;

                $this->Price = $response->Price;
                $datetime = new \DateTime();
                $data = $datetime->format('c');
                $this->DeliveryDate = $data;

                $this->DeliveryDateType = 'CONFIRMED';
                $this->LineNo = $lineNo;

            }
        }
    }

    private function keys4Lines($abc_full_order)
    {
        $result_array = array();
        array_walk($abc_full_order,
            function ($a) use (&$result_array) {
                $result_array[$a->LineNo] = $a;
            },
            $result_array);
        return $result_array;
    }
}
