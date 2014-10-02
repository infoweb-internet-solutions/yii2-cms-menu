<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use infoweb\menu\models\Menu;
use yii\web\View;
use yii\web\JqueryAsset;
use yii\helpers\Url;

use infoweb\menu\AppAsset;
AppAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel infoweb\menu\models\MenuItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $menu->name; //Yii::t('app', 'Menu items');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Menus'), 'url' => ['menu/index']];
$this->params['breadcrumbs'][] = $this->title;

// Render growl messages
$this->render('_growl_messages');

// Nested sortable max level
$this->registerJs("var maxLevels = {$max_level};", View::POS_HEAD);

?>
<div class="menu-item-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create {modelClass}', [
            'modelClass' => 'Menu Item',
        ]), ['create', 'menu-id' => Yii::$app->request->get('menu-id')], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // @todo move to main.css ?>
    <style>
        .table-bordered > thead > tr > th,
        .table-bordered > thead > tr > td {
            border-bottom-width: 1px;
        }
    </style>
    <table class="table table-bordered" style="margin: 20px 0 0 0;">
        <thead>
            <tr>
                <th>Naam</th>
                <th style="width:150px;" >
                    Acties
                </th>
            </tr>
        </thead>
    </table>

    <?php echo Menu::sortable_tree(['menu-id' => $menu->id]); ?>

</div>