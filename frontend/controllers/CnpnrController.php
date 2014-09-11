<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 7/29/2014
 * Time: 4:42 PM
 */

namespace frontend\controllers;

use common\models\db\Account;
use common\models\db\CnpnrAccount;
use common\models\db\Deal;
use common\models\db\RepaymentOrder;
use frontend\models\Controller;
use frontend\models\api\ChinaPNR;
use frontend\models\Credit;
use frontend\models\CreditOrder;
use frontend\models\TenderForm;

class CnpnrController extends Controller
{
    public $enableCsrfValidation = false;
    private $response;

    public function actionIndex($backend = false)
    {
        header('Content-Type: text/html; charset=UTF-8');
        $backend = $backend ? true : $backend;
        if (isset($_POST) && $_POST)
        {
            $cnpnr = new ChinaPNR();
            $cnpnr->setResponse($_POST, $backend);
            if ($response = $cnpnr->getResponse())
            {
                $this->response = $response;
                $result = $this->_responser();
                if ($backend) exit('RECV_ORD_ID_'.$response[$response[ChinaPNR::PARAM_MERPRIV]['showId']]);
            }
        }
    }

    public function actionBackend()
    {
        return $this->actionIndex(true);
    }

    protected function CreditAssign()
    {
        if ($this->response[ChinaPNR::RESP_CODE] == '000')
        {
            if ($order = CreditOrder::find()->where('status='.CreditOrder::STATUS_UNPAID.' and serial=:orderId', [':orderId'=>$this->response[ChinaPNR::PARAM_ORDID]])->one())
            {
                $credit = Credit::find()->where('id=:creditId', [':creditId'=>$order->credit_id])->one();
                if ($order->shares > $credit->in_stock_shares)
                {
                    exit('债权超卖了，请撤单！');//@todo how to cancel the creditAssign order?
                }
                else
                {
                    //Update user data that belonging to account balance
                    $acctSeek = [
                        $credit->user_id,
                        $order->user_id,
                        Deal::find()->where('deal_id=:dealId', [':dealId'=>$credit->deal_id])->one()->uid,
                    ];
                    foreach($acctSeek as $userId)
                    {
                        $acct = Account::find()->where('uid=:userId', [':userId'=>$userId])->one();
                        $acctData = ChinaPNR::queryBalanceBg(CnpnrAccount::find()->where('uid=:userId', [':userId'=>$userId])->one()->UsrCustId);
                        if ($acctData && $acctData['RespCode'] == '000')
                        {
                            $acct->AcctBal = number_format(preg_replace('/,/', '', $acctData['AcctBal']), 2, '.', '');
                            $acct->AvlBal = number_format(preg_replace('/,/', '', $acctData['AvlBal']), 2, '.', '');
                            $acct->FrzBal = number_format(preg_replace('/,/', '', $acctData['FrzBal']), 2, '.', '');
                            $acct->save();
                        }
                    }
                    $order->status = CreditOrder::STATUS_PAID;
                    if ($order->validate())
                    {
                        if ($order->save())
                        {
                            if ($order->status == CreditOrder::STATUS_PAID)
                            {
                                $repaymentOrders = RepaymentOrder::find()->where('deal_number=:dealOrderId and status=1', [':dealOrderId'=>$credit->order_id])->orderBy('deal_qishu')->all();
                                $remainedPrincipalAmt = 0.00;
                                foreach ($repaymentOrders as $val)
                                {
                                    if ($val->status == 1)
                                    {
                                        $remainedPrincipalAmt += $val->benjin;
//                                        $remainedInterestAmt += $repaymentOrder->lixi;
//                                        if ($nextRepaymentDate < $repaymentOrder->refund_time)
//                                            $nextRepaymentDate = $repaymentOrder->refund_time;
//                                        if (time() < $repaymentOrder->refund_time)
//                                        {
//                                            $currentTermId = min($currentTermId, $repaymentOrder->deal_qishu);
//                                            $currentRepaymentOrder = $repaymentOrder;
//                                        }
                                    }
                                    /**
                                    if ($repaymentOrder->status == 2)
                                    {
                                        $retrievedPrincipalAmt += $repaymentOrder->benjin;
                                        $retrievedInterestAmt += $repaymentOrder->lixi;
                                        if ($lastRepaymentDate < $repaymentOrder->refund_time)
                                            $lastRepaymentDate = $repaymentOrder->refund_time;
                                    }
                                     * */
                                }
                                $creditCount = intval($remainedPrincipalAmt / 100) ? intval($remainedPrincipalAmt / 100) : ($remainedPrincipalAmt ? 1 : 0);
                                foreach($repaymentOrders as $repaymentOrderItem)
                                {
                                    $repaymentOrder = new RepaymentOrder();
                                    $repaymentOrder->uid = $order->user_id;
                                    $repaymentOrder->deal_id = $repaymentOrderItem->deal_id;
                                    $repaymentOrder->deal_number = $order->serial;
                                    $repaymentOrder->deal_time = date('Ymd', $order->created_at);
                                    $repaymentOrder->deal_qishu = $repaymentOrderItem->deal_qishu;
                                    $repaymentOrder->Buid = $repaymentOrderItem->Buid;
                                    $repaymentOrder->benjin = $order->principal_amt * $order->shares;
                                    $repaymentOrder->lixi = $repaymentOrderItem->lixi / $creditCount * $order->shares;
                                    $repaymentOrder->benxi = $repaymentOrder->benjin + $repaymentOrder->lixi;
                                    $repaymentOrder->refund_time = $repaymentOrderItem->refund_time;
                                    $repaymentOrder->log = '';
                                    $repaymentOrder->save();
                                    $originalRepaymentOrder = new RepaymentOrder();
                                    $originalRepaymentOrder->uid = $repaymentOrderItem->uid;
                                    $originalRepaymentOrder->deal_id = $repaymentOrderItem->deal_id;
                                    $originalRepaymentOrder->deal_number = $repaymentOrderItem->deal_number;
                                    $originalRepaymentOrder->deal_time = $repaymentOrderItem->deal_time;
                                    $originalRepaymentOrder->deal_qishu = $repaymentOrderItem->deal_qishu;
                                    $originalRepaymentOrder->Buid = $repaymentOrderItem->Buid;
                                    $originalRepaymentOrder->benjin = $repaymentOrderItem->benjin - $repaymentOrder->benjin;
                                    $originalRepaymentOrder->lixi = $repaymentOrderItem->lixi - $repaymentOrder->lixi;
                                    $originalRepaymentOrder->benxi = $originalRepaymentOrder->benjin + $originalRepaymentOrder->lixi;
                                    $originalRepaymentOrder->refund_time = $repaymentOrderItem->refund_time;
                                    $originalRepaymentOrder->log = $repaymentOrderItem->log;
                                    if ($originalRepaymentOrder->validate())
                                    {
                                        if ($originalRepaymentOrder->save())
                                        {
                                            $repaymentOrderItem->status = 3;
                                            $repaymentOrderItem->save();
                                        }
                                    }
                                    else var_dump($originalRepaymentOrder->errors);
                                }
                            }
                        }
                        else exit('抱歉，订单状态未能保存！');
                    }
                    else var_dump($order->errors);
                }
            }
            else exit("矮油，订单无法找到哦，可能该单不存在，更可能是该单已经被处理过啦！");
        }
        else exit("汇付说处理失败哦！".urldecode($this->response[ChinaPNR::RESP_DESC]));
    }

    private function _responser()
    {
        $method = $this->response[ChinaPNR::PARAM_CMDID];
        if (method_exists($this, $method))
            return $this->$method();
        elseif (method_exists($this, strtolower($method)))
        {
            $method = strtolower($method);
            return $this->$method();
        }
        return $this;
    }

    private function _getUser()
    {
        var_dump(\Yii::$app->getUser());
    }

}