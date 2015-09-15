<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of CallResult
 *
 * @author Tymoteusz Sokołowski
 */

class CallResult
{
    public $ErrorCode;
    public $Message;
    public $ResultType; //CallResult.CallResultEnum

    function __construct($obj)
    {
        $this->ErrorCode = $obj->ErrorCode;
        $this->Message = $obj->Message;;
        $this->ResultType = $obj->ResultType;
    }
}
