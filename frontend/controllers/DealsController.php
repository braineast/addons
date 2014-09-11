<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/20/2014
 * Time: 1:08 AM
 */

namespace frontend\controllers;
use common\models\db\CnpnrAccount;
use common\models\db\RepaymentOrder;
use common\models\db\User;
use frontend\models\api\ChinaPNR;
use frontend\models\Controller;
use frontend\models\Credit;
use common\models\db\Deal;
use common\models\db\DealOrder;
use frontend\models\CreditOrder;
use Yii;
use yii\helpers\Json;
use yii\web\Response;

class DealsController extends Controller {
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * @param $id 投标订单id
     */
    public function actionCreate($orderNumber, $transferShares=1, $discountRate=0)
    {
        $respCode = -1;
        $data = null;
        if (/*DealOrder::canBeTransfer($orderNumber) && */$creditData = DealOrder::getCreditDetail($orderNumber, $discountRate))
        {
            $model = new Credit();
            $model->transfer_shares = $transferShares;
            foreach($creditData as $k => $v) if ($model->hasAttribute($k)) $model->$k = $v;
            if ($model->validate())
            {
                if ($model->save())
                {
                    $respCode = 0;
                    $data = $model->attributes;
                }
            }
            else $data = $model->errors;
        }
        if (Yii::$app->request->isAjax)
        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return ['respCode'=>$respCode, 'data'=>$data];
        }
        var_dump(['respCode'=>$respCode, 'data'=>$data]);
    }

    public function actionCredit($orderNumber)
    {
        $respCode = -1; //未知错误
        $creditData = null;
        $ret = null;
        $credit = Credit::find()->where('order_id=:orderId', [':orderId'=>$orderNumber])->one();
        if ($credit)
        {
            $creditData = $credit->attributes;
        }
//        elseif(DealOrder::canBeTransfer($orderNumber))
//        {
            $creditData = DealOrder::getCreditDetail($orderNumber);
//        }
//        else $respCode = -2; //该订单不可被转让
        if ($creditData) $respCode = 0;
        $ret = [
            'respCode' => $respCode,
            'data' => $creditData
        ];
//        if (Yii::$app->request->isAjax)
//        {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return $ret;
//        }
    }

    public function actionTendercancel()
    {
        $cnpnr = new ChinaPNR();
        $cnpnr->tenderCancel('6000060000301070', '20140911041058316700', '20140911', 203.50);
        $url = $cnpnr->getLink();
        exit($url);
    }

    /**
     * @param $creditId
     * @param $shares
     * @param $userId
     */
    public function actionBuy($creditId, $shares, $userId)
    {
        $respCode = -1;
        if ($credit = Credit::findOne($creditId))
        {
            if ($credit->in_stock_shares >= $shares)
            {
                $creditPrincipalAmt = number_format($credit->unit_principal_amt * $shares, 2, '.', '');
                $bidOrder = DealOrder::find()->where('deal_number=:orderId', [':orderId'=>$credit->order_id])->one();
                $deal = Deal::find()->where('deal_id=:dealId', [':dealId'=>$bidOrder->deal_id])->one();
                $order = new CreditOrder();
                $order->credit_id = $creditId;
                $order->user_id = $userId;
                $order->shares = $shares;
                $order->amount = $credit->unit_price * $shares;
                $order->fee = $credit->unit_fee_amt * $shares;
                if ($order->validate())
                {
                    if ($order->save())
                    {
                        $cnpnr = new ChinaPNR();
                        $cnpnr->creditAssign(
                            CnpnrAccount::find()->where('uid=:userId', [':userId'=>$bidOrder->uid])->one()->UsrCustId,
                            CnpnrAccount::find()->where('uid=:userId', [':userId'=>$userId])->one()->UsrCustId,
                            $creditPrincipalAmt,
                            $order->amount,
                            $order->fee
                        );
                        $bidDetails = [
                            'BidDetails' => [
                                [
                                    'BidOrdId'=>$bidOrder->deal_number,
                                    'BidOrdDate'=>$bidOrder->OrdDate,
                                    'BidCreditAmt'=> $creditPrincipalAmt, //从原投标订单中转出的本金
                                    'BorrowerDetails' => [
                                        [
                                            'BorrowerCustId' => CnpnrAccount::find()->where('uid=:userId', [':userId'=>$deal->uid])->one()->UsrCustId,
                                            'BorrowerCreditAmt' => $creditPrincipalAmt, //从原投标订单借款人转出的已放款金额
                                            'PrinAmt' => number_format(0, 2, '.', ''), //借款人还款金额中所占的本金部分
                                        ],
                                    ],
                                ],
                            ],
                        ];
                        $cnpnr->BidDetails = Json::encode($bidDetails);
                        $cnpnr->ordId = $order->serial;
                        $cnpnr->ordDate = substr($order->serial, 0, 8);
                        $this->redirect($cnpnr->getLink());
                    }
                }
            }
            else $respCode = -2; //库存不足
        }
    }

    public function actionOpen()
    {
        $cnpnr = new ChinaPNR();
        $cnpnr->creditAssignReconciliation('20140901', '20140911');
        $ch = curl_init($cnpnr->getLink());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        foreach($result as $k => $v) $result[$k] = is_string($v) ? urldecode($v) : $v;
        var_dump($result);
    }
}
