<?php

namespace infoweb\menu;

use yii\web\AssetBundle as AssetBundle;

class MenuAsset extends AssetBundle
{
    public $sourcePath = '@infoweb/menu/assets/';
    public $css = [
        'css/main.css',
    ];

    public $js = [
        'js/menu_item.js',
        'js/main.js'
    ];
    public $depends = [
        'backend\assets\AppAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}