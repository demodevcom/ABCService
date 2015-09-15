<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ArrayOfOrderResponseLine
 *
 * @author Tymoteusz Sokołowski
 */
class ArrayOfOrderResponseLine implements \IteratorAggregate
{
    public $OrderResponseLine; // OrderResponseLine

    function __construct($item, $abc_full_order, $notice)
    {
        $this->OrderResponseLine = array();
        $this->addLine($item, $abc_full_order, $notice);
    }

    public function addLine($item, $abc_full_order, $notice)
    {
        $this->OrderResponseLine[] = new OrderResponseLine($item, $abc_full_order, -1, $notice);
    }

    public function addEmptyLine($abc_full_order, $lineNo)
    {
        $this->OrderResponseLine[] = new OrderResponseLine(null, $abc_full_order, $lineNo, '');
    }

    public function getIterator()
    {
        return new  \ArrayIterator($this->OrderResponseLine);
    }
}
