<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Mns\Service\AbcdataHub;

/**
 * Description of SupplierItem
 *
 * @author Tymoteusz Sokołowski
 */
class SupplierItem
{
    public $Brand;
    public $Category;
    public $Description;

    public function __construct($brand, $category, $description)
    {
        $this->Brand = $brand;
        $this->Category = $category;
        $this->Description = $description;
    }
}

