<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/28/2014
 * Time: 5:17 PM
 */

namespace frontend\models;


use common\models\db\behaviors\CreditOrderCalcBehavior;
use common\models\db\behaviors\OrderSerialBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Class CreditOrder
 * @package frontend\models
 * @property integer $id
 * @property integer $credit_id
 * @property integer $user_id
 * @property string $serial
 * @property integer $shares
 * @property float $unit_value
 * @property float $discount_rate
 * @property float $principal_amt
 * @property float $accrued_interest_amt
 * @property float $earning_rate
 * @property float $amount
 * @property float $fee
 * @property integer $status
 * @property integer $updated_at
 * @property integer $created_at
 *
 */
class CreditOrder extends ActiveRecord
{
    const STATUS_UNPAID = 0;
    const STATUS_PAID = 1;
    public static function tableName()
    {
        return '{{%credit_order}}';
    }

    public function init()
    {
        parent::init();
        $this->on(ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'updateCreditInStockShares']);
        $this->on(static::EVENT_AFTER_UPDATE, [$this, 'updateOriginalRepaymentOrders']);
    }

    public function updateOriginalRepaymentOrders()
    {
        if ($this->status == static::STATUS_PAID && $this->getOldAttribute('status') == static::STATUS_UNPAID)
        {
            //计算本次债权投资的可获取的收益率
        }
        return true;
    }

    public function updateCreditInStockShares()
    {
        if ($this->status == static::STATUS_PAID && $this->getOldAttribute('status') == static::STATUS_UNPAID)
        {
            $credit = Credit::findOne($this->credit_id);
            $credit->in_stock_shares = $credit->in_stock_shares - $this->shares;
            if ($credit->in_stock_shares == 0) $credit->status = 1;
            return $credit->save();
        }
        return true;
    }

    public function behaviors()
    {
        return [
            CreditOrderCalcBehavior::className(),
//            OrderSerialBehavior::className(),
            TimestampBehavior::className(),
        ];
    }
} 