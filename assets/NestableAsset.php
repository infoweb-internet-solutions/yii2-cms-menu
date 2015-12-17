<?php

namespace infoweb\menu\assets;

use yii\web\AssetBundle;

class NestableAsset extends AssetBundle
{
    public $sourcePath = '@bower/nestable2';
    public $css = [
        'jquery.nestable.css'
    ];
    public $js = [
        'jquery.nestable.js'
    ];
}