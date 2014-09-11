<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/29/2014
 * Time: 10:12 AM
 */

namespace common\models\db\behaviors;


use frontend\models\Credit;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class CreditOrderCalcBehavior extends Behavior
{
    public $attributes = [];
    public $value;

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    public function afterUpdate()
    {
        $model = $this->owner;
        return true;
    }

    public function beforeInsert()
    {
        $model = $this->owner;
        if ($credit = Credit::findOne($model->credit_id))
        {
            $model->setAttribute('principal_amt', $credit->unit_principal_amt);
            $model->setAttribute('accrued_interest_amt', $credit->unit_accrued_interest_amt);
            $model->setAttribute('discount_rate', $credit->discount_rate);
            $model->setAttribute('unit_value', $credit->unit_value);
            return true;
        }
        return false;
    }
}