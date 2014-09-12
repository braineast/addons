<?php
/**
 * Created by IntelliJ IDEA.
 * Author: al
 * Date: 6/24/2014
 * Time: 2:55 AM
 */

namespace frontend\models\api;

use Yii;
use yii\helpers\Json;


class ChinaPNR {
    const VERSION10 = 10;
    const VERSION20 = 20;

    const CMD_OPEN = 'UserRegister';
    const CMD_DEPOSIT = 'NetSave';
    const CMD_TENDER = 'InitiativeTender';
    const CMD_UNFREEZE = 'UsrUnFreeze';
    const CMD_CREDITASSIGN = 'CreditAssign';
    const CMD_TENDERCANCEL = 'TenderCancle';
    const CMD_CREDITASSIGNRECONCILIATION = 'CreditAssignReconciliation'; //债权查询接口
    const CMD_QUERYBALANCEBG = 'QueryBalanceBg';

    const PARAM_VERSION = 'Version';
    const PARAM_CMDID = 'CmdId';
    const PARAM_MERCUSTID = 'MerCustId';
    const PARAM_USRCUSTID = 'UsrCustId';
    const PARAM_SELLCUSTID = 'SellCustId';
    const PARAM_BUYCUSTID = 'BuyCustId';
    const PARAM_CREDITAMT = 'CreditAmt'; //债权转让转出的本金
    const PARAM_CREDITDEALAMT = 'CreditDealAmt'; //债权转让承接人付给转让人的金额
    const PARAM_FEE = 'Fee';    //放款或扣款的手续费
    const PARAM_DIVACCTID = 'DivAcctId'; //DivDetails 参数下的二级参数 分账账户号
    const PARAM_DIVAMT = 'DivAmt'; //DivDetails 参数下的二级参数 分账金额，保留两位小数
    const PARAM_DIVDETAILS = 'DivDetails';
    const PARAM_BIDDETAILS = 'BidDetails';
    const PARAM_ORDID = 'OrdId';
    const PARAM_ORDDATE = 'OrdDate';
    const PARAM_GATEBUSIID = 'GateBusiId';
    const PARAM_OPENBANKID = 'OpenBankId';
    const PARAM_DCFLAG = 'DcFlag';
    const PARAM_TRANSAMT = 'TransAmt';
    const PARAM_RETURL = 'RetUrl';
    const PARAM_BGRETURL = 'BgRetUrl';
    const PARAM_MERPRIV = 'MerPriv';
    const PARAM_USRID = 'UsrId';
    const PARAM_USRNAME = 'UsrName';
    const PARAM_IDTYPE = 'IdType';
    const PARAM_IDNO = 'IdNo';
    const PARAM_USRMP = 'UsrMp';
    const PARAM_USREMAIL = 'UsrEmail';
    const PARAM_CHARSET = 'CharSet';
    const PARAM_MAXTENDERRATE = 'MaxTenderRate';
    const PARAM_BORROWERDETAILS = 'BorrowerDetails';
    const PARAM_ISFREEZE = 'IsFreeze';
    const PARAM_FREEZEORDID = 'FreezeOrdId';
    const PARAM_FREEZETRXID = 'FreezeTrxId';
    const PARAM_REQEXT = 'ReqExt';
    const PARAM_CHKVALUE = 'ChkValue';
    const PARAM_PRIVATE_SHOWID = 'showId';
    const PARAM_ISUNFREEZE = 'IsUnFreeze';
    const PARAM_UNFREEZEORDID = 'UnFreezeOrdId';
    const PARAM_BEGINDATE = 'BeginDate';
    const PARAM_ENDDATE = 'EndDate';
    const PARAM_PAGENUM = 'PageNum';
    const PARAM_PAGESIZE = 'PageSize';
    const PARAM_AVLBAL = 'AvlBal';
    const PARAM_ACCTBAL = 'AcctBal';
    const PARAM_FRZBAL = 'FrzBal';

