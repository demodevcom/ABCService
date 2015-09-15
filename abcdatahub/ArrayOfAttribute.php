<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ArrayOfAttribute
 *
 * @author Tymoteusz Sokołowski
 */
class ArrayOfAttribute
{
    public $Attribute;

    function __construct($ObjArr)
    {
        if (!empty($ObjArr)) {
            $this->Attribute = array();
            $Arr = $ObjArr;
            foreach ($Arr as $obj) {
                $this->Attribute[] = new Attribute($obj);
            }
        }
    }
}
