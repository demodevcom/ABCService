<?php
/*
 * Created on 09-04-2014
 *
 * @author Tymek
 */

namespace Mns\Service;


class AbcdataHub extends \Mns\ServiceBase
{

    private $_border_time = '17:10'; // przed tą godziną delivery jest ustawiane na dzień dzisiejszy, po niej na jutrzejszy
    private $_order_properties = array(
        'order_payed' => 0, // Zamówienie niezapłacone
        'order_state' => HOME_ORDER_STATE, // Status zamówienia na Home
        'op_type' => 1, // ustaw op_type na 1
        'op_deadlineChanged' => 0, // Ustaw zmianę Deadlina na 0
        'op_source' => 3, // ticket: #10587 op_source dla zamówień z ABCDATA_HUB
    );
    private $_user_id = 581505; // na sztywno user 
    private $_deliveryinfo = array(
        'warehouse_id' => 20,
        'ordertype_id' => 13,
        'orderpayment_id' => 3
    ); // delivery info stworzone przez dział logistyki
    private $_clientMessages = null; // klient soap dla webserwisu ABCData Messages
    private $_clientOrders = null; // klient soap dla webserwisu ABCData Orders
    private $_clientSupplier = null; // klient soap dla webserwisu ABCData Supplier
    private $_options; // opcje dla połączeń z serwisami ABCDaty
    private $_messages;
    private $_orders; // zamówienia z ABCData
    private $_preparedOrderResponse; // przygotowane odpowiedzi na zamówienia z ABCDaty
    private $_orders_ids = []; // zmienna do przechowywania id zamówień utworzonych w naszym panelu
    //  na podstawie przychodzących z ABCData, które spełniają założone kryteria
    private $_orders_map = []; // zmienna do mapowania id zamówień w panelu i iz zamówień z ABCData
    private $_orders_map_message = []; // zamówienia z ABCData
    private $_products = []; // zmienna do przechowywania produktów
    private $_service_addresses = [];
    private $_service_type;

    private $_conn;

    public function __construct() // ($login,$password) z bazy
    {
        parent::__construct();

        $this->_conn = \Propel::getConnection(); // po ostatnich zmianach propela tu praktycznie nie używamy, z przyczyn wydajnościowych głównie PDO

        $this->_conn->setAttribute(\PDO::ATTR_PERSISTENT, 1);
        $this->_conn->setAttribute(\PDO::ATTR_TIMEOUT, 900);

        if ('DEV' !== ENVIRONMENT) {
            $service_name = 'hubAbcData';
        } else {
            $service_name = 'hubAbcDataTest';
            echo("ustawiam $service_name jako nazwe serwisu!");
        }

        $query = "SELECT * FROM service WHERE service_name = '$service_name'";

        $stmt = $this->_conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($res)) {
            die('Blad bazy danych!!!!!!!!!!!!!');
        }
        $r = $res[0];
        $password = $r['service_password'];
        $login = $r['service_login'];

        $this->_service_type = $r['service_type'];
        $this->_service_addresses = explode(';', $r['service_address']);

        $this->_options = array(
            'login' => $login,
            'password' => $password,
            'soap_version' => SOAP_1_1,
            'exceptions' => false,
            'cache_wsdl' => WSDL_CACHE_NONE,
        );
        $this->_order_properties['order_deadline'] = date('Y-m-d H:i:s'); // Ustaw datę Deadline na aktualną
        $this->_order_properties['op_type'] = 1;

