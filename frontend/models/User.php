<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/20/2014
 * Time: 1:00 AM
 */

namespace frontend\models;

use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface {
    public static function tableName()
    {
        return '{{cp_user}}';
    }
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username'=>$username]);
    }

    public static function findIdentityByAccessToken($token)
    {
        return '';
    }

    public function getId()
    {
        return $this->getPrimaryKey();
    }

    public function getName()
    {
        return $this->username;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getAuthKey()
    {
        return 'getAuthKey';
    }

    public function validateAuthKey($authKey)
    {
        return true;
    }
}