<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\web\View;
use yii\web\JqueryAsset;
use yii\helpers\Url;
use infoweb\menu\models\Menu;
use infoweb\menu\widgets\Nestable;

$this->title = $menu->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('infoweb/menu', 'Menus'), 'url' => ['menu/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-item-index">

    <?php // Title ?>
    <h1>
        <?= Html::encode($this->title) ?>
        <?php // Buttons ?>
        <div class="pull-right">
            <?= Html::a(Yii::t('app', 'Create {modelClass}', [
                'modelClass' => Yii::t('infoweb/menu', 'Menu item'),
            ]), ['create'], ['class' => 'btn btn-success']) ?>
        </div>
    </h1>

    <?php // Flash messages ?>
    <?php echo $this->render('_flash_messages'); ?>

    <?php // Nestable ?>
    <?= Nestable::widget([
        'items'    => $menu->getNestableTree(),
        'maxDepth' => $menu->max_level
    ]) ?>

</div>