<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/25/2014
 * Time: 11:50 AM
 */

namespace common\models\db;

use yii\db\ActiveRecord;


class CnpnrAccount extends ActiveRecord {

    public static function tableName()
    {
        return '{{%user_huifu}}';
    }
} 