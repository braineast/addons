<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/23/2014
 * Time: 4:58 PM
 */

namespace common\models\db;
use frontend\models\Credit;
use yii\db\ActiveRecord;

class DealOrder extends ActiveRecord {

    const DEAL_PERIOD_TYPE_DAY = 'D';
    const DEAL_PERIOD_TYPE_MONTH = 'M';

    public static function tableName()
    {
        return '{{%deal_order}}';
    }

    public static function getCreditDetail($orderId, $transferShares, $discountRate)
    {
        $data = [];
        $model = static::find()->where('status=2 and deal_number=:orderId', [':orderId'=>$orderId])->one();
        //@todo Search and Fetch order object from Credit order table, if this orderId is credit order id.
        if ($model)
        {
            $deal = Deal::findOne($model->deal_id);
            if ($deal && $deal->deal_status == 5)
            {
                $repaymentOrders = RepaymentOrder::find()->where('deal_number=:dealOrderId and status<>3', [':dealOrderId'=>$model->deal_number])->orderBy('deal_qishu')->all();
                $lastRepaymentDate = null;
                $nextRepaymentDate = null;
                $retrievedPrincipalAmt = 0.00;
                $retrievedInterestAmt = 0.00;
                $remainedPrincipalAmt = 0.00;
                $remainedInterestAmt = 0.00;
                $currentTermId = 1;
                $currentRepaymentOrder = $repaymentOrders[0];
                foreach ($repaymentOrders as $repaymentOrder)
                {
                    if ($repaymentOrder->status == 1)
                    {
                        $remainedPrincipalAmt += $repaymentOrder->benjin;
                        $remainedInterestAmt += $repaymentOrder->lixi;
                        if ($nextRepaymentDate < $repaymentOrder->refund_time)
                            $nextRepaymentDate = $repaymentOrder->refund_time;
                        if (time() < $repaymentOrder->refund_time)
                        {
                            $currentTermId = min($currentTermId, $repaymentOrder->deal_qishu);
                            $currentRepaymentOrder = $repaymentOrder;
                        }
                    }
                    if ($repaymentOrder->status == 2)
                    {
                        $retrievedPrincipalAmt += $repaymentOrder->benjin;
                        $retrievedInterestAmt += $repaymentOrder->lixi;
                        if ($lastRepaymentDate < $repaymentOrder->refund_time)
                            $lastRepaymentDate = $repaymentOrder->refund_time;
                    }
                }
                $orderEffectiveDate = max($deal->full_time, $model->order_time);
                $holdingPeriodInDay = static::loanTermCalc(date('Y-m-d', $orderEffectiveDate), null, time(), 'd', false)['period']->days;
                $creditCount = intval($remainedPrincipalAmt / 100) ? intval($remainedPrincipalAmt / 100) : ($remainedPrincipalAmt ? 1 : 0);
                //如果有债权可以转让，则继续计算
                if ($creditCount)
                {
                    /** @var integer $creditTransferShares 债权转让份数 */
                    $creditTransferShares = min($transferShares, $creditCount);

                    /**
                     * 当期利息分账，债权转让时，只有当期中尚未还款的利息与债权出让人有关系，因此只针对当期未还的利息进行分账
                     * @var integer $currentTermLength 当期还款订单涵盖了多少天
                     * @var integer $accruedInterestDays 出让人的应计利息天数，本质是上一还款日到今天的天数，不含当天（当天的收益属于债权受让人）
                     * @var float $creditUnitPrincipalAmt 每份债权的本金金额
                     * @var float $creditUnitInterestAmt 每份债权的利息金额
                     * @var float $creditUnitAccruedInterestAmt 债权出让人的每份债权当期利息收入 即，应计利息
                     */
                    if (!$lastRepaymentDate) $lastRepaymentDate = $deal->full_time;
                    $currentTermLength = static::loanTermCalc(date('Y-m-d', $lastRepaymentDate), null, $currentRepaymentOrder->refund_time, 'd', false)['period']->days;
                    $accruedInterestDays = static::loanTermCalc(date('Y-m-d', $lastRepaymentDate), null, time(), 'd', false)['period']->days;
                    $accruedInterestDays = $accruedInterestDays > 1 ? $accruedInterestDays - 1 : $accruedInterestDays;
                    $totalAccruedInterestAmt = $currentRepaymentOrder->lixi / $currentTermLength * $accruedInterestDays;
//                    $remainedInterestAmt = $remainedInterestAmt - $totalAccruedInterestAmt;
                    $creditUnitPrincipalAmt = $remainedPrincipalAmt / $creditCount;
                    $creditUnitInterestAmt = $remainedInterestAmt / $creditCount;
                    $creditUnitAccruedInterestAmt = $totalAccruedInterestAmt / $creditCount;

                    /**
                     * 债权相关价值价格等计算
                     * @var integer $creditLeavingPeriodInDay 债权剩余期限
                     * @var float $creditUnitValue 每份债权实际价值
                     * @var float $creditDiscountRate 折让率
                     * @var float $creditUnitPrice 每份债权的出让价格
                     * @var float $creditUnitActualValue 每份债权的应收本息金额
                     * @var float $creditFeeRate 债权转让的手续费率，默认是千五
                     * @var float $creditUnitFeeAmt 每份债权的转让费金额
                     * @var float $creditUnitIncomingAmt 每份债权于转让人而言的实际收入
                     *
                     */
                    $creditLeavingPeriodInDay = static::loanTermCalc(null, null, $deal->deal_end_date, 'd', false)['period']->days + 1;
                    $creditUnitValue = $creditUnitPrincipalAmt + $creditUnitAccruedInterestAmt;
                    $creditDiscountRate = $discountRate;
                    $creditUnitPrice = $creditUnitPrincipalAmt * (1 - $creditDiscountRate / 100) + $creditUnitAccruedInterestAmt;
                    $creditUnitActualValue = $creditUnitPrincipalAmt + $creditUnitInterestAmt;
                    $creditFeeRate = 0.003;
                    $creditUnitFeeAmt = $creditUnitPrincipalAmt * (1 - $creditDiscountRate / 100) * $creditFeeRate;
                    $creditUnitIncomingAmt = $creditUnitPrice - $creditUnitFeeAmt;
                }

                $data = [
                    'deal_id' => $deal->deal_id,
                    'deal_title' => $deal->title,
                    'deal_rate' => $deal->syl,
                    'deal_end_date' => date('Y-m-d', $deal->deal_end_date),
                    'bidAmt' => $model->order_money, //原始标的投资金额
                    'unitRetrievedPrincipalAmt' => $retrievedPrincipalAmt / $creditCount, //每份债权已回收本金
                    'unitRetrievedInterestAmt' => $retrievedInterestAmt / $creditCount, //每份债权已回收利息
                    'totalAmt' => $creditUnitPrice * $creditTransferShares, //转让总价格
                    'totalFeeAmt' => $creditUnitPrincipalAmt * (1 - $creditDiscountRate / 100) * $creditTransferShares * $creditFeeRate, //转让总费用（平台收取，一般千三）
                    'totalIncomingAmt'=>$creditUnitIncomingAmt * $creditTransferShares, //预计输入金额
                    'holdingPeriodInDay'=>$holdingPeriodInDay,
                    'user_id' => $model->uid,
                    'order_id' => $model->deal_number,
                    'leaving_period'=>$creditLeavingPeriodInDay,
                    'repayment_term_id' => $currentRepaymentOrder->deal_qishu,
                    'shares' => $creditCount,
                    'discount_rate'=>$creditDiscountRate,
                    'unit_principal_amt'=>$creditUnitPrincipalAmt,
                    'unit_interest_amt'=>$creditUnitInterestAmt,
                    'unit_accrued_interest_amt' => $creditUnitAccruedInterestAmt,
                    'actual_unit_value' => $creditUnitActualValue,
                    'unit_value' => $creditUnitValue,
                    'unit_price' => $creditUnitPrice,
                    'transfer_fee_rate'=>$creditFeeRate,
                    'unit_fee_amt'=>$creditUnitFeeAmt,
                    'unit_incoming_amt'=>$creditUnitIncomingAmt,
                    'accruedInterestDays' => $accruedInterestDays,
                ];
            }
            else die('订单虽然是正确的，但很显然，该订单所依附的标的已执行完毕，不可进行债权转让了！');
        }
        else die('对不起，找不到该订单号！');
        return $data;
    }

