Payon Xml Api PHP Class
=======================

This is a simple unofficial PayOn XML API client implementation in PHP. These classes will make easier to execute API commands on PayOn server and Query data from it.

Usage Examples
==============

*User Registration Example*

```php
require_once 'vendor/autoload.php';

use DatingVIP\Payment\PayOn\TransactionParams;
use DatingVIP\Payment\PayOn\QueryParams;
use DatingVIP\Payment\PayOn\XmlApi;

// config - replace to your values

$config = [
    'sender'   => 'PAYON_SERVER_ID',
    'channel'  => 'PAYON_CHANNEL',
    'userid'   => 'PAYON_USER_ID',
    'password' => 'PAYON_PASSWORD'
];

// init api class
$po_api = new XmlApi(
                   $config['sender'],
                   $config['channel'],
                   $config['userid'],
                   $config['password'],
                   true );

// init and build transaction params
$po_req = new TransactionParams();

// register user for CC payment

$po_req->setAccountNumber('4111111111111111');
$po_req->setAccountExpYear('2015');
$po_req->setAccountExpMonth('12');
$po_req->setAccountHolder('John Smith');
$po_req->setAccountVerif('123');

$po_req->setCustNameFamily('Smith');
$po_req->setCustNameGiven('John');
$po_req->setCustAddrZip('12345');
$po_req->setCustAddrStreet('Main Street');
$po_req->setCustAddrCountry('US');
$po_req->setCustAddrCity('New York');

$po_req->setCustAddrState('New York');
$po_req->setCustContIp($_SERVER['REMOTE_ADDR']);
$po_req->setCustContEmail('customer@example.com');

$po_req->setAccountBrand('VISA');

$po_req->setPaymentMethod('CC.RG');

// execute request

$res = $po_api->executeTransaction($po_req);

if (empty($res)) // probably curl/communication error
{
    die('Communication error!');
}

if (!$po_api->wasTranSuccessful()) // unsuccessful reginstration
{
    $response = $po_api->getTranResponseData();
    die('ERROR: ' . $response['return_code'] . ' : ' . $response['return_msg']);
}

// display
displayReqResp($po_api, 'Registration');

```

*Charge registered user*

```php

// get registration UID for future use

$reg_uid = $po_api->getTranResponseData('unique_id');

// build params for charging the account

$po_req = new TransactionParams();

$po_req->setPaymentAmount('1.23');
$po_req->setPaymentCurrency('USD');

$po_req->setPaymentUsage('INV-TEST-' . time());
$po_req->setShopperId('CUST-' . time());
$po_req->setInvoiceId('INV-TEST-' . time());
$po_req->setPaymentMethod('CC.DB');
$po_req->setAccountRegId($reg_uid);
$po_req->setRecurrenceMode(Xml_Api::RECURRENCE_INIT);

// charge it
$res = $po_api->executeTransaction($po_req);

if (empty($res)) // probably curl/communication error
{
    die('Communication error!');
}

if (!$po_api->wasTranSuccessful()) // unsuccessful registration
{
    $response = $po_api->getTranResponseData();
    die('ERROR: ' . $response['return_code'] . ' : ' . $response['return_msg']);
}

// display
displayReqResp($po_api, 'Initial Charge');

```

*How to query data*

```php

// Query example

$query_params = new QueryParams(
                                QueryParams::LEVEL_CHANNEL,
                                $config['channel'],
                                QueryParams::TYPE_STANDARD );

$query_params->setTypes(array('DB', 'RG'));

$from   = date('Y-m-d', strtotime('-1 days', time()));
$to     = date('Y-m-d',  time());

$query_params->setPeriodFrom($from);
$query_params->setPeriodTo($to);

$po_api->executeQuery( $query_params );

$result = $po_api->getResponse();

// display

displayReqResp($po_api, 'Query Results');

```

*Just a helper function to show data in browser*

```php

// helper function

function displayReqResp(XmlApi $xml_api, $title = '')
{
    echo empty($title) ? '' : $title . '<hr />';
    echo 'REQUEST:<br /><pre>';
    echo htmlentities($xml_api->getRequest());
    echo '</pre><br />';
    echo 'RESPONSE:<br /><pre>';
    echo htmlentities($xml_api->getResponse());
    echo '</pre><br />';
}

```