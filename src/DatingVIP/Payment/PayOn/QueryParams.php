<?php
/**
 * QueryParams Class
 *
 * @package		DatingVIP
 * @subpackage  Payment
 * @category	lib
 * @author		Boldizsar Bednarik <bbednarik@gmail.com>
 * @copyright   All rights reserved
 * @version     1.0
 */

namespace DatingVIP\Payment\PayOn;

class QueryParams
{
    // level constants

    const LEVEL_CHANNEL     = 'CHANNEL';
    const LEVEL_MERCHANT    = 'MERCHANT';
    const LEVEL_PSP         = 'PSP';

    // type constants

    const TYPE_STANDARD                         = 'STANDARD';
    const TYPE_ACTIVE_TRANSACTIONS	            = 'ACTIVE_TRANSACTIONS';
    const TYPE_LINKED_TRANSACTIONS              = 'LINKED_TRANSACTIONS';
    const TYPE_AVAILABLE_TRANSACTIONS           = 'AVAILABLE_TRANSACTIONS';
    const TYPE_ACTIVE_LINKED_TRANSACTIONS       = 'ACTIVE_LINKED_TRANSACTIONS';
    const TYPE_AVAILABLE_LINKED_TRANSACTIONS    = 'AVAILABLE_LINKED_TRANSACTIONS';

    // source constants

    const SOURCE_SCHEDULER = "SCHEDULER";

    // transaction type constants

    const TRAN_TYPE_PAYMENT		    = "PAYMENT";
    const TRAN_TYPE_REGISTER	    = "REGISTER";
    const TRAN_TYPE_SCHEDULE	    = "SCHEDULE";
    const TRAN_TYPE_RISK_MANAGEMENT	= "RISKMANAGEMENT";

    // processing result constants

    const PROC_RES_ACK = "ACK";
    const PROC_RES_NOK = "NOK";

    /**
     * @var string
     */
    private $level;

    /**
     * @var string
     */
    private $type;

    /**
     * @var integer
     */
    private $max_count;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $source;

    /**
     * @var mixed
     */
    private $identificationUniqueID;

    /**
     * @var string
     */
    private $identificationShortID;

    /**
     * @var string
     */
    private $identificationTransactionID;

    /**
     * @var string
     */
    private $transactionType;

    /**
     * @var integer
     */
    private $periodFrom;

    /**
     * @var integer
     */
    private $periodTo;

    /**
     * @var array
     */
    private $methods;

    /**
     * @var array
     */
    private $types;

    /**
     * @var string
     */
    private $processingResult;

    /**
     * @var string
     */
    private $accId;

    /**
     * @var string
     */
    private $accPassword;

    /**
     * @var string
     */
    private $accBrand;

    /**
     * Constructor
     *
     * @param string $level    Query level param
     * @param string $entity   Query entity param
     * @param string $type     Query type param
     */
    public function __construct($level, $entity, $type)
    {
        $this->setEntity($entity);
        $this->setLevel($level);
        $this->setType($type);
    }

    /**
     * @param string $accBrand
     */
    public function setAccBrand($accBrand)
    {
        $this->accBrand = $accBrand;
    }

    /**
     * @return string
     */
    public function getAccBrand()
    {
        return $this->accBrand;
    }

    /**
     * @param string $accId
     */
    public function setAccId($accId)
    {
        $this->accId = $accId;
    }

    /**
     * @return string
     */
    public function getAccId()
    {
        return $this->accId;
    }

    /**
     * @param string $accPassword
     */
    public function setAccPassword($accPassword)
    {
        $this->accPassword = $accPassword;
    }

    /**
     * @return string
     */
    public function getAccPassword()
    {
        return $this->accPassword;
    }



    /**
     * @param string $processingResult
     */
    public function setProcessingResult($processingResult)
    {
        $this->processingResult = $processingResult;
    }

    /**
     * @return string
     */
    public function getProcessingResult()
    {
        return $this->processingResult;
    }

    /**
     * @param array $types
     */
    public function setTypes($types)
    {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param array $methods
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param string $transactionType
     */
    public function setTransactionType($transactionType)
    {
        $this->transactionType = $transactionType;
    }

    /**
     * @return string
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    /**
     * Tells if some of the period limits is set
     *
     * @return bool
     */
    public function isPeriodSet()
    {
        return ( !empty($this->periodFrom) || !empty($this->periodTo) );
    }

    /**
     * Set from date
     *
     * @param $string
     */
    public function setPeriodFrom($string)
    {
        $this->periodFrom = strtotime( $string );
    }

    /**
     * @return int
     */
    public function getPeriodFrom()
    {
        if(empty($this->periodFrom)) {return '';}
        return date('Y-m-d H:i:s', $this->periodFrom);
    }

    /**
     * Set from date
     *
     * @param string $string    About any English textual datetime description into a Unix timestamp
     */
    public function setPeriodTo($string)
    {
        $this->periodTo = strtotime( $string );
    }

    /**
     * @return int
     */
    public function getPeriodTo()
    {
        if(empty($this->periodTo)) {return '';}
        return date('Y-m-d H:i:s', $this->periodTo);
    }

    /**
     * @param string $identificationShortID
     */
    public function setIdentificationShortID($identificationShortID)
    {
        $this->identificationShortID = $identificationShortID;
    }

    /**
     * @return string
     */
    public function getIdentificationShortID()
    {
        return $this->identificationShortID;
    }

    /**
     * @param string $identificationTransactionID
     */
    public function setIdentificationTransactionID($identificationTransactionID)
    {
        $this->identificationTransactionID = $identificationTransactionID;
    }

    /**
     * @return string
     */
    public function getIdentificationTransactionID()
    {
        return $this->identificationTransactionID;
    }

    /**
     * @param mixed $identificationUniqueID
     */
    public function setIdentificationUniqueID($identificationUniqueID)
    {
        $this->identificationUniqueID = $identificationUniqueID;
    }

    /**
     * @return mixed
     */
    public function getIdentificationUniqueID()
    {
        return $this->identificationUniqueID;
    }



    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $level
     */
    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param int $max_count
     */
    public function setMaxCount($max_count)
    {
        $this->max_count = $max_count;
    }

    /**
     * @return int
     */
    public function getMaxCount()
    {
        return $this->max_count;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
