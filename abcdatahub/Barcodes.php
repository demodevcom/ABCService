<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of Barcodes
 *
 * @author Tymoteusz Sokołowski
 */
class Barcodes
{
    //public $ArrayOfstring; // array of strings
    public $string; // array of strings

    function __construct($obj)
    {
        if (is_array($obj)) {
            $this->string = $obj;
        } else {
            $this->string = $obj->string;
        }

    }
}