    const RESP_CODE = 'RespCode';
    const RESP_DESC = 'RespDesc';
    const RESP_TRXID = 'TrxId';
    const RESP_ORDID = 'OrdId';
    const RESP_RESPEXT = 'RespExt';
    const RESP_TOTALITEMS = 'TotalItems';

    protected $host;
    private $merId;
    protected $params;
    protected $response;
    private $link;
    private $queryString;
    private $maps;
    private $signOrder;
    private $vSignOrder;
    private $retUrl;
    private $bgRetUrl;
    private $showId;
    private $apiInfo;

    public function __construct($hostInfo = null)
    {
        $this->apiInfo = \Yii::$app->params['api']['cnpnr'];
        $this->retUrl = Yii::$app->request->hostInfo.'/cnpnr';
        $this->bgRetUrl = Yii::$app->request->hostInfo . '/cnpnr/backend';
        $this->host = $this->apiInfo['host'];
        $this->merId = $this->apiInfo['merid'];
        $this->params = [
            self::PARAM_VERSION => self::VERSION10,
            self::PARAM_MERCUSTID => $this->apiInfo['mercustid'],
        ];
        $this->link = null;
        $this->queryString = null;
        $this->maps = [
            self::PARAM_VERSION,self::PARAM_CMDID,self::PARAM_MERCUSTID,
            self::PARAM_USRCUSTID,self::PARAM_ORDID,self::PARAM_ORDDATE,
            self::PARAM_GATEBUSIID,self::PARAM_OPENBANKID,self::PARAM_DCFLAG,
            self::PARAM_TRANSAMT,self::PARAM_RETURL,self::PARAM_BGRETURL,
            self::PARAM_MERPRIV,self::PARAM_CHKVALUE,self::RESP_TRXID,self::RESP_ORDID, self::RESP_RESPEXT,
            self::PARAM_USRID, self::PARAM_USRNAME, self::PARAM_IDTYPE, self::PARAM_IDNO,
            self::PARAM_USRMP, self::PARAM_USREMAIL, self::PARAM_CHARSET, self::PARAM_MAXTENDERRATE,
            self::PARAM_BORROWERDETAILS, self::PARAM_ISFREEZE, self::PARAM_FREEZEORDID, self::PARAM_FREEZETRXID,
            self::PARAM_REQEXT,
            self::PARAM_SELLCUSTID,
            self::PARAM_BIDDETAILS,
            self::PARAM_DIVACCTID,
            self::PARAM_DIVAMT,
            self::PARAM_CREDITAMT,
            self::PARAM_CREDITDEALAMT,
            self::PARAM_FEE,
            self::PARAM_DIVDETAILS,
            self::PARAM_ISUNFREEZE,
            self::PARAM_UNFREEZEORDID,
            self::PARAM_BEGINDATE,
            self::PARAM_ENDDATE,
            self::PARAM_PAGENUM,
            self::PARAM_PAGESIZE,
        ];
        $this->signOrder = [
            self::CMD_QUERYBALANCEBG => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRCUSTID,
            ],
            self::CMD_TENDER => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,3=>self::PARAM_ORDID,
                4=>self::PARAM_ORDDATE, 5=>self::PARAM_TRANSAMT, 6=>self::PARAM_USRCUSTID, 7=>self::PARAM_MAXTENDERRATE,
                8=>self::PARAM_BORROWERDETAILS, 9=>self::PARAM_ISFREEZE, 10=>self::PARAM_FREEZEORDID, 11=>self::PARAM_RETURL,
                12=>self::PARAM_BGRETURL, 13=>self::PARAM_MERPRIV,
                14=>self::PARAM_REQEXT
            ],
            self::CMD_OPEN => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_BGRETURL, 4=>self::PARAM_RETURL, 5=>self::PARAM_USRID,
                6=>self::PARAM_USRNAME, 7=>self::PARAM_IDTYPE, 8=>self::PARAM_IDNO,
                9=>self::PARAM_USRMP, 10=>self::PARAM_USREMAIL, 11=>self::PARAM_MERPRIV,
            ],
            self::CMD_DEPOSIT => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRCUSTID,4=>self::PARAM_ORDID,5=>self::PARAM_ORDDATE,
                6=>self::PARAM_GATEBUSIID,7=>self::PARAM_OPENBANKID,8=>self::PARAM_DCFLAG,
                9=>self::PARAM_TRANSAMT,10=>self::PARAM_RETURL,11=>self::PARAM_BGRETURL,
                12=>self::PARAM_MERPRIV
            ],

            self::CMD_UNFREEZE => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID,4=>self::PARAM_ORDDATE,5=>self::RESP_TRXID,
                6=>self::PARAM_RETURL,7=>self::PARAM_BGRETURL,8=>self::PARAM_MERPRIV
            ],

            self::CMD_CREDITASSIGN => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_SELLCUSTID, 4=>self::PARAM_CREDITAMT, 5=>self::PARAM_CREDITDEALAMT,
                6=>self::PARAM_BIDDETAILS, 7=>self::PARAM_FEE, 8=>self::PARAM_DIVDETAILS,
                9=>self::PARAM_BUYCUSTID, 10=>self::PARAM_ORDID, 11=>self::PARAM_ORDDATE,
                12=>self::PARAM_RETURL, 13=>self::PARAM_BGRETURL, 14=>self::PARAM_MERPRIV,
                15=>self::PARAM_REQEXT
            ],

            self::CMD_TENDERCANCEL => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID, 4=>self::PARAM_ORDDATE, 5=>self::PARAM_TRANSAMT,
                6=>self::PARAM_USRCUSTID, 7=>self::PARAM_ISUNFREEZE, 8=>self::PARAM_UNFREEZEORDID,
                9=>self::PARAM_FREEZETRXID,10=>self::PARAM_RETURL, 11=>self::PARAM_BGRETURL,
                12=>self::PARAM_MERPRIV,13=>self::PARAM_REQEXT
            ],

            self::CMD_CREDITASSIGNRECONCILIATION => [
                0=>self::PARAM_VERSION, 1=>self::PARAM_CMDID, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID,4=>self::PARAM_BEGINDATE, 5=>self::PARAM_ENDDATE,
                6=>self::PARAM_SELLCUSTID, 7=>self::PARAM_BUYCUSTID, 8=>self::PARAM_PAGENUM,
                9=>self::PARAM_PAGESIZE, 10=>self::PARAM_REQEXT,
            ],
        ];
        $this->vSignOrder = [
            self::CMD_QUERYBALANCEBG => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRCUSTID,4=>self::PARAM_AVLBAL,5=>self::PARAM_ACCTBAL,
                6=>self::PARAM_FRZBAL,
            ],
            self::CMD_TENDER => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID, 4=>self::PARAM_ORDDATE, 5=>self::PARAM_TRANSAMT,
                6=>self::PARAM_USRCUSTID, 7=>self::RESP_TRXID,8=>self::PARAM_ISFREEZE,
                9=>self::PARAM_FREEZEORDID, 10=>self::PARAM_FREEZETRXID, 11=>self::PARAM_RETURL,
                12=>self::PARAM_BGRETURL, 13=>self::PARAM_MERPRIV,
                14=>self::RESP_RESPEXT
            ],
            self::CMD_OPEN => [
                0=>self::PARAM_CMDID,1=>self::RESP_CODE,2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRID, 4=>self::PARAM_USRCUSTID, 5=>self::PARAM_BGRETURL,
                6=>self::RESP_TRXID, 7=>self::PARAM_RETURL, 8=>self::PARAM_MERPRIV
            ],
            self::CMD_DEPOSIT => [
                0=>self::PARAM_CMDID,1=>self::RESP_CODE,2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_USRCUSTID,4=>self::PARAM_ORDID,5=>self::PARAM_ORDDATE,
                6=>self::PARAM_TRANSAMT,7=>self::RESP_TRXID,8=>self::PARAM_RETURL,
                9=>self::PARAM_BGRETURL,10=>self::PARAM_MERPRIV
            ],

            self::CMD_UNFREEZE => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID, 4=>self::PARAM_ORDDATE, 5=>self::RESP_TRXID,
                6=>self::PARAM_RETURL, 7=>self::PARAM_BGRETURL, 8=>self::PARAM_MERPRIV
            ],

            self::CMD_CREDITASSIGN => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_SELLCUSTID, 4=>self::PARAM_CREDITAMT,5=>self::PARAM_CREDITDEALAMT,
                6=>self::PARAM_FEE, 7=>self::PARAM_BUYCUSTID, 8=>self::PARAM_ORDID,
                9=>self::PARAM_ORDDATE, 10=>self::PARAM_RETURL, 11=>self::PARAM_BGRETURL,
                12=>self::PARAM_MERPRIV, 13=>self::RESP_RESPEXT
            ],

            self::CMD_TENDERCANCEL => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID, 4=>self::PARAM_ORDDATE, 5=>self::PARAM_TRANSAMT,
                6=>self::PARAM_USRCUSTID, 7=>self::PARAM_ISUNFREEZE, 8=>self::PARAM_UNFREEZEORDID,
                9=>self::PARAM_FREEZETRXID, 10=>self::PARAM_RETURL, 11=>self::PARAM_BGRETURL,
                12=>self::PARAM_MERPRIV, 13=>self::RESP_RESPEXT
            ],

            self::CMD_CREDITASSIGNRECONCILIATION => [
                0=>self::PARAM_CMDID, 1=>self::RESP_CODE, 2=>self::PARAM_MERCUSTID,
                3=>self::PARAM_ORDID, 4=>self::PARAM_BEGINDATE, 5=>self::PARAM_ENDDATE,
                6=>self::PARAM_SELLCUSTID, 7=>self::PARAM_BUYCUSTID,8=>self::PARAM_PAGENUM,
                9=>self::PARAM_PAGESIZE, 10=>self::RESP_TOTALITEMS, 11=>self::RESP_RESPEXT,
            ],
        ];
        $this->response = null;
        $this->showId = self::PARAM_ORDID;
    }

    public static  function queryBalanceBg($usrCustId)
    {
        $cnpnr = new static();
        $cnpnr->params[self::PARAM_CMDID] = self::CMD_QUERYBALANCEBG;
        $cnpnr->params[self::PARAM_USRCUSTID] = $usrCustId;
        $ch = curl_init($cnpnr->getLink());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = $data ? json_decode($data, true) : null;
        return $data;
    }

    public function tenderCancel($usrCustId,$orderId, $orderDate, $transAmt, $isUnFreeze = false, $freezeTrxId = null, $unFreezeOrdId = null)
    {
        $this->params[self::PARAM_VERSION] = self::VERSION20;
        $this->params[self::PARAM_CMDID] = self::CMD_TENDERCANCEL;
        $this->params[self::PARAM_USRCUSTID] = $usrCustId;
        $this->params[self::PARAM_ORDID] = $orderId;
        $this->params[self::PARAM_ORDDATE] = $orderDate;
        if ($isUnFreeze)
        {
            $this->params[self::PARAM_ISUNFREEZE] = 'Y';
            $this->params[self::PARAM_UNFREEZEORDID] = $unFreezeOrdId ? $unFreezeOrdId : $this->_createSerial();
            $this->params[self::PARAM_FREEZETRXID] = $freezeTrxId;
        }
        else $this->params[self::PARAM_ISUNFREEZE] = 'N';
        $this->params[self::PARAM_TRANSAMT] = number_format($transAmt, 2, '.', '');
        $this->params[self::PARAM_RETURL] = $this->retUrl;
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        return $this;
    }

    public function creditAssign($sellCustId, $buyCustId, $creditPrincipalAmt, $amount, $feeAmt)
    {
        $amount = number_format($amount, 2, '.', '');
        $feeAmt = number_format($feeAmt, 2, '.', '');
        $this->params[self::PARAM_CMDID] = self::CMD_CREDITASSIGN;
        $this->params[self::PARAM_RETURL] = Yii::$app->params['api']['cnpnr']['baseSiteHost'].'/HuifuPay/CreditReturnBack';
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        $this->params[self::PARAM_SELLCUSTID] = $sellCustId; //债权转让转让人客户号
        $this->params[self::PARAM_BUYCUSTID] = $buyCustId;
        $this->params[self::PARAM_CREDITAMT] = $creditPrincipalAmt;
        $this->params[self::PARAM_CREDITDEALAMT] = $amount;
        $this->params[self::PARAM_FEE] = $feeAmt;
        $this->params[self::PARAM_DIVACCTID] = 'MDT000001';
        $this->params[self::PARAM_DIVAMT] = $feeAmt;
        $this->params[self::PARAM_DIVDETAILS] = Json::encode([['DivAcctId'=>'MDT000001', 'DivAmt'=>$feeAmt],]);
        return $this;
    }

    public function creditAssignReconciliation($beginDate=null, $endDate=null, $pageNum = 1, $pageSize=999)
    {
        $this->params[self::PARAM_CMDID] = self::CMD_CREDITASSIGNRECONCILIATION;
        $this->params[self::PARAM_ORDID] = $this->_createSerial();
        $this->params[self::PARAM_BEGINDATE] = $beginDate;
        $this->params[self::PARAM_ENDDATE] = $endDate;
        $this->params[self::PARAM_PAGENUM] = $pageNum;
        $this->params[self::PARAM_PAGESIZE] = $pageSize;
        return $this;
    }

    public function open()
    {
        $this->params[self::PARAM_CMDID] = self::CMD_OPEN;
        $this->params[self::PARAM_RETURL] = $this->retUrl;
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        $this->showId = self::RESP_TRXID;
        return $this;
    }

    public function deposit($cnpnr_account_id)
    {
        $this->params[self::PARAM_CMDID] = self::CMD_DEPOSIT;
        $this->params[self::PARAM_USRCUSTID] = $cnpnr_account_id;
        $this->params[self::PARAM_DCFLAG] = 'D';
        $this->params[self::PARAM_RETURL] = $this->retUrl;
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        $this->showId = self::RESP_TRXID;
        return $this;
    }

    public function tender($cnpnr_account_id)
    {
        $this->params[self::PARAM_VERSION] = self::VERSION20;
        $this->params[self::PARAM_CMDID] = self::CMD_TENDER;
        $this->params[self::PARAM_USRCUSTID] = $cnpnr_account_id;
        $this->params[self::PARAM_RETURL] = $this->retUrl;
        $this->params[self::PARAM_BGRETURL] = $this->bgRetUrl;
        $this->showId = self::RESP_ORDID;
        return $this;
    }


    public function setResponse(array $responseArr, $isBackend = false)
    {
        $cmdId = isset($responseArr[self::PARAM_CMDID]) && $responseArr[self::PARAM_CMDID] ? $responseArr[self::PARAM_CMDID] : null;
        $chkValue = isset($responseArr[self::PARAM_CHKVALUE]) && $responseArr[self::PARAM_CHKVALUE] ? $responseArr[self::PARAM_CHKVALUE] : null;
        if ($cmdId && $chkValue)
        {
            $vSignFieldsOrd = isset($this->vSignOrder[$cmdId]) ? $this->vSignOrder[$cmdId] : null;
            if ($vSignFieldsOrd)
            {
                //Check Sign
                $vSignMessage = '';
                for($i=0;$i<count($vSignFieldsOrd);$i++)
                {
                    $field = $vSignFieldsOrd[$i];
                    $value = isset($responseArr[$field]) && $responseArr[$field] ? trim(urldecode($responseArr[$field])) : null;
                    if ($value) $vSignMessage .= $value;
                }
                if ($this->_vSign($vSignMessage, $chkValue))
                {
                    foreach($responseArr as $k => $v)
                    {
                        if ($k == self::PARAM_MERPRIV)
                        {
                            $v = json_decode(base64_decode(urldecode($v)), true);
                            if ($isBackend) $v['return'] = 'backend';
                        }
                        $this->response[$k] = $v;
                    }
                    Log::cnpnr($this->response);
                }
                else exit('验签失败');
            }
        }
        return $this;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getLink()
    {
        if (!$this->link)
        {
            $this->sign();
            foreach($this->params as $fieldName => $fieldValue)
            {
                $fieldValue = is_string($fieldValue) ? trim($fieldValue) : $fieldValue;
                if ($fieldName == self::PARAM_RETURL || $fieldName == self::PARAM_BGRETURL) $fieldValue = urlencode($fieldValue);
                if ($fieldValue)
                    $this->queryString .= $this->queryString ? '&'.$fieldName.'='.$fieldValue : '?'.$fieldName.'='.$fieldValue;
            }
            $this->link = $this->host.($this->queryString ? $this->queryString : '');
        }
        return $this->link;
    }

    public function __get($name)
    {
        $value = null;
        $params = [];
        foreach($this->params as $k=>$v)
            $params[strtolower($k)] = $v;
        if (isset($params[strtolower($name)]) && $params[strtolower($name)]) $value = $params[strtolower($name)];
        else $value = $this->$name;
        return $value;
    }

    public function __set($name, $value)
    {
        $name = strtolower($name);
        foreach($this->maps as $field)
        {
            if ($name == strtolower($field))
            {
                if ($name == strtolower(self::PARAM_TRANSAMT)) $value = number_format($value, 2, '.', '');
                $this->params[$field] = $value;
            }
        }
        return $this;
    }

    private function sign()
    {
        if (isset($this->params[self::PARAM_CMDID]))
        {
            $signMessage = null;
            $chkValue = null;
            $signMessage = $this->_getSignMessageStr($this->signOrder[$this->params[self::PARAM_CMDID]]);
            if ($signMessage) $chkValue = $this->_sign($signMessage);
            if ($chkValue) $this->params[self::PARAM_CHKVALUE] = $chkValue;
            return $this;
        }
        return false;
    }

    private function _sign($msg)
    {
        $sign = null;
        $fp = fsockopen($this->apiInfo['sign']['host'], $this->apiInfo['sign']['port'], $errno, $errstr, 10);
        if ($fp)
        {
            $len = sprintf("%04s", strlen($msg));
            $out = 'S'.$this->merId.$len.$msg;
            $out = sprintf("%04s", strlen($out)).$out;
            fputs($fp, $out);
            while(!feof($fp)) $sign .= fgets($fp, 128);
            fclose($fp);
            $sign = substr($sign, -256);
        }
        return $sign;
    }

    private function _vSign($messageBody, $chkValue)
    {
        $result = false;
        $len = sprintf("%04s", strlen($messageBody));
        $out = 'V'.$this->merId.$len.$messageBody.$chkValue;
        $out = sprintf("%04s", strlen($out)).$out;
        $fp = fsockopen($this->apiInfo['sign']['host'], $this->apiInfo['sign']['port'], $errno, $errstr, 10);
        if ($fp)
        {
            fputs($fp, $out);
            $in = '';
            while(!feof($fp)) $in .= fgets($fp, 128);
            fclose($fp);
            $result = substr($in, -4) == '0000';
        }
        return $result;
    }

    private function _getSignMessageStr(array $signParamOrd)
    {
        $message = '';
        for($i=0; $i<count($signParamOrd);$i++)
        {
            $name = $signParamOrd[$i];
            $val = isset($this->params[$name]) ? trim($this->params[$name]) : null;
            if ($val)
            {
                if ($name == self::PARAM_MERPRIV)
                {
                    $val = json_decode($val, true);
                    $val[self::PARAM_PRIVATE_SHOWID] = $this->showId;
                    $val = base64_encode(json_encode($val));
                }
                $message .= $val;
            }
            $this->params[$name] = $val;
        }
        return $message;
    }

    private function _createSerial()
    {
        $orderNumber = false;
        if (preg_match('/.*\.+?(\d+)?\s*(\d+)$/', microtime(), $microTimeArr))
        {
            $orderNumber = date('YmdHis', $microTimeArr[2]).substr($microTimeArr[1], 0, 6);
        }
        return $orderNumber;
    }
}