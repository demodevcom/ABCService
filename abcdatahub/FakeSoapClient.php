<?php

namespace Mns\Service\AbcdataHub;

/**
 * FakeSoapClient - klasa używana do testowania tego
 * co wysyłamy do webserwisów
 *
 * @author Tymoteusz Sokołowski
 */

class FakeSoapClient
{

    function __construct($url, $options)
    {
        echo "<pre>tworzę klienta o url:" . $url . " i opcjach:" . PHP_EOL;
        print_r($options);
        echo "</pre>";
    }

    public function __call($name, $arguments)
    {
        echo "<pre>Wywołano metodę '$name' z argumentami: " . PHP_EOL;
        print_r($arguments);
        echo "</pre>";
    }

    public static function __callStatic($name, $arguments)
    {
        echo "<pre>Wywołano statycznie metodę '$name' z argumentami: " . PHP_EOL;
        print_r($arguments);
        echo "</pre>";
    }
}