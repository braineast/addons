<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/25/2014
 * Time: 11:17 AM
 */

namespace common\models\db;

use yii\db\ActiveRecord;


class Deal extends ActiveRecord {

    public static function tableName()
    {
        return '{{%deal}}';
    }
} 