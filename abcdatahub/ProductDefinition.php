<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of ProductDefinition
 *
 * @author Tymoteusz Sokołowski
 * Definicja produktu
 */
class   ProductDefinition
{


    private $_client;

    function __construct($client)
    {
        $this->_client = $client;
    }

    function saveDefinition(SupplierItemSetItemDef $sItem)
    {
        $Brand = $sItem->Brand;
        $Category = $sItem->Category;
        $Description = $sItem->Description;

        $odp = $this->_client->SupplierItemSet(array(
            'ItemDef' => $sItem,
            'Brand' => $Brand,
            'Category' => $Category,
            'Description' => $Description
        ));

        return new SupplierItemSetResult($odp);
    }

}


