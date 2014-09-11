<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/25/2014
 * Time: 2:58 PM
 */

namespace common\models\db;
use common\models\db\behaviors\OrderSerialBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;


/*
 * @property integer $refund_time
 * @property integer $status
 */
class RepaymentOrder extends ActiveRecord {

    public static function tableName()
    {
        return '{{%refund_record}}';
    }

    public function behaviors()
    {
        return [
            'autoTimeStamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['create_time'],
                ],
            ],
            'orderId' => [
                'class'=> OrderSerialBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'huankuan_number',
                ],
            ],
        ];
    }
}