    public static function hasCredit($orderId)
    {
        return Credit::find()->where('order_id=:orderId', [':orderId'=>$orderId])->one();
    }

    public static function canBeTransfer($orderId)
    {
        $data = static::getCreditDetail($orderId, 1, 0);
        if ($data && isset($data['holdingPeriodInDay']))
        {
            if ($data['holdingPeriodInDay'] >= 20 && $data['shares']) return true;
        }
        return false;
    }

    /**
     * @param string $tenderCompletedDate The deal load full timestamp in string, sucn as 2014-08-08.
     * @param Char $periodType Whether 'D' or 'M', Day or Month
     * @param Integer $period Day number, or Month number
     * @param Integer $dueDate The deal's due date, this is timestamp in integer
     * @param Boolean or Integer (0|1) $amortized 该借款是否属于分期偿付
     */
    public static function loanTermCalc($tenderCompletedDate=null, $period=null, $dueDate=null, $periodType=self::DEAL_PERIOD_TYPE_DAY, $amortized = true)
    {
        $ret = null;
        $tz = new \DateTimeZone('Asia/Shanghai');
        try {
            if (!$period && !$dueDate) throw new \Exception('Error: The deal\'s period and due date is null.');
            $periodType = $periodType ? strtoupper($periodType) : null;
            if (!$periodType) throw new \Exception('Error: The period type not defined.');
            if ($periodType != self::DEAL_PERIOD_TYPE_DAY && $periodType != self::DEAL_PERIOD_TYPE_MONTH) throw new \Exception('Error: The period type must be \'d\' for day, or \'m\' for month.');
            if ($dueDate)
            {
                $dt = new \DateTime();
                $dt->setTimestamp($dueDate);
                $dt->setTimezone($tz);
                $dueDate=$dt;
            }
            if ($tenderCompletedDate)
            {
                $dt = new \DateTime($tenderCompletedDate, $tz);
                $tenderCompletedDate = $dt;
            }
            if ($tenderCompletedDate)
            {
                if (!$dueDate)
                {
                    $dueDate = new \DateTime();
                    $dueDate->setTimestamp($tenderCompletedDate->format('U'));
                    $dueDate->setTimezone($tz);
                    $dueDate->add(new \DateInterval(sprintf("P%s%s", $period, $periodType)));
                }
                $period = $tenderCompletedDate->diff($dueDate);
                if (!$period->invert)
                {
                    if ($amortized)
                    {
                        $monthNumber = 0;
                        if ($period->y) $monthNumber += $period->y * 12;
                        $monthNumber += $period->m;
                        if ($monthNumber)
                        {
                            $formatStr = $tenderCompletedDate->format('Ymd') == $tenderCompletedDate->format('Ymt') ? 'Y-m-t' : 'Y-m-d';
                            for($i=1;$i<=$monthNumber;$i++)
                            {
                                if ($i == 1) $lastDT = $tenderCompletedDate;
                                $lastU = $lastDT->format('U');
                                $lastDT->add(new \DateInterval('P1M'));
                                $nextU = $lastDT->format('U');
                                $last = new \DateTime();
                                $last->setTimestamp($lastU);
                                $last->setTimezone($tz);
                                $next = new \DateTime();
                                $next->setTimestamp($nextU);
                                $next->setTimezone($tz);
                                $dt1 = new \DateTime($last->format($formatStr), $tz);
                                $dt2 = new \DateTime($next->format($formatStr), $tz);
                                if ($dt2->format('Ym') == $dueDate->format('Ym') && $tenderCompletedDate->format('Ymd') == $tenderCompletedDate->format('Ymt') ) $dt2 = $dueDate;
                                $ret['days'][$i] = ['date'=>$dt2->format('Y-m-d'), 'length'=>$dt1->diff($dt2)->days, 'period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
                            }
                        }
                        if ($period->d)
                        {
                            if (isset($ret['days']) && $ret['days'])
                            {
                                $ret['days'][count($ret['days'])+1] = ['date'=>$dueDate->format('Y-m-d'), 'length'=>$period->d, 'period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
                            }
                        }
                    }
                    else
                    {
                        $ret['days'][1] = ['date'=>$dueDate->format('Y-m-d'), 'length'=>$period->days, 'period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
                    }
                }
            }
            else
            {
                if (!$dueDate)
                {
                    $dueDate = new \DateTime();
                    $dueDate->setTimezone($tz);
                    $dueDate->add(new \DateInterval(sprintf("P%s%s", $period, $periodType)));
                }
                $now = new \DateTime();
                $now->setTimezone($tz);
                $period = $now->diff($dueDate);
                $ret = ['period'=>['y'=>$period->y, 'm'=>$period->m, 'd'=>$period->d, 'days'=>$period->days]];
            }
            if ($ret && isset($ret['days']) && $ret['days']) $ret['count'] = count($ret['days']);
            $ret['period'] = $period;
            return $ret;
        }
        catch(\Exception $e) {
            exit($e->getMessage());
        }
    }
} 
