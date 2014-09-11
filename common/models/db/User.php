<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/23/2014
 * Time: 5:04 PM
 */

namespace common\models\db;
use yii\db\ActiveRecord;


class User extends ActiveRecord {
    public static function tableName()
    {
        return '{{%user}}';
    }
} 