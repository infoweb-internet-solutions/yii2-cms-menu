<?php

use yii\web\session;
use kartik\widgets\Growl;

$session = new Session;

if ($session->hasFlash('menu-item-success'))
{
    echo Growl::widget([
        'type' => Growl::TYPE_SUCCESS,
        'body' => $session->getFlash('menu-item-success'),
    ]);
}

if ($session->hasFlash('menu-item-error'))
{
    echo Growl::widget([
        'type' => Growl::TYPE_DANGER,
        'body' => $session->getFlash('menu-item-error'),
        'delay' => 200,
        'pluginOptions' => [
            'position' => [
                'from' => 'top',
                'align' => 'right',
            ]
        ]
    ]);
}