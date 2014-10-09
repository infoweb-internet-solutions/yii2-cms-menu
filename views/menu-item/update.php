<?php

use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\widgets\ActiveForm;

use infoweb\menu\MenuAsset;
MenuAsset::register($this);

/* @var $this yii\web\View */
/* @var $model infoweb\menu\models\MenuItem */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Menu Item',
]) . ' ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Menus'), 'url' => ['menu/index']];
$this->params['breadcrumbs'][] = ['label' => $menu->name, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['update', 'id' => $model->id]];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Update')];
?>
<div class="menu-item-create">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <?= $this->render('_form', [
        'model' => $model,
        'menu'  => $menu,
        'pages' => $pages
    ]) ?>
    
</div>