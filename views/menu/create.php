<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model infoweb\menu\models\Menu */

$this->title = Yii::t('app', 'Create {modelClass}', [
    'modelClass' => Yii::t('infoweb/menu', 'Menu'),
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('infoweb/menu', 'Menus'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="menu-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
