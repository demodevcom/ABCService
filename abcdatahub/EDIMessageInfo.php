<?php

namespace Mns\Service\AbcdataHub;
    /*
     * To change this license header, choose License Headers in Project Properties.
     * To change this template file, choose Tools | Templates
     * and open the template in the editor.
     */

/**
 * Description of EDIMessageInfo
 *
 * @author Tymoteusz Sokołowski
 */

class EDIMessageInfo
{
    public $ContentIdentifier;
    public $CreateDateTime;
    public $MessageId;
    public $MessageType;

    function __construct($obj)
    {
        $this->ContentIdentifier = $obj->ContentIdentifier;
        $this->CreateDateTime = $obj->CreateDateTime;
        $this->MessageId = $obj->MessageId;
        $this->MessageType = $obj->MessageType;
    }
}
