<?php
/**
 * XmlApi Class
 *
 * @package		DatingVIP
 * @subpackage  Payment
 * @category	lib
 * @author		Boldizsar Bednarik <bbednarik@gmail.com>
 * @copyright   All rights reserved
 * @version     1.0
 */

namespace DatingVIP\Payment\PayOn;

use DatingVIP\cURL\Request as cURL;
use DOMDocument;
use Exception;
use SimpleXMLElement;

class XmlApi
{
    const TEST_SERVER_URL_TRANSACTION       = 'https://test.ctpe.net/payment/ctpe';
    const LIVE_SERVER_URL_TRANSACTION       = 'https://ctpe.io/payment/ctpe';

    const TEST_SERVER_URL_QUERY             = 'https://test.ctpe.io/payment/query';
    const LIVE_SERVER_URL_QUERY             = 'https://ctpe.io/payment/query';

    const API_VERSION                       = '1.0';
    const CURL_AGENT                        = 'php ctpepost';
    const CURL_TIMEOUT                       = 30;

    const SYNC_REQUEST                      = 'SYNC';
    const ASYNC_REQUEST                     = 'ASYNC';

    const TRAN_MODE_CONNECTOR_TEST          = 'CONNECTOR_TEST';
    const TRAN_MODE_INTEGRATOR_TEST         = 'INTEGRATOR_TEST';
    const TRAN_MODE_LIVE                    = 'LIVE';

    const RECURRENCE_INIT                   = 'INITIAL';
    const RECURRENCE_REBILL                 = 'REPEATED';

    const FORMAT_XML                        = 'xml';
    const FORMAT_JSON                       = 'json';
    const FORMAT_ARRAY                      = 'array';
    const FORMAT_OBJECT                     = 'object';

    // credentials

    private $sender;
    private $channel;
    private $userId;
    private $userPass;
    private $isTesting;

    // request & response

    private $request;
    private $response;

    // transaction values

    private $transactionMode;
    private $responseMode;

    /**
     * @var TransactionParams
     */
    protected $transactionParams;

    // query values

    private $queryMode;

    /**
     * @var QueryParams
     */
    private $queryParams;

    /**
     * Instance of DatingVIP\cURL\Request lib
     *
     * @var cURL
     */
    private $curl;

    /**
     * Constructor
     *
     * @param string $sender        Sender param
     * @param string $channel       Target channel
     * @param string $userId        User's ID
     * @param string $userPass      Password
     * @param string $isTesting     Use test parameters
     * @param string $testMode      Default test mode to use
     */
    public function __construct($sender, $channel, $userId, $userPass, $isTesting,$testMode = self::TRAN_MODE_INTEGRATOR_TEST)
    {
        $this->test_mode = $testMode;

        // set credentials and test value

        $this->sender       = $sender;
        $this->channel      = $channel;
        $this->userId       = $userId;
        $this->userPass     = $userPass;
        $this->isTesting    = $isTesting;

        // initialize stuff

        $this->transactionParams = new TransactionParams();
        $this->responseMode = self::SYNC_REQUEST;

        $this->request      = null;
        $this->response     = null;
    }

    /**
     * @var string test mode can be TRAN_MODE_INTEGRATOR_TEST or TRAN_MODE_CONNECTOR_TEST
     */
    private $test_mode;

    /**
     * Set test mode
     *
     * @param $test_mode
     */
    public function setTestMode($test_mode)
    {
        $this->test_mode = $test_mode;
    }

    /**
     * Get test mode
     *
     * @return string
     */
    public function getTestMode()
    {
        return $this->test_mode;
    }

    /**
     * Exec a transaction based on XML string
     *
     * @param string $xml_string    send xml string to payon
     *
     * @return bool                 false if there was an error
     */
    public function executeTransactionXML($xml_string = '')
    {
        if( empty($xml_string) ) { return false; }
        $this->transactionMode = ( empty( $this->isTesting ) ? self::TRAN_MODE_LIVE : $this->test_mode );
        $this->request = $xml_string;
        $url = ( empty( $this->isTesting ) ? self::LIVE_SERVER_URL_TRANSACTION : self::TEST_SERVER_URL_TRANSACTION );
        $res = $this->sendRequest($url);
        if ( empty($res) ) { return false; }	// curl error
        return true;
    }

