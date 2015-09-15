<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ArrayOfEDIMessageInfo
 *
 * @author Tymoteusz Sokołowski
 */
class ArrayOfEDIMessageInfo implements \IteratorAggregate
{
    public $ArrayOfEDIMessageInfo;

    function __construct($objArr)
    {
        $this->ArrayOfEDIMessageInfo = array();

        $tmp = (array)$objArr->NewMessagesGetResult;
        if (!empty($tmp)) {
            $Arr = $objArr->NewMessagesGetResult->EDIMessageInfo;


            // ABCData wysyła albo pojedynczy obiekt albo tablicę obiektów:
            if (is_array($Arr)) {
                foreach ($Arr as $obj) {
                    $this->ArrayOfEDIMessageInfo[] = new EDIMessageInfo($obj);
                }
            } else {
                $this->ArrayOfEDIMessageInfo[] = new EDIMessageInfo($Arr);
            }
        }
    }

    public function getIterator()
    {
        return new  \ArrayIterator($this->ArrayOfEDIMessageInfo);
    }
}

