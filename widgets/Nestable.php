<?php

namespace infoweb\menu\widgets;

use Yii;
use yii\bootstrap\Widget;
use yii\helpers\Html;
use yii\web\View;
use infoweb\menu\widgets\assets\NestableAsset;

class Nestable extends Widget
{
    public $template = 'nestable';

    /**
     * The items of the nestable tree
     * @var array
     */
    public $items = [];

    /**
     * The max depth of the nestable tree
     * @var int
     */
    public $maxDepth = 2;

    /**
     * The that will be used for the pjax operations
     * @var string
     */
    public $pjaxId = 'pjax-nestable';

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        // Register the nestable asset bundle
        $view = $this->getView();
        NestableAsset::register($view);

        // Register JS variables
        $view->registerJs("var nestableSettings = {
            maxDepth: {$this->maxDepth},
            pjaxId: '{$this->pjaxId}'
        };", View::POS_HEAD);

        return $this->render($this->template, [
            'items'               => $this->items,
            'pjaxId'              => $this->pjaxId,
            'privateItemsEnabled' => Yii::$app->getModule('menu')->enablePrivateMenuItems
        ]);
    }
}