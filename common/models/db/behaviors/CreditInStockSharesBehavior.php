<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/28/2014
 * Time: 3:45 PM
 */

namespace common\models\db\behaviors;


use frontend\models\CreditOrder;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

class CreditInStockSharesBehavior extends AttributeBehavior
{
    public $attributes = [
        BaseActiveRecord::EVENT_BEFORE_INSERT => 'in_stock_shares',
    ];
    public $value;

    protected function getValue($event)
    {
        $model = $this->owner;
        return $model->transfer_shares;
    }
}