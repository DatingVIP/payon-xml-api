<?php
/**
 * TransactionParams Class
 *
 * @package		DatingVIP
 * @subpackage  Payment
 * @category	lib
 * @author		Boldizsar Bednarik <bbednarik@gmail.com>
 * @copyright   All rights reserved
 * @version     1.0
 */

namespace DatingVIP\Payment\PayOn;

class TransactionParams
{
    // identification

    private $transactionId      = '';       // defined by merchant
    private $referenceId        = '';       // for subsequent transactions
    private $shopperId          = '';       // defined by merchant
    private $invoiceId          = '';       // defined by merchant
    private $orderId            = '';       // order id - optional

    // payment

    private $paymentMethod      = '';
    private $paymentAmount      = '';
    private $paymentCurrency    = '';
    private $paymentUsage       = '';       // dynamic part of the descriptor - orderId for example

    private $paymentMemo        = '';

    // recurrance

    private $recurrenceMode     = '';

    // account common / cc

    private $accountHolder      = '';
    private $accountNumber      = '';
    private $accountBrand       = '';
    private $accountExpMonth    = '';       // 2 digits
    private $accountExpYear     = '';       // 4 digits
    private $accountVerif       = '';

    private $accountRegId       = '';       // used for subsequent transactions

    // acc. bank

    private $accountBank        = '';
    private $accountBankName    = '';
    private $accountCountry     = '';
    private $accountBic         = '';
    private $accountIban        = '';

    // acc. tagged

    private $accountId          = '';
    private $accountPassword    = '';

    // customer

    private $custNameSalut      = '';
    private $custNameTitle      = '';
    private $custNameGiven      = '';
    private $custNameFamily     = '';
    private $custNameSex        = '';
    private $custNameBirthDate  = '';       // yyyy-MM-dd
    private $custNameCompany    = '';
    private $custRegistration    = '';

    private $custAddrStreet     = '';
    private $custAddrZip        = '';
    private $custAddrCity       = '';
    private $custAddrState      = '';
    private $custAddrCountry    = '';
    private $custContPhone      = '';
    private $custContMobile     = '';
    private $custContEmail      = '';
    private $custContIp         = '';

    private $custDetIdentity    = '';
    private $custDetType        = '';

    // frontend

    private $frontendRespURL    = '';
    private $frontendSessId     = '';

    // Analysis

    private $analysisArray      = [];

    // getters / setters / other functions

    public function getAnalysisParams()
    {
        return $this->analysisArray;
    }

    public function hasAnalysisParams()
    {
        return !empty( $this->analysisArray );
    }

    /**
     * Add value to analysis group
     *
     * @param $name     string
     * @param $value    string
     */
    public function addAnalysysParam($name, $value)
    {
        $this->analysisArray[$name] = $value;
    }

    public function clearAnalysisParams()
    {
        $this->analysisArray = array();
    }

    public function isSubsequest()
    {
        return !empty($this->referenceId) || !empty($this->accountRegId);
    }

    public function setAccountRegId($accountRegId)
    {
        $this->accountRegId = $accountRegId;
    }

    public function getAccountRegId()
    {
        return $this->accountRegId;
    }

    public function setCustRegistration($custRegistration)
    {
        $this->custRegistration = $custRegistration;
    }

    public function getCustRegistration()
    {
        return $this->custRegistration;
    }

    public function setPaymentMemo($paymentMemo)
    {
        $this->paymentMemo = $paymentMemo;
    }

    public function getPaymentMemo()
    {
        return $this->paymentMemo;
    }

    public function hasFrontend()
    {
        return ( !empty($this->frontendRespURL) || !empty($this->frontendSessId) );
    }

    public function hasIdentity()
    {
        return !empty($this->custDetIdentity);
    }

    public function hasExpireDate()
    {
        return ( !empty($this->accountExpMonth) || !empty($this->accountExpYear) );
    }

    public function hasRecurrence()
    {
        return !empty( $this->recurrenceMode );
    }

    public function setAccountBank($accountBank)
    {
        $this->accountBank = $accountBank;
    }

    public function getAccountBank()
    {
        return $this->accountBank;
    }

    public function setAccountBankName($accountBankName)
    {
        $this->accountBankName = $accountBankName;
    }

    public function getAccountBankName()
    {
        return $this->accountBankName;
    }

    public function setAccountBic($accountBic)
    {
        $this->accountBic = $accountBic;
    }

    public function getAccountBic()
    {
        return $this->accountBic;
    }

    public function setAccountIban($accountIban)
    {
        $this->accountIban = $accountIban;
    }

    public function getAccountIban()
    {
        return $this->accountIban;
    }

    public function setAccountBrand($accountBrand)
    {
        $this->accountBrand = $accountBrand;
    }

    public function getAccountBrand()
    {
        return $this->accountBrand;
    }

    public function setAccountCountry($accountCountry)
    {
        $this->accountCountry = $accountCountry;
    }

    public function getAccountCountry()
    {
        return $this->accountCountry;
    }

    public function setAccountExpMonth($accountExpMonth)
    {
        $this->accountExpMonth = $accountExpMonth;
    }

    public function getAccountExpMonth()
    {
        return $this->accountExpMonth;
    }

    public function setAccountExpYear($accountExpYear)
    {
        $this->accountExpYear = $accountExpYear;
    }

    public function getAccountExpYear()
    {
        return $this->accountExpYear;
    }

    public function setAccountHolder($accountHolder)
    {
        $this->accountHolder = $accountHolder;
    }

    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    public function setAccountId($accountId)
    {
        $this->accountId = $accountId;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
    }

