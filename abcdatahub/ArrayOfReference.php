<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ArrayOfReference
 *
 * @author Tymoteusz Sokołowski
 */

class ArrayOfReference implements \IteratorAggregate
{
    public $ArrayOfReference;

    function __construct($ObjArr)
    {
        if (!empty($ObjArr)) {
            $this->ArrayOfReference = array();

            $Arr = $ObjArr;

            foreach ($Arr as $obj) {
                $this->ArrayOfReference[] = new Reference($obj);
            }
        }
    }

    public function getIterator()
    {
        return new  \ArrayIterator($this->ArrayOfReference);
    }
}