    /**
     * Exec a transaction
     *
     * @param $params   TransactionParams    transaction params
     *
     * @return bool     false if there was an error
     */
    public function executeTransaction(TransactionParams $params)
    {
        $this->transactionParams = $params;
        $this->transactionMode = ( empty( $this->isTesting ) ? self::TRAN_MODE_LIVE : $this->test_mode );

        $this->generateXmlRequest();

        $url = ( empty( $this->isTesting ) ? self::LIVE_SERVER_URL_TRANSACTION : self::TEST_SERVER_URL_TRANSACTION );

        $res = $this->sendRequest($url);

        if ( empty($res) ) { return false; }	// curl error

        return true;
    }

    /**
     * Exec a query based on XML string
     *
     * @param $xml_string string send xml string to Payon
     *
     * @return bool     false if there was an error
     */
    public function executeQueryXML($xml_string = '')
    {
        if( empty($xml_string) ) { return false; }
        $this->queryMode = ( empty( $this->isTesting ) ? self::TRAN_MODE_LIVE : $this->test_mode );
        $this->request = $xml_string;
        $url = ( empty( $this->isTesting ) ? self::LIVE_SERVER_URL_QUERY : self::TEST_SERVER_URL_QUERY );
        $res = $this->sendRequest($url);
        if ( empty($res) ) { return false; }	// curl error
        return true;
    }

    /**
     * Exec a query
     *
     * @param $params   QueryParams    transaction params
     *
     * @return bool     false if there was an error
     */
    public function executeQuery(QueryParams $params)
    {
        $this->queryParams = $params;
        $this->queryMode = ( empty( $this->isTesting ) ? self::TRAN_MODE_LIVE : $this->test_mode );

        $this->generateXmlRequest(true);

        $url = ( empty( $this->isTesting ) ? self::LIVE_SERVER_URL_QUERY : self::TEST_SERVER_URL_QUERY );

        $res = $this->sendRequest($url);

        if ( empty($res) ) { return false; }	// curl error

        return true;
    }

    /**
     * Tells if the last transaction was successful
     *
     * @return boolean
     */
    public function wasTranSuccessful()
    {
        $sxo = self::xml2object($this->response);
        if( empty( $sxo ) ) { return false; }
        return ( (string) $sxo->Transaction->Processing->Result ) == 'ACK';
    }

    /**
     * Tells if the last query was successful
     *
     * @return boolean
     */
    public function wasQuerySuccessful()
    {
        $sxo = self::xml2object($this->response);
        if( empty( $sxo ) ) { return false; }
        return empty($sxo->Error);
    }

    /**
     * Get's string
     *
     * @param $string
     *
     * @return string
     */
    private static function getString($string)
    {
        return empty($string) ? '' : (string)$string;
    }

    /**
     * Return data from response as an array for easier use
     *
     * @param string $key           return only the desired key if passed, returns array if empty
     * @return array/false/string   array,string or false if data not present
     */
    public function getTranResponseData($key = '')
    {
        $sxo = self::xml2object($this->response);
        if( empty( $sxo ) ) { return false; }
        $prc = $sxo->Transaction->Processing;

        $ret = [
            'processing_code'   => self::getString( $prc->attributes()->code),
            'payment_code'      => self::getString( $sxo->Transaction->Payment->attributes()->code ),
            'timestamp'         => strtotime( self::getString( $prc->Timestamp )),
            'result'            => self::getString( $prc->Result),
            'status'            => self::getString( $prc->Status),
            'status_code'       => self::getString( $prc->Status->attributes()->code),
            'reason_msg'        => self::getString( $prc->Reason),
            'reason_code'       => self::getString( $prc->Reason->attributes()->code),
            'return_msg'        => self::getString( $prc->Return),
            'return_code'       => self::getString( $prc->Return->attributes()->code),
            'risk_score'        => !empty( $prc->Risk ) ? self::getString( $prc->Risk->attributes()->code) : '',
            'conf_status'       => self::getString( $prc->ConfirmationStatus),
            'unique_id'         => self::getString( $sxo->Transaction->Identification->UniqueID ),
            'security_hash'     => self::getString( $sxo->Transaction->Processing->SecurityHash ),
            'short_id'          => self::getString( $sxo->Transaction->Identification->ShortID ),
            'trans_id'          => self::getString( $sxo->Transaction->Identification->TransactionID ),
            'reference_id'      => self::getString( $sxo->Transaction->Identification->ReferenceID ),
            'c_amount'          => floatval(self::getString( $sxo->Transaction->Payment->Clearing->Amount )),
            'c_currency'        => self::getString( $sxo->Transaction->Payment->Clearing->Currency ),
            'c_descriptor'      => self::getString( $sxo->Transaction->Payment->Clearing->Descriptor ),
            'c_fx_rate'         => floatval(self::getString( $sxo->Transaction->Payment->Clearing->FxRate )),
            'c_fx_source'       => self::getString( $sxo->Transaction->Payment->Clearing->FxSource ),
            'c_fx_date'         => intval(strtotime( self::getString( $sxo->Transaction->Payment->Clearing->FxDate ))),
            'c_support_tel'     => self::getString( $sxo->Transaction->Payment->Clearing->Support ),
            't_mode'            => self::getString( $sxo->Transaction->attributes()->mode ),
            't_response'        => self::getString( $sxo->Transaction->attributes()->response ),
            't_channel'         => self::getString( $sxo->Transaction->attributes()->channel )
        ];

        return ( empty($key) ? $ret : $ret[$key] );
    }