        $this->_preparedOrderResponse = [];
        $this->_orders_map = [];
    }

    public function __destruct()
    {
        \Propel::close();
    }

    public function SupplierItemDisable($disabled_id)
    {
        $client = $this->getClientSupplier();
        $par = array('SupplierItemIndex' => $disabled_id);
        $client->__soapCall('SupplierItemDisable', array('parameters' => $par));
        return $this;
    }

    /**
     * Pobiera produkty, które są już w bazie ABCDaty
     */
    public function getProductsSetsInABCDataBase()
    {
        $client = $this->getClientSupplier();
        $odp = $client->SupplierItemsStockInfoAvaliableGet();

        return $this->keys4ProductsFromABCDataBase($odp->SupplierItemsStockInfoAvaliableGetResult->SupplierItemStockInfo);
    }

    /**
     * Przypisuje kolejne rekordy do odpowiednich kluczy
     * @param $ProductsSetsArray
     * @return array
     */
    private function keys4ProductsFromABCDataBase($ProductsSetsArray)
    {
        $result_array = [];
        array_walk($ProductsSetsArray,
            function ($a) use (&$result_array) {
                $result_array[$a->SupplierItemIndex] = $a;
            },
            $result_array);
        return $result_array;
    }

    /*
     *  uproszczony debug dla celów testowych
     */

    /**
     * pokazuje info o naszych produktach w bazie ABCDaty
     * @return $this
     */
    public function productInfoDebug()
    {
        $produkty_w_bazie_ABCDaty = $this->getProductsSetsInABCDataBase();
        $ile = count($produkty_w_bazie_ABCDaty);
        echo("W bazie ABCDaty znajduje się $ile produktów ");
        echo("<table><tr><th>Id produktu</th><th>Cena produktu</th><th>Ilość produktu</th></tr>");
        $par = true;

        foreach ($produkty_w_bazie_ABCDaty as $produkt) {
            $cena = $produkt->Price;
            $ilosc = $produkt->StockInfoDetails->{"SupplierItemStockInfo.ItemStockInfoDetail"}->Qty;
            $produkt_id = $produkt->SupplierItemIndex;
            $par = !$par;
            echo("<tr class='par$par'><td>$produkt_id</td><td>$cena</td><td>$ilosc</td></tr>");
        }
        echo('</table>');
        return $this;
    }

    /**
     * debug klientów soap wszystkich trzech webserwisów
     * @return $this
     */
    public function soapsDebug()
    {
        $this->soapDebug($this->getClientMessages());
        $this->soapDebug($this->getClientOrders());
        $this->soapDebug($this->getClientSupplier());
        return $this;
    }

    public function soapDebug($soap_client)
    {
        if (INBROWSER === true) {
            echo('<h2>Soap client dump</h2>');
            echo "REQUEST:" . NLB . print_r($soap_client->__getLastRequest(), true) . NLB;
            echo "TYPY:" . NLB;
            var_dump($soap_client->__getTypes());
            echo "FUNKCJE:" . NLB;
            var_dump($soap_client->__getFunctions());
        }

        return $this;
    }

    // zamiast standardowego mnsowego debugera - uproszczony
    public function bug($object)
    {
        if (INBROWSER === true) {
            echo(NLB . NLB . 'debug:');
            echo(NLB . NLB . '<pre>');
            print_r($object);
            echo('</pre>');
            echo(json_encode($object));
        }
    }


    /**
     * Wysyła informacje o naszych produktach
     *
     * uwaga: wcześniej sprawdzało też czy informacje zostały poprawnie wpisane do ich bazy
     * usunięte z przyczyn wydajnościowych (przynajmniej do czasu, aż zoptymalizują po swojej stronie)
     *
     * @return $this
     */
    function sendInfoAboutProducts()
    {
        // 13777
        // najpierw ich na samym początku odpytamy co mają, ta operacja trwa tylko ~20 sekund
        $produkty_w_bazie_ABCDaty = $this->getProductsSetsInABCDataBase();

        // 13777 to trwa tylko ~1s
        $res = $this->getRawData4ABCData();

        $do_zdefiniowania = [];
        $do_zerowania = [];
        $przygotuj_do_wyslania = [];

        // pierwszy przebieg - sprawdzamy po kolei każdy z produktów od nas w odpowiedzi ABCDaty
        foreach ($res as $pro) {
            // jeśli jakimś cudem zdarzy się produkt bez id (nie powinien)
            if ($pro['product_id'] == '') {
                continue;
            }

            $pro_abc = $produkty_w_bazie_ABCDaty[$pro['product_id']];

            if (!$pro_abc) {
                // nie ma produktu w abcdacie, trzeba go zdefiniować, tu tylko zasygnalizowane, dodawane później
                $do_zdefiniowania[] = $pro['product_id'];
            }

            // jeśli w bazie abcdaty cena lub ilość nie zgadza się z naszą
            // (1 grosz dodany ze względu na błędy zaookrągleń)
            // lub jeśli produkt nie jest ustawiony jako dostępny:
            if ((floor($pro['price_brutto'] * 100) != floor($pro_abc->Price * 123 + 1))
                ||
                ($pro['alias_quantity'] != $pro_abc->StockInfoDetails->{'SupplierItemStockInfo.ItemStockInfoDetail'}->Qty)
                ||
                ($pro_abc->Enabled != 1)
            ) {
                $przygotuj_do_wyslania[] = $pro['product_id']; // z tego potem będzie tworzony jeden array wysyłanych
            }
        }

        // drugi przebieg - robimy na odwrót jak w powyższym foreachu sprawdzamy każdy z produktów z bazy ABCDaty u nas:
        foreach ($produkty_w_bazie_ABCDaty as $pro_abc) {
            if ($pro_abc->Enabled) {
                // jeśli produkt jest dostępny w bazie abcDaty a nie ma go u nas:
                $pro = $res[$pro_abc->SupplierItemIndex];
                if (!$pro) {
                    $do_zerowania[] = $pro_abc->SupplierItemIndex;
                }
            }
        }

        //  trzeci przebieg - budujemy tablicę produktów do wysłania (w tym miejscu się da wysłać w jednym połączeniu)
        foreach ($przygotuj_do_wyslania as $produkt_id) {
            $pro = $res[$produkt_id];
            $Qty = $pro['alias_quantity']; // 13849
            $StockInfoDetails = new \Mns\Service\AbcdataHub\ItemStockInfoDetail($Qty);
            $SupplierItemIndex = $pro['product_id'];
            $dokladnosc = 10000; // dokładność do 1/100 grosza w sumie to powinno się znaleźć w configu lub envach
            $Price = floor($pro['price_brutto'] / 1.23 * $dokladnosc) / $dokladnosc;

            $p = new \Mns\Service\AbcdataHub\SupplierItemStockInfo($Price, $StockInfoDetails, $SupplierItemIndex);
            $do_wyslania[] = $p;
        }

        if ('DEV' === ENVIRONMENT) {
            file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'ABCH_przygotuj_do_wyslania.txt', print_r($przygotuj_do_wyslania, true));
            file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'ABCH_do_zerowania.txt', print_r($do_zerowania, true));
            file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'ABCH_do_wyslania.txt', print_r($do_wyslania, true));
            file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'ABCH_do_zdefiniowania.txt', print_r($do_zdefiniowania, true));
        }

        // koniec czynności przygotowawczych - czas wykonania do tej pory to jakieś ~37 sekund
        // zaczynamy gadać z webserwisem ABCDaty
        // na przyszłość przydałoby się, żeby przepisali swoje metody tak, by dało się do nich wrzucać
        // array a nie stukać z każdym produktem

        $client = $this->getClientSupplier();

        // czwarty przebieg - wrzucamy definicje:
        foreach ($do_zdefiniowania as $produkt_id) {
            $pro = $res[$produkt_id];
            // dane od nas (z naszej bazy):
            $Barcodes = explode(',', $pro['eans']);
            $ItemIndex = $pro['product_id'];
            $Name = $pro['product_name'];
            $SupplierItemIndex = $pro['product_id'];
            $Brand = $pro['brand_id'];
            $Category = $pro['category_id'];
            $Description = $pro['alias_code'];

            $sI = new \Mns\Service\AbcdataHub\SupplierItemSetItemDef($Barcodes, $ItemIndex, $Name, $SupplierItemIndex, $Brand, $Category, $Description);

            $pD = new \Mns\Service\AbcdataHub\ProductDefinition($client);
            $pD->saveDefinition($sI);
        }

        // tutaj na szczęście da się array wrzucić
        $odp = $client->SupplierItemStockInfoSet(array('SupplierItemStockInfos' => $do_wyslania));

        // piąty przebieg - zerujemy niedostępne - to trwa najdłużej
        foreach ($do_zerowania as $produkt_id) {
            $this->SupplierItemDisable($produkt_id);
        }

        // rezygnuję ze sprawdzania, czy wszystko zapisało się u nich dobrze - to jest kolejne wolne łączenie
        // z ich webserwisem i niepotrzebne zaśmiecanie naszych logów
        // całość procesu nie powinna teraz przekroczyć 7 minut

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        return $this;
    }

    /**
     *
     * @return Soap Client dla Serwisu Message
     */
    function getClientMessages()
    {
        if (is_null($this->_clientMessages)) {
            $this->_clientMessages = $this->_connectSoap($this->_service_type . $this->_service_addresses[0]);
        }
        return $this->_clientMessages;
    }

    /**
     *
     * @return Soap Client dla Serwisu Orders
     */
    function getClientOrders()
    {
        // return new \Mns\Service\AbcdataHub\FakeSoapClient($this->_service_type.$this->_service_addresses[1], $this ->_options);
        if (is_null($this->_clientOrders)) {
            $this->_clientOrders = $this->_connectSoap($this->_service_type . $this->_service_addresses[1]);
        }
        return $this->_clientOrders;
    }


    /**
     *
     * @return Soap Client dla Serwisu Supplier
     */
    function getClientSupplier()
    {
        if (is_null($this->_clientSupplier)) {
            $this->_clientSupplier = $this->_connectSoap($this->_service_type . $this->_service_addresses[2]);
        }
        return $this->_clientSupplier;
    }

    /**
     *
     * @param type $url
     * @return \SoapClient
     */
    function _connectSoap($url)
    {
        return new \SoapClient($url, $this->_options);
    }


    /**
     *
     * @return ArrayOfEDIMessageInfo
     */
    public function NewMessagesGet()
    {
        $odp = $this->getClientMessages()->__soapCall("NewMessagesGet", []);

        $this->_messages = new \Mns\Service\AbcdataHub\ArrayOfEDIMessageInfo($odp);

        if (STORAGE_LOGS_ON) file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'message_buforABCDaty.txt', json_encode($this->_messages));
        if (STORAGE_LOGS_ON) file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'message_buforABCDaty.' . date('YmdHis') . '.OrderGetResults.log.txt', serialize($this->_messages));

        return $this->_messages;
    }

    /**
     * Pobieranie zamówień
     * @return \Mns\Service\OrderGetResult
     */
    public function getOrders()
    {
        $this->NewMessagesGet();

        if (ADD_FAKE_ORDER) {
            $this->getFakeOrders();
        } else {
            $this->getRealOrders();
        }

        return $this->_orders;
    }

    protected function getFakeOrders()
    {
        // tworzenie fikcyjnego zamówienia:

        $xml = simplexml_load_string(file_get_contents(DIR_SHARED . 'abcdhub.xml'));
        $json = json_encode($xml);
        $oo = json_decode($json);


        if (INBROWSER === true) {
            echo('tworzenie fikcyjnego zamówienia:');
            echo('<pre>');
            print_r($oo);
            echo('</pre>');
        }

        $OrderGetResults[0] = new \Mns\Service\AbcdataHub\OrderGetResult($oo); // zapisanie pobranego zamĂłwienia pod numerem message'a

        // uwaga - nr message ($msg) nie ma nic wspólnego z nr zamówienia ($odp->OrderGetResult->OrderNo) == $AO->ContentIdentifier;

        $this->_orders = $OrderGetResults; // wpisanie ich do obiektu _orders

        if (INBROWSER === true) {
            echo('<pre>');
            print_r($this->_orders);
            echo('</pre>');
        }
    }

    protected function getRealOrders()
    {
        $OrderGetResults = [];
        if (empty($this->_messages->ArrayOfEDIMessageInfo)) {
            if (INBROWSER === true) echo(" Brak zamówień " . NLB . NLB);
            if (LOGUJ_ABCDATA_HUB) $this->logger->info("Brak zamówień z ABCDataHUB.");
        } else {
            if (INBROWSER === true) echo(" Pobieram zamówienia " . NLB . NLB);

            foreach ($this->_messages as $AO) {
                $par = array('OrderNo' => $AO->ContentIdentifier);
                $msg = $AO->MessageId; // id właśnie odebranego zlecenia z ABCDaty

                // Pobieranie zamówień„ oczekujących w serwisie ABCDaty
                $odp = $this->getClientOrders()->__soapCall("OrderGet", array($par));

                if (STORAGE_LOGS_ON) file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . $AO->ContentIdentifier . '.' . date('YmdHis') . '.log.txt', serialize($odp));

                if ('SoapFault' == get_class($odp)) {
                    // Błąd po stronie ABCDaty
                    if (INBROWSER === true) {
                        echo('<a name="' . $AO->ContentIdentifier . '" ></a><hr>Błąd w odpowiedzi ABCDaty dla zamówienia:  <b>' . $AO->ContentIdentifier . '</b> w msg: ' . $msg . ' ');
                        $this->logger->info('Błąd w odpowiedzi ABCDataHub dla zamówienia: ' . $AO->ContentIdentifier . ' w msg: ' . $msg);
                    }
                    continue;
                }

                $OrderGetResults[$msg] = new \Mns\Service\AbcdataHub\OrderGetResult($odp->OrderGetResult); // zapisanie pobranego zamówienia pod numerem message'a

                if (STORAGE_LOGS_ON) file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . $msg . '.' . date('YmdHis') . '.OrderGetResults.log.txt', serialize($OrderGetResults[$msg]));

                $this->_orders = $OrderGetResults; // wpisanie ich do obiektu _orders

                if (INBROWSER === true) echo(NLB . NLB . "Próbuję zamknąć zlecenie $msg ");

                // Poinformowanie ABCDaty o tym, że zamówienie zostało przetworzone
                if (ERASE_RECEIVED_ORDERS) {
                    if (INBROWSER === true) echo('Usuwam pobrane zamówienie z listy zamówień ABCDaty!');
                    $odp = $this->getClientMessages()->MessageReceivedSet(array('MessageId' => $msg));
                    if (INBROWSER === true) {
                        if (is_soap_fault($odp)) {
                            if (INBROWSER === true) echo("Nie udało się zamknąć zlecenia: $msg " . NLB . NLB);
                        } else {
                            if (INBROWSER === true) echo("Hurra! Udało się zamknąć zlecenie: $msg " . NLB);
                        }
                    }
                } else {
                    if (INBROWSER === true) echo('<p style="color:green">Test: Emuluję tylko usuwanie, ale naprawdę nie usuwam pobranych zamówień z listy zamówień ABCDaty!</p>');
                }
            }
        } // end else
    }

    /**
     * pobiera produkty z zamówień ABCDaty
     * jeśli zamówienia nie zostały przedtem pobrane, próbuje wpierw pobrać zamówienia
     * @return array
     */
    public function getProducts()
    {
        $all_odp = [];

        if ((empty($this->_orders))) // jeśli obiekt orders jest pusty, prawdopodobnie zamówienia nie zostały przedtem pobrane, próbuję wpierw pobrać zamówienia
        {
            if (INBROWSER === true) echo("Pusty obiekt zamówień, próbuję pobrać zamówienia");
            $this->logger->err("Pusty obiekt zamówień, próbuję pobrać zamówienia");
            $this->getOrders();
        }

        if ((empty($this->_orders))) // jeśli nadal orders jest pusty - widocznie nie ma nowych zamówień
        {
            if (INBROWSER === true) echo('Brak nowych zamówień');
            $this->logger->info('Brak nowych zamówień');
            $all_odp = false;
        } else {

            // przetwarzamy pobrane zamówienia
            $nr_przetwarzanego = 0;
            foreach ($this->_orders as $order_nr => $OO) {
                // numer zamówienia
                $OrderNo = $OO->OrderNo;
                ++$nr_przetwarzanego;
                if (INBROWSER === true) echo('Przetwarzanie zamówienia ' . $nr_przetwarzanego . ' o numerze ' . $OrderNo . ' zapowiedzianego w komunikacie: ' . $order_nr . NLB . NLB);
                $nr_lini_przetwarzanej = 0;

                $odpowiedzi = [];

                foreach ($OO->Lines as $OL) {
                    ++$nr_lini_przetwarzanej;
                    $lineNo = $OL->LineNo;
                    if (INBROWSER === true) echo('Przetwarzanie linii ' . $nr_lini_przetwarzanej . " ($lineNo) " . NLB . NLB);
                    $this->logger->info('Przetwarzanie linii ' . $nr_lini_przetwarzanej . " ($lineNo) " . ' zamówienia ' . $nr_przetwarzanego . ' o numerze ' . $OrderNo . ' zapowiedzianego w komunikacie: ' . $order_nr);

                    $eans = $OL->Item->Barcodes->string;
                    $odp = $this->getProductsByEans($eans);
                    $odp['lineNo'] = $lineNo;
                    $odp['lineNoInternal'] = $nr_lini_przetwarzanej - 1;

                    if (!empty($odp)) {
                        $odp['Qty'] = $OL->Qty;
                        $odp['ABCDataPrice'] = $OL->Price->PriceValue;
                        $odp['abc_message_id'] = $order_nr;
                        $odpowiedzi[] = $odp;
                    } else {
                        if (INBROWSER === true) echo('Linia ' . $lineNo . ' zamówienia ' . $OrderNo . ' zawiera zamówienie produktu, który w tej chwili nie jest przez nas oferowany. ' . PHP_EOL);
                        if (INBROWSER === true) echo('Linia ' . $nr_lini_przetwarzanej . " ($lineNo) " . ' zamówienia ' . $nr_przetwarzanego . ' o numerze ' . $OrderNo . ' zawiera zamówienie produktu, który w tej chwili nie jest przez nas oferowany ean: ' . print_r($eans, true));
                        $this->logger->err('Linia ' . $nr_lini_przetwarzanej . " ($lineNo) " . ' zamówienia ' . $nr_przetwarzanego . ' o numerze ' . $OrderNo . ' zawiera zamówienie produktu, który w tej chwili nie jest przez nas oferowany ean: ' . print_r($eans, true));
                        # $odpowiedzi[]=false; // 13849
                        $odp = [];
                        $odp['product_code'] = $OL->Item->SupplierItemIndex;
                        $odp['alias_code'] = $OL->Item->SupplierItemIndex;
                        $odp['Qty'] = $OL->Qty;
                        $odp['ABCDataPrice'] = $OL->Price->PriceValue;
                        $odp['abc_message_id'] = $order_nr;
                        $odpowiedzi[] = $odp;
                    }
                }
                $all_odp[$OrderNo] = $odpowiedzi;
            }
        }
        $this->_products = $all_odp;
        if (!empty($all_odp)) {
            return $this->createOrders();
        } else {
            $this->sendOrderResponses();
            return false;
        }
    }


    /**
     * @assert (0, 0) == false
     *
     * Zwraca dane dla produktów w zamówieniu morelowym na podstawie danych
     * z zamówienia ABCDaty i danych ustawionych przez PMów
     * @author Tymek
     * @param array $items dane itemów na podstawie zamówienia ABCDaty i morelowej bazy danych (ustawiane przez PMów)
     * @return false / array zwraca dane dla zamówienia morelowego lub false w przypadku odrzucenia danych np. jeśli cena z ABCDaty jest mniejsza od ustawionej przez PMów o więcej niĹĽ 1 grosz, braków w magazynie itp.
     */
    function prepareItems($items, $order)
    {
        if (empty($items)) {
            return;
        }

        $itemdata = [];

        $single_order = $items;
        foreach ($single_order as $k => $item) {
            $abc_message_id = $item['abc_message_id'];
            $itemPrepare = [];
            if (INBROWSER === true) echo('Sprawdzam Itemy rządane w zamówieniu' . NLB);
            $this->logger->info('Sprawdzam Itemy rządane w zamówieniu');

            if (!empty($item['product_id'])) {
                $itemPrepare['product_id'] = $item['product_id'];
                $itemPrepare['state'] = HOME_PRODUCT_STATE; //
                $itemPrepare['supplier_id'] = NADMIAR;

                $abc_price = $item['ABCDataPrice'] * 1.23; // liczymy brutto z netta ABCDaty
                $our_price = $item['price_brutto'];
                $this->logger->info('cena z abc daty:' . $item['ABCDataPrice'] . ' przeliczona cena z abc daty:' . $abc_price . ' nasza cena:' . $our_price);

                $itemPrepare['item_valueBrutto'] = $abc_price;
                if (INBROWSER === true) echo('cena:' . $itemPrepare['item_valueBrutto']);

                if ($our_price - 0.01 > $abc_price) {
                    $komm = 'za niska cena';
                    if (LOGUJ_ABCDATA_HUB) $this->logger->err("Uwaga: Nie przerywam przetwarzania, wysyłam info do ABCDaty, że Cena z ABCDaty ($abc_price) jest mniejsza od zadeklarowanej przez nas($our_price).");
                } elseif ($item['Qty'] > $item['alias_quantity']) {
                    $komm = 'brak na stanie';
                    if (LOGUJ_ABCDATA_HUB) $this->logger->err('Linia zamówienie z ABCDaty odrzucona: Nie mamy aż tyle produktu: ' . $item['product_id'] . ' w magazynie.');
                } else {
                    $komm = 'ok';
                    if (LOGUJ_ABCDATA_HUB) $this->logger->info('tworzę zamówienie z ABCDaty na produkt: ' . $item['product_id'] . ' w ilości: ' . $item['Qty']);
                }

                $itemPrepare['item_quantity'] = $item['Qty'];

                // Zadanie #10805  usunięcie opłaty logistycznej
                $itemPrepare['item_heavy'] = 0;

                // Błąd #10935 usunięcie opłaty za wniesienie
                $itemPrepare['item_lift'] = 0;

                if ($komm == 'ok') {
                    $itemdata[] = $itemPrepare;
                }
            } else {
                if (INBROWSER === true) echo(NLB . NLB . 'Błąd: nie znaleziono rządanego produktu wśród oferowanych ABCDacie!' . NLB . NLB);
                if (INBROWSER === true) var_dump($item);
                $this->logger->err('Błąd: nie znaleziono rządanego produktu wśród oferowanych ABCDacie!');
                $komm = 'produkt niedostępny';
            }
            $this->prepareOrderResponse($item, $order, $this->_orders[$abc_message_id], $komm);
        } // end foreach

        return $itemdata;
    }

    /**
     * Zwraca datę wysyłki
     * // do ustawionej godziny _border_time - dzisiejszą, po tym jutrzejszą
     * // pierwotnie   _border_time jest ustawiona na 17:10
     * @return DateTime data - data wysyłki
     */
    public function  getDeliveryDateForResponse()
    {
        // Ustaw datę na aktualną
        $datetime = new \DateTime();

        list($gH, $gM) = explode(":", $this->_border_time);

        $H = $datetime->format('H');
        $m = $datetime->format('i');
        if (($H > $gH) || (($H == $gH) && ($m > $gM))) { // jeśli jest po _border_time dodaj jeden dzień do daty:
            $datetime->modify('+1 day');
        }

        $data = $datetime->format('c');

        return $data;
    }

    public function  getSimpleDateForResponse()
    {
        $datetime = new \DateTime();
        $data = $datetime->format('c');
        return $data;
    }


    /**
     * Pobiera istniejącą odpowiedź na zamówienie lub tworzy nową pustą
     * @param $orderId
     * @return AbcdataHub\OrderResponse
     */
    public function getPreparedResponse($orderId)
    {
        if (isset($this->_preparedOrderResponse[$orderId])) {
            return $this->_preparedOrderResponse[$orderId];
        }
        return new \Mns\Service\AbcdataHub\OrderResponse();
    }


    /**
     * @assert ('ok' ) == 'ACCEPTED'
     * @assert ('Zamówienie odrzucone.' ) == 'REJECTED'
     * @assert ('cokolwiek innego' ) == 'ACCEPTED_WITH_EXCEPTIONS'
     *
     * Konwertuje $komm na responseType
     * @param $komm
     * @return string
     */
    public function generateResponseType($komm)
    {
        if ($komm == 'ok') {
            return 'ACCEPTED';
        }

        if ($komm == 'Zamówienie odrzucone.') {
            return 'REJECTED';
        }

        return 'ACCEPTED_WITH_EXCEPTIONS';
    }


    /**
     *
     * @assert ('ok','' ) == 'ACCEPTED'
     * @assert ('ok','ACCEPTED' ) == 'ACCEPTED'
     *
     * @assert ('ok','REJECTED' ) == 'REJECTED'
     * @assert ('cokolwiek innego','REJECTED' ) == 'REJECTED'
     * @assert ('Zamówienie odrzucone.','REJECTED' ) == 'REJECTED'
     * @assert ('Zamówienie odrzucone.','ACCEPTED' ) == 'REJECTED'
     * @assert ('Zamówienie odrzucone.','ACCEPTED_WITH_EXCEPTIONS' ) == 'REJECTED'
     * @assert ('Zamówienie odrzucone.','' ) == 'REJECTED'
     *
     * @assert ('cokolwiek innego','' ) == 'ACCEPTED_WITH_EXCEPTIONS'
     * @assert ('cokolwiek innego','ACCEPTED' ) == 'ACCEPTED_WITH_EXCEPTIONS'
     * @assert ('cokolwiek innego','ACCEPTED_WITH_EXCEPTIONS' ) == 'ACCEPTED_WITH_EXCEPTIONS'
     *
     * funkcja tworzy nowy responseType na podstawie $komm i starego responseType
     *
     * @param $komm
     * @param $preres
     * @return string
     */
    public function regenerateResponseType($komm, $preres)
    {
        if (empty($preres)) {
            $preres = 'ACCEPTED';
        }

        if (INBROWSER === true) echo("mój komunikat:" . $komm . NLB . NLB);
        $res = $this->generateResponseType($komm);
        if ($res == $preres) return $preres;
        if ($res == 'REJECTED' || $preres == 'REJECTED') return 'REJECTED'; // jeśli cokolwiek REJECTED
        if ($res == 'ACCEPTED_WITH_EXCEPTIONS' || $preres == 'ACCEPTED_WITH_EXCEPTIONS') {
            return 'ACCEPTED_WITH_EXCEPTIONS';
        }
        return $res;
    }


    /**
     *
     * Odpowiedź na zamówienie
     * @param type $item - dane itemu
     * @param type $orderId - id zamówienia ABCDaty
     * @param type $abc_full_order - peĹ‚ne zamówienie z ABCDaty
     * @param type $komm - komunikat do przekazania (ok = w porządku, cokolwiek innego = REJECTED)
     */
    public function prepareOrderResponse($item, $orderId, $abc_full_order, $komm)
    {
        $oR = $this->getPreparedResponse($orderId);
        $r_type = $this->regenerateResponseType($komm, $oR->ResponseType);
        if (INBROWSER === true) echo("Przygotowuję odpowiedź typu: " . $r_type . PHP_EOL . " ");

        $new_order = (empty($oR->Lines));

        if (($oR->Comment == '') || ($oR->Comment == 'ok')) {
            $oR->Comment = $komm;
        } else {
            if ($komm != 'ok') {
                $oR->Comment .= ',' . $komm;
            }
        }

        $oR->CurrencyCode = 'PLN';
        $oR->DeliveryDate = $this->getDeliveryDateForResponse();

        if ($new_order) {
            $oR->Lines = new \Mns\Service\AbcdataHub\ArrayOfOrderResponseLine($item, $abc_full_order, $komm);
        } else {
            $oR->Lines->addLine($item, $abc_full_order, $komm);
        }

        $oR->OrderNo = $orderId;
        $oR->ResponseDateTime = $this->getSimpleDateForResponse();
        $oR->ResponseType = $r_type;
        $oR->SupplierOrderNo = $komm;


        $this->_preparedOrderResponse[$orderId] = $oR;
        return $this->_preparedOrderResponse[$orderId];
    }


    /**
     * Odpowiedź na zamówienie
     */
    public function sendOrderResponses()
    {
        foreach ($this->_orders as $order_map_message => $ABCDorder) {
            $order = $ABCDorder->OrderNo; // id zamówienia z ABCDaty
            if (empty($this->_orders_map[$order])) {
                $item = null;
                $abc_order = null;
                $komm = "Zamówienie odrzucone.";
                $this->logger->err("Wysylam informacje do ABCDATY o odrzuceniu zamowienia $order.");
                $oR = $this->getPreparedResponse($order);
                $oR->Comment = "zamówienie odrzucone";
                $oR->ResponseType = "REJECTED";
                $oR->SupplierOrderNo = 0;
            } else {
                if ((INBROWSER === true)) echo("order map uzupełniam tak: this -> _preparedOrderResponse[$order]->SupplierOrderNo= this -> _orders_map[$order] ( " . $this->_orders_map[$order] . ')' . NLB . NLB);
                $this->_preparedOrderResponse[$order]->SupplierOrderNo = $this->_orders_map[$order];
                $this->_orders_map_message[$this->_orders_map[$order]] = $order_map_message;
            }
        }

        $client = $this->getClientOrders();
        $nrRes = 0;

        // zapisywanie dodatkowych logów:
        foreach ($this->_preparedOrderResponse as $order => $oR) {
            $nrRes++;
            if (STORAGE_LOGS_ON) {
                file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'respon_' . date('YmdHis') . '_' . $nrRes . '.txt', print_r($oR, true));

                if ((INBROWSER === true)) {
                    if (empty($oR)) {
                        echo('<h1>odpowiedź jest pusta!</h1>');
                    } else {
                        echo("<h1>To powinno się znaleźć w odpowiedzi:</h1>");
                        echo("<pre>");
                        print_r($oR);
                    }
                }
            }


            if ((INBROWSER === true)) echo(" saving response $nrRes " . NLB);
            $client->OrderResponsePut(array('Response' => $oR));
        }

    }


    /**
     * Zwraca pustą tablicę lub tablicę id utworzonych zamówień w panelu morelowym
     * @author Tymek
     * @return array tablica id utworzonych zamówień w panelu morelowym
     */
    public function createOrders()
    {
        $products = $this->_products;

        $ordd = new \Mns\Service\Order();

        $orders = [];


        foreach ($products as $order => $its) {

            $items = $this->prepareItems($its, $order);

            if (!empty($items)) {

                $this->_conn->beginTransaction();

                try {
                    // Tworzenie zamowienia w panelu, ustawianie opoznionej platnosci, itemstate dla produktow itp.
                    $order_result = $ordd->createOrder($items, $this->_user_id, $this->_deliveryinfo, $this->_order_properties);
                    if ($order_result['errors'] > 0) {
                        $this->logger->err("Blad: Zamowienie $order ABCDaty ABCDataHub nie zostalo poprawnie zaimportowane. Wykonuje rollback.");
                        $this->_conn->rollback();
                        return $order_result; // jesli poszlo cos zle
                    }
                    $this->_conn->commit();
                    $id = $order_result['id'];
                    // $this->setPaymentDelayed($id); //dawniej: ustawienie opoznionej platnosci po zmianie w Order dokonanej przez Arka:
                    $ordd->addOrderPayment($id, 0, 1); // done=1 <-- płatność dokonana
                    $this->setItemState($id); // ustawienie itemstate dla produkow
                    $orders[] = $id; // wrzucenie id zamowienia do tabeli

                    $comment = " Utworzono na podstawie zamówienia: $order z ABCData HUB  ";

                    $ordd->addOrderComment($id, 0, $comment, 0);

                    if (INBROWSER === true) echo("podstawiam: orders_map[$order]=$id " . NLB . NLB);
                    $this->_orders_map[$order] = $id;  // mapowanie zamówień w następujący sposób: _orders_map[ABCData_order_id]=morele_order_id

                } catch (PropelException $e) {

                    // Poszło całkiem źle Rollback całej transakcji
                    $this->_conn->rollback();
                    throw $e;
                }
                $this->_orders_ids = $orders;
            } else {
                if (INBROWSER === true) echo("Nie zostały spełnione warunki konieczne do realizacji zamówienia $order. Zamówienie $order zostało odrzucone" . NLB . NLB);
                $this->logger->err("Blad: Nie zostaly spelnione warunki do realizacji zamowienia $order ABCDaty ABCDataHub");
                $logg = array($its, $order);
                if (STORAGE_LOGS_ON) file_put_contents(DIR_SHARED . 'abcdata_hub_logs' . DIRECTORY_SEPARATOR . 'odrzucone_' . date('YmdHis') . '.OrderGetResults.log.txt', serialize($logg));
            }
        }
        $this->sendOrderResponses();
        return ($this->_orders_map);
    }

    public function get_Orders()
    {
        return $this->_orders_ids;
    }

    public function get_Products()
    {
        return $this->_products;
    }


    /**
     * Zwraca surowe dane do dalszej obróbki, na podstawie wypełnianej przez PMów tabeli product_pricelevel
     * używa PDO zamiast propella
     * @author Tymek
     * @return array tablica wierszy pobranych z bazy danych
     */
    function getRawData4ABCData()
    {
        $query = "SELECT *,GROUP_CONCAT(DISTINCT product_ean.product_ean) AS eans
		FROM `product_pricelevel`
		LEFT JOIN alias ON product_pricelevel.`product_id`=alias.`product_id`
		LEFT JOIN product ON product.`product_id`=alias.`product_id`
		INNER JOIN product_ean ON product.`product_id`=product_ean.`product_id`
		WHERE supplier_id = 7 AND `alias_quantity`>0 AND product.product_active=1 AND product.product_onStock>0
		GROUP BY product.`product_id`
		ORDER BY `alias_quantity` DESC
		";

        $stmt = $this->_conn->prepare($query);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        return $this->keys4RawData($res);
    }

    /**
     * Dopasowuje odpowiednie klucze do tabeli pobranych danych z pricelevel
     * @param $ProductsSetsArray
     * @return array
     */
    private function keys4RawData($ProductsSetsArray)
    {
        $result_array = [];
        array_walk($ProductsSetsArray,
            function ($a) use (&$result_array) {
                $result_array[$a['product_id']] = $a;
            },
            $result_array);
        return $result_array;
    }

    /**
     * ustawia itemstate produktu w zamówieniu na 4 (home)
     * @param int id id produktu do ustawienia itemstate
     * @author Tymek
     */
    function setItemState($id)
    {
        $query = "UPDATE item SET item_state=" . HOME_PRODUCT_STATE . " WHERE order_id =$id;";
        $stmt = $this->_conn->prepare($query);
        $stmt->execute();
    }

    /**
     * @assert ("5901425361002", false) == "274628"
     *
     * pobiera produkty na podstawie kodów ean i wpisów w abcdata hub pricelevel (tablica product_pricelevel)
     * @param array / string $eans tablica lub ciąg oddzielonych przecinkami kodów ean
     * @param boolean $do_testow - jeśli jest TRUE ignorowane jest, że produkt nie został dodany do PRICELEVEL przez PM-a - tylko do celów testowych - nie używać na produkcji!
     * @return array $res tablica wierszy pobranych z bazy danych
     * @author Tymek
     */
    public function getProductsByEans($eans, $do_testow = false)
    {

        if (empty($eans)) return false;

        if (is_array($eans)) {
            $ens = implode('" OR `product_ean` LIKE "', $eans) . '"';
        } else {
            $ens = implode('" OR `product_ean` LIKE "', explode(',', $eans)) . '"';
        }

        if (!$do_testow) {
            $query = " SELECT * FROM product_pricelevel ";
            $query .= " LEFT JOIN product ON product_pricelevel.`product_id`=product.`product_id` ";
        } else {
            $query = " SELECT * FROM product ";
            $query .= " LEFT JOIN product_pricelevel ON product_pricelevel.`product_id`=product.`product_id` ";
        }

        $query .=
            " LEFT JOIN alias ON product.`product_id`=alias.`product_id`
			LEFT JOIN product_ean ON product.`product_id`=product_ean.`product_id` ";


        if (!$do_testow) {
            $query .= "
			WHERE 1=1 
			AND supplier_id = 7 AND `alias_quantity`>0 AND product.product_active=1 AND product.product_onStock>0 AND  ";
        } else {
            $query .= " WHERE ";
        }

        $query .= "	(`product_ean` LIKE " . '"' . $ens . ")
			GROUP BY product.`product_id` ORDER BY `alias_quantity` DESC";

        $stmt = $this->_conn->prepare($query);
        $stmt->execute();

        $res = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $res;
    }
}
