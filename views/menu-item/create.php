<?php

use yii\helpers\Html;
use yii\bootstrap\Tabs;
use yii\widgets\ActiveForm;

use infoweb\menu\AppAsset;
AppAsset::register($this);

/* @var $this yii\web\View */
/* @var $model infoweb\menu\models\MenuItem */

$this->title = Yii::t('app', 'Create {modelClass}', [
    'modelClass' => 'Menu Item',
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Menus'), 'url' => ['menu/index']];
$this->params['breadcrumbs'][] = ['label' => $menu->name, 'url' => ['index', 'menu-id' => $menu->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <?php
    $items = [
        [
            'label' => 'General',
            'content' => $this->render('_form', [
                'model' => $model,
                'form' => $form,
                'level_select' => $level_select,
                'menu_id' => $menu->id,
                'pages' => $pages,
            ]),
            'active' => true,
        ],
    ];

    // loop through languages to edit
    foreach (Yii::$app->params['languages'] as $k => $language) {
        $model->language = $k;
        $items[] = [
            'label' => $language,
            'content' => $this->render('_translation_item', ['model' => $model, 'language' => $k, 'form' => $form]),
        ];
    }
    ?>

    <?php
    echo Tabs::widget([
        'items' => $items,
    ]);
    ?>

    <div class="form-group">&nbsp;</div>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create & close') : Yii::t('app', 'Update & close'), ['class' => 'btn btn-default', 'name' => 'close']) ?>
        <?= Html::submitButton(Yii::t('app', $model->isNewRecord ? 'Create & new' : 'Update & new'), ['class' => 'btn btn-default', 'name' => 'new']) ?>
        <?= Html::a(Yii::t('app', 'Close'), ['index', 'menu-id' => $menu->id], ['class' => 'btn btn-danger']) ?>
    </div>

    <?php ActiveForm::end(); ?>
    
</div>