    /**
     * Helper function for adding xml child element
     *
     * @param $root     SimpleXMLElement    root xml element
     * @param $name     string              element name
     * @param $value    string              element value
     */
    public static function addChildIfNotEmpty(&$root, $name, $value)
    {
        if( !empty($value) ) {

            // FIXED ON 2014.01.30. by BBOLDI
            // used this method because addChild does not escape '&' char - caused problems while testing
            // ref: http://www.php.net/manual/en/simplexmlelement.addchild.php#112204

            $root->$name = $value;
        }
    }

    /**
     * Converts xml object to array WITH attributes
     *
     * @param $obj SimpleXMLElement
     *
     * @return array
     */
    public static function xmlToRealArray($obj)
    {
        $arr = self::_xmlToRealArrayBase( $obj );
        if(empty($arr)) { return false; }
        $res = [];
        self::_xmlToRealArrayFixer($arr, $res);
        return $res;
    }

    /**
     * Converts xml object to array WITH attributes
     *
     * @param $obj SimpleXMLElement
     *
     * @return array
     */
    private static function _xmlToRealArrayBase($obj)
    {
        $namespace       = $obj->getDocNamespaces( true );
        $namespace[null] = null;

        $children   = [];
        $attributes = [];
        $name       = (string)$obj->getName();

        $value = trim( (string)$obj );
        if ( strlen( $value ) <= 0 ) { $value = null; }

        if ( is_object( $obj ) ) {
            foreach ( $namespace as $ns => $nsUrl ) {

                $objAttributes = $obj->attributes( $ns, true );

                foreach ( $objAttributes as $attributeName => $attributeValue ) {
                    $attribName = trim( (string)$attributeName );
                    $attribVal  = trim( (string)$attributeValue );
                    if ( !empty( $ns ) ) { $attribName = $ns . ':' . $attribName; }
                    $attributes[$attribName] = $attribVal;
                }

                $objChildren = $obj->children( $ns, true );
                foreach ( $objChildren as $childName => $child ) {
                    $childName = (string)$childName;
                    if ( !empty( $ns ) ) { $childName = $ns . ':' . $childName; }
                    $children[$childName][] = self::_xmlToRealArrayBase( $child );
                }
            }
        }

        $ret = [];

        if(!empty($name))       { $ret['@n']=$name; }
        if(!empty($value))      { $ret['@v']=$value; }
        if(!empty($attributes)) { $ret['@a']=$attributes; }
        if(!empty($children))   { $ret['@c']=$children; }

        return $ret;
    }

