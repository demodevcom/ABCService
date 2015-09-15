<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of Reference
 *
 * @author Tymoteusz Sokołowski
 */

class Reference
{
    public $ReferenceType;
    public $ReferenceValue;

    function __construct($obj)
    {
        if (!empty($obj)) {
            $this->ReferenceType = $obj->ReferenceType;
            $this->ReferenceValue = $obj->ReferenceType;
        }
    }
}

