<?php

namespace infoweb\menu;

use yii\web\AssetBundle as AssetBundle;

class MenuAsset extends AssetBundle
{
    public $sourcePath = '@infoweb/menu/assets/';
    public $css = [
        '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css',
        'css/main.css',
    ];

    public $js = [
        '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.js',
        'js/jquery.mjs.nestedSortable.js',
        'js/nested-sortable-settings.js',
        'js/floatThead/jquery.floatThead.min.js',
        'js/menu_item.js',
        'js/main.js',
    ];
    public $depends = [
        'backend\assets\AppAsset',
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}