    /**
     * Helper function to convert xml array to more readable array
     *
     * @param $array
     * @param $result
     * @param string $path
     * @param string $separator
     */
    private static function _xmlToRealArrayFixer($array, &$result, $path = '',$separator='/')
    {
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                self::_xmlToRealArrayFixer($value, $result, $path, $separator);
            } else {
                if($key == '@v' || strpos($key, '@') === false ) {
                    // add @ before attributes

                    $ktemp = '@'.$key;
                    $ktemp = ($ktemp == '@@v' ? '' : $ktemp);
                    $ktemp = trim( $path.$ktemp, $separator);
                    $ckey = $ktemp;

                    // add counter to elements when there is more than one with the same key

                    $c = 1;
                    while(array_key_exists($ckey, $result)) {
                        if(strpos($ktemp, $separator.'@') === false) {
                            $ckey = $ktemp.'_'.self::getString($c);
                        } else {
                            // when we are dealing with multiple attributes insert the counter before @
                            $ckey = str_replace( $separator.'@', '_'.self::getString($c).$separator.'@', $ktemp);
                        }
                        $c++;
                    }

                    $result[$ckey] = $value;
                } else {
                    $path .= $value.$separator;
                }
            }
        }
    }

    /**
     * Converts xml to json
     *
     * @param $xml_string   string   valid xml string
     *
     * @return string/bool   json formated string or false on error
     */
    public static function xml2json($xml_string)
    {
        $arr = self::xml2array($xml_string);
        return ( empty($arr) ? false : json_encode($arr, true) );
    }

    /**
     * Converts xml to array
     *
     * @param $xml_string   string   valid xml string
     *
     * @return array/bool   array or false on error
     */
    public static function xml2array($xml_string)
    {
        $xml = self::xml2object($xml_string);
        return ( empty($xml) ? false : self::xmlToRealArray( $xml ) );
    }

    /**
     * Converts xml to object
     *
     * @param $xml_string   string   valid xml string
     *
     * @return SimpleXMLElement/bool   object or false on error
     */
    public static function xml2object($xml_string)
    {
        try {
            $xml = simplexml_load_string( $xml_string, 'SimpleXMLElement' , ( LIBXML_NOERROR | LIBXML_NOWARNING ) );
        } catch(Exception $exception) {
            // @todo log errors and/or exception see libxml_use_internal_errors(), libxml_get_errors()
            return false;
        }

        return $xml;
    }

    /**
     * Returns request in desired format
     *
     * @param string $format xml or json
     *
     * @return null|string
     */
    public function getRequest($format = self::FORMAT_XML)
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return self::xml2json( $this->request );

            case self::FORMAT_ARRAY:
                return self::xml2array( $this->request );

            case self::FORMAT_OBJECT:
                return self::xml2object( $this->request );
        }

        // default xml
        return $this->request;
    }

    /**
     * A function to ser response so we can process data that comes from payon notification
     *
     * @param $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * Set response mode (self::SYNC_REQUEST | self::ASYNC_REQUEST)
     *
     * @param $responseMode
     */
	public function setResponseMode($responseMode)
    {
        $this->responseMode = $responseMode;
    }

    /**
     * Returns response in desired format
     *
     * @param string $format xml or json
     *
     * @return null|string
     */
    public function getResponse($format = self::FORMAT_XML)
    {
        switch ($format) {
            case self::FORMAT_JSON:
                return self::xml2json( $this->response );

            case self::FORMAT_ARRAY:
                return self::xml2array( $this->response );

            case self::FORMAT_OBJECT:
                return self::xml2object( $this->response );
        }

        // default xml
        return $this->response;
    }

    /**
     * Add query to XML while building
     *
     * @param $root
     * @return mixed
     */
    private function addQueryXml(SimpleXMLElement &$root)
    {
        // add transaction

        $query = $root->addChild('Query');
        $query->addAttribute( 'mode',     $this->queryMode );
        $query->addAttribute( 'level',  $this->queryParams->getLevel() );
        $query->addAttribute( 'entity',  $this->queryParams->getEntity() );
        $query->addAttribute( 'type',  $this->queryParams->getType() );
        if($this->queryParams->getMaxCount()) { $query->addAttribute( 'maxCount',  $this->queryParams->getMaxCount() ); }

        // add user

        $user = $query->addChild('User');
        $user->addAttribute( 'login', $this->userId );
        $user->addAttribute( 'pwd',  $this->userPass );

        // add indentification

        $id = $query->addChild( 'Identification' );

        $uid = $this->queryParams->getIdentificationUniqueID();

        if(is_array($uid) && !empty($uid)) {
            // if there is more UniqueID defined

            $uidsn = $id->addChild('UniqueIDs');

            foreach($uid as $cuid) {
                $uidsn->addChild('ID', $cuid);
            }
        } else {
            // if there is only one UniqueID defined

            self::addChildIfNotEmpty( $id, 'UniqueID',  $uid );
        }


        self::addChildIfNotEmpty( $id, 'ShortID',  $this->queryParams->getIdentificationShortID() );
        self::addChildIfNotEmpty( $id, 'TransactionID',  $this->queryParams->getIdentificationTransactionID() );

        if( $id->count()==0 ) { unset($query->Identification); }

        // transaction type

        self::addChildIfNotEmpty( $query, 'TransactionType',  $this->queryParams->getTransactionType() );

        // period

        if($this->queryParams->isPeriodSet()) {
            $period = $query->addChild('Period');
            $period->addAttribute( 'from',     $this->queryParams->getPeriodFrom() );
            $period->addAttribute( 'to', $this->queryParams->getPeriodTo() );
        }

        // Methods group

        $mtds = $this->queryParams->getMethods();

        if(!empty($mtds)) {
            $methodn = $query->addChild('Methods');

            foreach($$mtds as $method) {
                $methodn->addChild('Method', $method);
            }
        }

        // Types group

        $typs = $this->queryParams->getTypes();

        if(!empty($typs)) {
            $typesn = $query->addChild('Types');

            foreach($typs as $typ) {
                $tc = $typesn->addChild('Type');
                $tc->addAttribute('code', $typ);
            }
        }

        // proc res group

        self::addChildIfNotEmpty( $query, 'ProcessingResult',  $this->queryParams->getProcessingResult() );

        // account group

        $accn = $query->addChild('Account');

        self::addChildIfNotEmpty( $query, 'Id',  $this->queryParams->getAccId() );
        self::addChildIfNotEmpty( $query, 'Brand',  $this->queryParams->getAccBrand() );
        self::addChildIfNotEmpty( $query, 'Password',  $this->queryParams->getAccPassword() );

        if( $accn->count()==0 ) { unset($query->Account); }

        return $query;
    }

    /**
     * Send the Curl post request
     *
     * @param $url  string where we send the xml request
     *
     * @return bool returns false if there is a curl error
     */
    protected function sendRequest($url)
    {
        $this->curl = new cURL([ CURLOPT_USERAGENT   =>  self::CURL_AGENT ]);
        $this->curl->setHeader('Content-Type' , 'application/x-www-form-urlencoded;charset=UTF-8');
        $this->curl->setTimeout(self::CURL_TIMEOUT);

        try {
            $response = $this->curl->post( $url, 'load='.urlencode($this->request) );
            $this->response = $response->getData();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Helper function for building XML request
     *
     * @param $root SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    private function addHeaderXml(SimpleXMLElement &$root)
    {
        $header = $root->addChild('Header');
        $security = $header->addChild('Security');
        $security->addAttribute( 'sender', $this->sender );
        return $header;
    }

    /**
     * Helper function for building XML request
     *
     * @param $root SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    private function addTransactionXml(SimpleXMLElement &$root)
    {
        // add transaction

        $transaction = $root->addChild('Transaction');
        $transaction->addAttribute( 'mode',     $this->transactionMode );
        $transaction->addAttribute( 'response', $this->responseMode );
        $transaction->addAttribute( 'channel',  $this->channel );

        // add user

        $user = $transaction->addChild('User');
        $user->addAttribute( 'login', $this->userId );
        $user->addAttribute( 'pwd',  $this->userPass );

        // add indentification

        $id = $transaction->addChild( 'Identification' );

        self::addChildIfNotEmpty( $id, 'TransactionID',  $this->transactionParams->getTransactionId() );
        self::addChildIfNotEmpty( $id, 'ReferenceID',  $this->transactionParams->getReferenceId() );
        self::addChildIfNotEmpty( $id, 'ShopperID',  $this->transactionParams->getShopperId());
        self::addChildIfNotEmpty( $id, 'InvoiceID',  $this->transactionParams->getInvoiceId() );
        self::addChildIfNotEmpty( $id, 'OrderID',  $this->transactionParams->getOrderId() );

        if( $id->count()==0 ) { unset($transaction->Identification); }

        // add payment

        $pm = $transaction->addChild( 'Payment' );
        $pm->addAttribute( 'code',  $this->transactionParams->getPaymentMethod() );
        self::addChildIfNotEmpty( $pm, 'Memo',  $this->transactionParams->getPaymentMemo() );

        $pr = $pm->addChild( 'Presentation' );
        self::addChildIfNotEmpty( $pr, 'Amount',    $this->transactionParams->getPaymentAmount() );
        self::addChildIfNotEmpty( $pr, 'Currency',  $this->transactionParams->getPaymentCurrency() );
        self::addChildIfNotEmpty( $pr, 'Usage',     $this->transactionParams->getPaymentUsage() );

        if( $pr->count()==0 ) { unset($pm->Presentation); }

        // Account Registration

        // recurrence

        if( $this->transactionParams->hasRecurrence() ) {
            $rec = $transaction->addChild( 'Recurrence' );
            $rec->addAttribute( 'mode', $this->transactionParams->getRecurrenceMode() );
        }

        // account
        $this->addAccountXml( $transaction );
        $reg_attr = $this->transactionParams->getCustRegistration();

        if( !$this->transactionParams->isSubsequest() || !empty( $reg_attr ) ) {
            // customer
            $this->addCustomerXml( $transaction );
        }

        // frontend
        $this->addFrontendXml( $transaction );

        //analysis
        $this->addAnalysisParams( $transaction );

        return $transaction;
    }

    /**
     * Helper function for building XML request
     *
     * @param $root SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    private function addAccountXml(SimpleXMLElement &$root)
    {
        $acc = $root->addChild( 'Account' );

        if( $this->transactionParams->isSubsequest() ) {
            $acc->addAttribute( 'registration', $this->transactionParams->getAccountRegId() );
        }

        self::addChildIfNotEmpty( $acc, 'Holder',       $this->transactionParams->getAccountHolder() );
        self::addChildIfNotEmpty( $acc, 'Number',       $this->transactionParams->getAccountNumber() );
        self::addChildIfNotEmpty( $acc, 'Brand',        $this->transactionParams->getAccountBrand() );
        self::addChildIfNotEmpty( $acc, 'Bic',          $this->transactionParams->getAccountBic() );
        self::addChildIfNotEmpty( $acc, 'Iban',         $this->transactionParams->getAccountIban() );

        if( $this->transactionParams->hasExpireDate() ) {
            $exp = $acc->addChild( 'Expiry' );
            $exp->addAttribute( 'month', $this->transactionParams->getAccountExpMonth() );
            $exp->addAttribute( 'year', $this->transactionParams->getAccountExpYear() );
            $acc->addChild( 'Year', $this->transactionParams->getAccountExpYear() );
            $acc->addChild( 'Month', $this->transactionParams->getAccountExpMonth() );
        }

        self::addChildIfNotEmpty( $acc, 'Verification', $this->transactionParams->getAccountVerif() );
        self::addChildIfNotEmpty( $acc, 'Bank',         $this->transactionParams->getAccountBank() );
        self::addChildIfNotEmpty( $acc, 'BankName',     $this->transactionParams->getAccountBankName() );
        self::addChildIfNotEmpty( $acc, 'Country',      $this->transactionParams->getAccountCountry() );
        self::addChildIfNotEmpty( $acc, 'Id',           $this->transactionParams->getAccountId() );
        self::addChildIfNotEmpty( $acc, 'Password',     $this->transactionParams->getAccountPassword() );

        return $acc;
    }

    /**
     * Helper function for building XML request
     *
     * @param $root SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    private function addAnalysisParams(SimpleXMLElement &$root)
    {
        if( $this->transactionParams->hasAnalysisParams() ) {
            $an = $root->addChild( 'Analysis' );
            $ap = $this->transactionParams->getAnalysisParams();
            foreach($ap as $k => $v) {
                $ch = $an->addChild( 'Criterion', $v );
                $ch->addAttribute( 'name', $k );
            }

            return $an;
        }

        return false;
    }

    /**
     * Helper function for building XML request
     *
     * @param $root SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    private function addCustomerXml(SimpleXMLElement &$root)
    {
        $cus = $root->addChild( 'Customer' );

        $reg_attr = $this->transactionParams->getCustRegistration();

        if( !empty( $reg_attr ) ) {
            $cus->addAttribute( 'registration', $reg_attr );
        }

        $name = $cus->addChild( 'Name' );
        $addr = $cus->addChild( 'Address' );
        $cont = $cus->addChild( 'Contact' );

        self::addChildIfNotEmpty( $name, 'Salutation', $this->transactionParams->getCustNameSalut() );
        self::addChildIfNotEmpty( $name, 'Title', $this->transactionParams->getCustNameTitle() );
        self::addChildIfNotEmpty( $name, 'Given', $this->transactionParams->getCustNameGiven() );
        self::addChildIfNotEmpty( $name, 'Family', $this->transactionParams->getCustNameFamily() );
        self::addChildIfNotEmpty( $name, 'Sex', $this->transactionParams->getCustNameSex() );
        self::addChildIfNotEmpty( $name, 'Birthdate', $this->transactionParams->getCustNameBirthDate() );
        self::addChildIfNotEmpty( $name, 'Company', $this->transactionParams->getCustNameCompany() );

        if( $name->count()==0 ) { unset($cus->Name); }

        self::addChildIfNotEmpty( $addr, 'Street', $this->transactionParams->getCustAddrStreet() );
        self::addChildIfNotEmpty( $addr, 'Zip', $this->transactionParams->getCustAddrZip() );
        self::addChildIfNotEmpty( $addr, 'City', $this->transactionParams->getCustAddrCity() );
        self::addChildIfNotEmpty( $addr, 'State', $this->transactionParams->getCustAddrState() );
        self::addChildIfNotEmpty( $addr, 'Country', $this->transactionParams->getCustAddrCountry() );

        if( $addr->count()==0 ) { unset($cus->Address); }

        self::addChildIfNotEmpty( $cont, 'Phone', $this->transactionParams->getCustContPhone() );
        self::addChildIfNotEmpty( $cont, 'Mobile', $this->transactionParams->getCustContMobile() );
        self::addChildIfNotEmpty( $cont, 'Email', $this->transactionParams->getCustContEmail() );
        self::addChildIfNotEmpty( $cont, 'Ip', $this->transactionParams->getCustContIp() );

        if( $cont->count()==0 ) { unset($cus->Contact); }

        if( $this->transactionParams->hasIdentity() ) {
            $det = $cus->addChild( 'Details' );
            $id = $det->addChild( 'Identity', $this->transactionParams->getCustDetType() );
            $id->addAttribute ( 'paper', $this->transactionParams->getCustDetIdentity() );
        }
    }

    /**
     * Helper function for building XML request
     *
     * @param $root SimpleXMLElement
     *
     * @return SimpleXMLElement
     */
    private function addFrontendXml(SimpleXMLElement &$root)
    {
        if( $this->transactionParams->hasFrontend() ) {
            $fro = $root->addChild( 'Frontend' );

            $fro->addChild( 'ResponseUrl',  $this->transactionParams->getFrontendRespURL() );
            $fro->addChild( 'SessionID',    $this->transactionParams->getFrontendSessId() );
        }
    }

    /**
     * Generates valid formatted XML request
     *
     * @param bool $is_query is it a query request
     */
    private function generateXmlRequest($is_query = false)
    {
        $xml = new SimpleXMLElement('<Request/>');
        $xml->addAttribute( 'version', self::API_VERSION );

        $this->addHeaderXml($xml);

        if($is_query) {
            $this->addQueryXml($xml);
        } else {
            $this->addTransactionXml($xml);
        }

        // format xml

        $dom = new DOMDocument('1.0', 'UTF-8'); // encoding needs to be UTF-8!
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML( $xml->asXML() );

        $this->request = $dom->saveXML();
        $this->request = str_replace('<?xml version="1.0"?>', '<?xml version="1.0" encoding="UTF-8"?>', $this->request);
    }

    /**
     * Get processing params result
     *
     * @param $processing_code
     * @return array
     */
    public static function getProcessingResultParts(array $processing_code)
    {
        $pcarr = explode( '.', $processing_code );

        return [
            'payment_method'    => self::getString( $pcarr[0] ),
            'payment_type'      => self::getString( $pcarr[1] ),
            'payment_code'      => self::getString( $pcarr[0] ).'.'.self::getString( $pcarr[1] ),
            'status_code'       => self::getString( $pcarr[2] ),
            'reason_code'       => self::getString( $pcarr[3] )
        ];
    }
}
