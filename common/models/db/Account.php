<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 9/11/2014
 * Time: 12:45 PM
 */

namespace common\models\db;


use yii\db\ActiveRecord;

class Account extends ActiveRecord{
    public static function tableName()
    {
        return '{{%user_data}}';
    }
}