<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ArrayOfOrderLine
 *
 * @author Tymoteusz Sokołowski
 */
class ArrayOfOrderLine implements \IteratorAggregate
{
    public $ArrayOfOrderLine;

    function __construct($ObjArr)
    {
        $this->ArrayOfOrderLine = array();
        $Arr = $ObjArr->OrderLine;


        // ABCData przesyła albo pojedynczy obiekt albo tablicę obiektów
        if (isset($Arr->Item)) {
            // przesłano pojedynczy obiekt w miejsce tablicy
            $this->ArrayOfOrderLine[] = new OrderLine($Arr);
        } else {
            // przesłano tablicę
            foreach ($Arr as $obj) {
                if (isset($obj->Item)) {
                    // jeśli są itemy
                    $this->ArrayOfOrderLine[] = new OrderLine($obj);
                }
            }
        }
    }

    public function getIterator()
    {
        return new  \ArrayIterator($this->ArrayOfOrderLine);
    }
}
