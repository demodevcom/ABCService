<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of Attribute
 *
 * @author Tymoteusz Sokołowski
 */
class Attribute
{
    public $AttribType;
    public $AttribValue;

    function __construct($obj)
    {
        if (!empty($obj)) {
            $this->AttribType = $obj->AttribType;
            $this->AttribValue = $obj->AttribValue;
        }
    }
}
