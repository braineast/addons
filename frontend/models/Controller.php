<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/20/2014
 * Time: 1:02 AM
 */

namespace frontend\models;

use yii\web\Controller as YiiWebController;
use Yii;

class Controller extends YiiWebController{

    const DEAL_PERIOD_TYPE_DAY = 'D';
    const DEAL_PERIOD_TYPE_MONTH = 'M';

    public $layout = 'wcg';

    public function beforeAction($action)
    {
        if (parent::beforeAction($action))
        {
            if ($userId = \Yii::$app->getSession()->get('user_id'))
            {
                if (Yii::$app->user->isGuest || $userId != Yii::$app->user->getId())
                {
                    Yii::$app->user->setIdentity(User::findIdentity($userId));
                }
            }
            elseif (!Yii::$app->user->isGuest) Yii::$app->user->logout();
        }
        return true;
    }

    /**
     * @param string $tenderCompletedDate The deal load full timestamp in string, sucn as 2014-08-08.
     * @param Char $periodType Whether 'D' or 'M', Day or Month
     * @param Integer $period Day number, or Month number
     * @param Integer $dueDate The deal's due date, this is timestamp in integer
     * @param Boolean or Integer (0|1) $amortized 该借款是否属于分期偿付
     */
    protected function loanTermCalc($tenderCompletedDate=null, $period=null, $dueDate=null, $periodType=self::DEAL_PERIOD_TYPE_DAY, $amortized = true)
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