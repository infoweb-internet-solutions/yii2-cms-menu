<?php

namespace infoweb\menu\widgets\assets;

use yii\web\AssetBundle;

class NestableAsset extends AssetBundle
{
    public $sourcePath = '@infoweb/menu/widgets/assets/';
    public $css        = [
        'css/nestable.css'
    ];
    public $js         = [
        'js/nestable.js'
    ];
    public $depends    = [
        'backend\assets\AppAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
        'infoweb\menu\assets\NestableAsset'
    ];
}