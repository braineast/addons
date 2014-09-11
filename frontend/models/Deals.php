<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/20/2014
 * Time: 3:57 AM
 */

namespace frontend\models;
use yii\db\ActiveRecord;

/**
 * Class Deals
 * @package frontend\models
 *
 * @property integer $id
 * @property
 */
class Deals extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%cp_deal}}';
    }
}