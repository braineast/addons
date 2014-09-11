<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/28/2014
 * Time: 6:32 PM
 */

namespace common\models\db\behaviors;


use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;

class OrderSerialBehavior extends AttributeBehavior {
    public $attributes = [
        BaseActiveRecord::EVENT_BEFORE_INSERT => 'serial',
    ];
    public $value;

    public function getValue()
    {
        return $this->_createSerial();
    }

    private function _createSerial()
    {
        $orderNumber = false;
        if (preg_match('/.*\.+?(\d+)?\s*(\d+)$/', microtime(), $microTimeArr))
        {
            $orderNumber = date('YmdHis', $microTimeArr[2]).substr($microTimeArr[1], 0, 6);
        }
        return $orderNumber;
    }
} 