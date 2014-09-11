<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/28/2014
 * Time: 4:58 PM
 */

namespace common\models\db;


use yii\db\ActiveRecord;

class CreditOrder extends ActiveRecord {

    public static function tableName()
    {
        return '{{%credit_order}}';
    }

} 