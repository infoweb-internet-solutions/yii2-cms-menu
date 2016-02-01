<?php

namespace infoweb\menu\assets;

use yii\web\AssetBundle;

class NestableAsset extends AssetBundle
{
    public $sourcePath = '@infoweb/menu/assets/';
    public $css = [
        'css/jquery.nestable.css'
    ];
    public $js = [
        'js/jquery.nestable.js'
    ];
}
