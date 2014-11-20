<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\web\View;
use yii\web\JqueryAsset;
use yii\helpers\Url;
use infoweb\menu\models\Menu;

use infoweb\menu\MenuAsset;
MenuAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel infoweb\menu\models\MenuItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $menu->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('infoweb/menu', 'Menus'), 'url' => ['menu/index']];
$this->params['breadcrumbs'][] = $this->title;

// Nested sortable max level
$this->registerJs("var maxLevels = {$maxLevel};", View::POS_HEAD);
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

    <?php // Gridview ?>
    <table class="table table-bordered" style="margin: 20px 0 0 0;">
        <thead>
            <tr>
                <th>Naam</th>
                <th class="actions">Acties</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2">
                    <?php echo Menu::sortableTree(['menu-id' => $menu->id]); ?>    
                </td>
            </tr>
        </tbody>
    </table>
</div>