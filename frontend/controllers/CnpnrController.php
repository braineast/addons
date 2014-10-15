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
                if ( $credit->in_stock_shares >= $order->shares)
                {
                    $order->status = CreditOrder::STATUS_PAID;
                    if ($order->validate())
                    {
                        if ($order->save())
                        {
                            if ($order->status == CreditOrder::STATUS_PAID)
                            {
                                $repaymentOrders = RepaymentOrder::find()->where('deal_number=:dealOrderId and status=1', [':dealOrderId'=>$credit->order_id])->orderBy('deal_qishu')->all();
                                $remainedPrincipalAmt = 0.00;
                                $remainedInterestAmt = 0.00;
                                foreach ($repaymentOrders as $val)
                                {
                                    if ($val->status == 1)
                                    {
                                        $remainedPrincipalAmt += $val->benjin;
                                        $remainedInterestAmt += $val->lixi;
                                    }
                                }
                                $creditCount = intval($remainedPrincipalAmt / 100) ? intval($remainedPrincipalAmt / 100) : ($remainedPrincipalAmt ? 1 : 0);
                                foreach($repaymentOrders as $repaymentOrderItem)
                                {
                                    $currentOrderInterestAmt = 0.00;
                                    $OriginalOrderInterestAmt = 0.00;
                                    $totalInterestAmt = $repaymentOrderItem->lixi;
                                    if ($totalInterestAmt)
                                    {
                                        $currentOrderInterestAmt = round($totalInterestAmt / $creditCount * $order->shares, 4);
                                        $OriginalOrderInterestAmt = round($totalInterestAmt - $currentOrderInterestAmt, 4);
                                        if ($splitInterest = self::balanceSplit($totalInterestAmt, [$currentOrderInterestAmt, $OriginalOrderInterestAmt]))
                                        {
                                            list($currentOrderInterestAmt, $OriginalOrderInterestAmt) = $splitInterest;
                                        }
                                    }
                                    $repaymentOrder = new RepaymentOrder();
                                    $repaymentOrder->uid = $order->user_id;
                                    $repaymentOrder->deal_id = $repaymentOrderItem->deal_id;
                                    $repaymentOrder->deal_number = $order->serial;
                                    $repaymentOrder->deal_time = date('Ymd', $order->created_at);
                                    $repaymentOrder->deal_qishu = $repaymentOrderItem->deal_qishu;
                                    $repaymentOrder->Buid = $repaymentOrderItem->Buid;
                                    $repaymentOrder->benjin = $order->principal_amt * $order->shares;
                                    $repaymentOrder->lixi = $currentOrderInterestAmt;
                                    $repaymentOrder->benxi = $repaymentOrder->benjin + $repaymentOrder->lixi;
                                    $repaymentOrder->refund_time = $repaymentOrderItem->refund_time;
                                    $repaymentOrder->log = '';
                                    if ($repaymentOrder->save())
                                    {
                                        $originalOrderPrincipalAmt = $repaymentOrderItem->benjin - $repaymentOrder->benjin;
                                        if ($originalOrderPrincipalAmt > 0 || $OriginalOrderInterestAmt > 0)
                                        {
                                            $originalRepaymentOrder = new RepaymentOrder();
                                            $originalRepaymentOrder->uid = $repaymentOrderItem->uid;
                                            $originalRepaymentOrder->deal_id = $repaymentOrderItem->deal_id;
                                            $originalRepaymentOrder->deal_number = $repaymentOrderItem->deal_number;
                                            $originalRepaymentOrder->deal_time = $repaymentOrderItem->deal_time;
                                            $originalRepaymentOrder->deal_qishu = $repaymentOrderItem->deal_qishu;
                                            $originalRepaymentOrder->Buid = $repaymentOrderItem->Buid;
                                            $originalRepaymentOrder->benjin = $originalOrderPrincipalAmt;
                                            $originalRepaymentOrder->lixi = $OriginalOrderInterestAmt;
                                            $originalRepaymentOrder->benxi = $originalRepaymentOrder->benjin + $originalRepaymentOrder->lixi;
                                            $originalRepaymentOrder->refund_time = $repaymentOrderItem->refund_time;
                                            $originalRepaymentOrder->log = $repaymentOrderItem->log;
                                            if ($originalRepaymentOrder->validate()) $originalRepaymentOrder->save();
                                        }
                                        $repaymentOrderItem->status = 3;
                                        $repaymentOrderItem->save();
                                    }
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

    public static function balanceSplit($totalAmt, $actorValues = [])
    {
        $result = [];
        if ($totalAmt && $actorValues)
        {
            $totalAmt = round($totalAmt, 2);
            $tmpValues = [];
            foreach($actorValues as $k => $v)
            {
                $tmpValues[$k] = round($v, 2);
            }
            $tmpAmt = number_format(array_sum($tmpValues), 2, '.', '');
            $diff = bcsub($totalAmt, $tmpAmt, 2);
            if ($diff)
            {
                if (abs($diff) * 100 >= count($actorValues))
                    return false;
                $tmpValues = [];
                foreach($actorValues as $k => $v)
                {
                    $v = number_format($v, 4, '.', '');
                    $v = substr($v, 0, strpos($v, '.') + 4);
                    $lastDigit = substr($v, -1);
                    $tmpValues[$lastDigit][$k] = $v;
                }
                if ($diff < 0)
                {
                    $diff = abs($diff) * 100;
                    ksort($tmpValues);
                    while($diff)
                    {
                        foreach($tmpValues as $k => $v)
                        {
                            if ($k > 4)
                            {
                                foreach($v as $key => $value)
                                {
                                    $tmpValues[$k][$key] = substr($value, 0, strpos($value, '.') + 3);
                                    $diff--;
                                    if (!$diff) break;
                                }
                            }
                            if (!$diff) break;
                        }
                    }
                }
                elseif ($diff > 0)
                {
                    $diff = abs($diff) * 100;
                    krsort($tmpValues);
                    while($diff)
                    {
                        foreach($tmpValues as $k => $v)
                        {
                            if ($k < 5)
                            {
                                foreach($v as $key => $value)
                                {
                                    $tmpValues[$k][$key] = number_format(substr($value, 0, strpos($value, '.') + 3).'5',3,'.', '');
                                    $diff--;
                                    if (!$diff) break;
                                }
                            }
                            if (!$diff) break;
                        }
                    }
                }
                foreach($tmpValues as $v)
                {
                    foreach($v as $key => $value)
                    {
                        $result[$key] = number_format($value, 2, '.', '');
                    }
                }
                if (array_sum($result) != $totalAmt) $result = false;
            }
        }
        return $result;
    }

}