    public function getAccountNumber()
    {
        return $this->accountNumber;
    }

    public function setAccountPassword($accountPassword)
    {
        $this->accountPassword = $accountPassword;
    }

    public function getAccountPassword()
    {
        return $this->accountPassword;
    }

    public function setAccountVerif($accountVerif)
    {
        $this->accountVerif = $accountVerif;
    }

    public function getAccountVerif()
    {
        return $this->accountVerif;
    }

    public function setCustAddrCity($custAddrCity)
    {
        $this->custAddrCity = $custAddrCity;
    }

    public function getCustAddrCity()
    {
        return $this->custAddrCity;
    }

    public function setCustAddrCountry($custAddrCountry)
    {
        $this->custAddrCountry = $custAddrCountry;
    }

    public function getCustAddrCountry()
    {
        return $this->custAddrCountry;
    }

    public function setCustAddrState($custAddrState)
    {
        $this->custAddrState = $custAddrState;
    }

    public function getCustAddrState()
    {
        return $this->custAddrState;
    }

    public function setCustAddrStreet($custAddrStreet)
    {
        $this->custAddrStreet = $custAddrStreet;
    }

    public function getCustAddrStreet()
    {
        return $this->custAddrStreet;
    }

    public function setCustAddrZip($custAddrZip)
    {
        $this->custAddrZip = $custAddrZip;
    }

    public function getCustAddrZip()
    {
        return $this->custAddrZip;
    }

    public function setCustContEmail($custContEmail)
    {
        $this->custContEmail = $custContEmail;
    }

    public function getCustContEmail()
    {
        return $this->custContEmail;
    }

    public function setCustContIp($custContIp)
    {
        $this->custContIp = $custContIp;
    }

    public function getCustContIp()
    {
        return $this->custContIp;
    }

    public function setCustContMobile($custContMobile)
    {
        $this->custContMobile = $custContMobile;
    }

    public function getCustContMobile()
    {
        return $this->custContMobile;
    }

    public function setCustContPhone($custContPhone)
    {
        $this->custContPhone = $custContPhone;
    }

    public function getCustContPhone()
    {
        return $this->custContPhone;
    }

    public function setCustDetIdentity($custDetIdentity)
    {
        $this->custDetIdentity = $custDetIdentity;
    }

    public function getCustDetIdentity()
    {
        return $this->custDetIdentity;
    }

    public function setCustDetType($custDetType)
    {
        $this->custDetType = $custDetType;
    }

    public function getCustDetType()
    {
        return $this->custDetType;
    }

    public function setCustNameBirthDate($custNameBirthDate)
    {
        $this->custNameBirthDate = $custNameBirthDate;
    }

    public function getCustNameBirthDate()
    {
        return $this->custNameBirthDate;
    }

    public function setCustNameCompany($custNameCompany)
    {
        $this->custNameCompany = $custNameCompany;
    }

    public function getCustNameCompany()
    {
        return $this->custNameCompany;
    }

    public function setCustNameFamily($custNameFamily)
    {
        $this->custNameFamily = $custNameFamily;
    }

    public function getCustNameFamily()
    {
        return $this->custNameFamily;
    }

    public function setCustNameGiven($custNameGiven)
    {
        $this->custNameGiven = $custNameGiven;
    }

    public function getCustNameGiven()
    {
        return $this->custNameGiven;
    }

    public function setCustNameSalut($custNameSalut)
    {
        $this->custNameSalut = $custNameSalut;
    }

    public function getCustNameSalut()
    {
        return $this->custNameSalut;
    }

    public function setCustNameSex($custNameSex)
    {
        $this->custNameSex = $custNameSex;
    }

    public function getCustNameSex()
    {
        return $this->custNameSex;
    }

    public function setCustNameTitle($custNameTitle)
    {
        $this->custNameTitle = $custNameTitle;
    }

    public function getCustNameTitle()
    {
        return $this->custNameTitle;
    }

    public function setFrontendRespURL($frontendRespURL)
    {
        $this->frontendRespURL = $frontendRespURL;
    }

    public function getFrontendRespURL()
    {
        return $this->frontendRespURL;
    }

    public function setFrontendSessId($frontendSessId)
    {
        $this->frontendSessId = $frontendSessId;
    }

    public function getFrontendSessId()
    {
        return $this->frontendSessId;
    }

    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    public function setPaymentAmount($paymentAmount)
    {
        $this->paymentAmount = $paymentAmount;
    }

    public function getPaymentAmount()
    {
        return $this->paymentAmount;
    }

    public function setPaymentCurrency($paymentCurrency)
    {
        $this->paymentCurrency = $paymentCurrency;
    }

    public function getPaymentCurrency()
    {
        return $this->paymentCurrency;
    }

    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
    }

    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    public function setPaymentUsage($paymentUsage)
    {
        $this->paymentUsage = $paymentUsage;
    }

    public function getPaymentUsage()
    {
        return $this->paymentUsage;
    }

    public function setRecurrenceMode($recurrenceMode)
    {
        $this->recurrenceMode = $recurrenceMode;
    }

    public function getRecurrenceMode()
    {
        return $this->recurrenceMode;
    }

    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    public function getReferenceId()
    {
        return $this->referenceId;
    }

    public function setShopperId($shopperId)
    {
        $this->shopperId = $shopperId;
    }

    public function getShopperId()
    {
        return $this->shopperId;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }

    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
    }

    public function getTransactionId()
    {
        return $this->transactionId;
    }
}
