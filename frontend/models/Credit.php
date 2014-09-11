<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/23/2014
 * Time: 12:33 PM
 */

namespace frontend\models;
use common\models\db\behaviors\CreditInStockSharesBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

use Yii;

/**
 * Class CreditForm
 * @package frontend\models
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $shares
 * @property integer $transfer_shares
 * @property integer $in_stock_shares
 * @property float $discount_rate
 * @property float $principal_amt
 * @property float $accrued_interest_amt
 * @property float $unit_value
 * @property float $earning_rate
 * @property integer $status
 * @property integer $is_deleted
 * @property integer $is_canceled
 * @property integer $updated_at
 * @property integer $created_at
 */
class Credit extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%credit}}';
    }

    public function init()
    {
        parent::init();
        $this->transfer_shares = 1;
        $this->discount_rate = 0;
    }

    public function rules()
    {
        return [
            [['user_id', 'order_id', 'transfer_shares', 'discount_rate'], 'required'],
            ['user_id', 'exist', 'targetClass'=>'common\models\db\User', 'targetAttribute'=>'id'],
            ['order_id', 'exist', 'targetClass'=>'common\models\db\DealOrder', 'targetAttribute'=>'deal_number'],
            ['order_id', 'unique', 'message'=>'该订单已经存在！'],
            ['transfer_shares', 'number', 'integerOnly' => true, 'min'=>1],
            ['discount_rate', 'number', 'min'=>0, 'max'=>5],
        ];
    }

    public function attributeLabels()
    {
        return [
            'transfer_shares' => Yii::t('credit', 'Transfer Shares'),
            'discount_rate' => Yii::t('credit', 'Discount Rate'),
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            CreditInStockSharesBehavior::className(),
        ];
    }

}