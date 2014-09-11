<?php
/**
 * Created by IntelliJ IDEA.
 * User: al
 * Date: 8/20/2014
 * Time: 1:11 AM
 */

namespace frontend\assets;
use yii\web\AssetBundle;

class WcgAsset extends AssetBundle {
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
//        'css/site.css',
        'http://dev.wangcaigu.com/template/default/Public/css/base.css',
        'http://dev.wangcaigu.com/template/default/Public/css/user.css',
    ];
    public $js = [
    ];
    public $depends = [
        'yii\web\YiiAsset',
//        'yii\bootstrap\BootstrapAsset',
    ];
}