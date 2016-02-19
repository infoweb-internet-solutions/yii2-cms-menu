<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Tabs;

/* @var $this yii\web\View */
/* @var $model infoweb\partials\models\PagePartial */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="menu-item-form">
    
    <?php // Flash messages ?>
    <?php echo $this->render('_flash_messages'); ?>
    
    <?php
    // Init the form
    $form = ActiveForm::begin([
        'options'                   => [
            'class' => 'tabbed-form',
            'id'                        => 'menu-item-form',
        ],
        'enableAjaxValidation'      => true,
        'enableClientValidation'    => false        
    ]);

    // Initialize the tabs
    $tabs = [
        [
            'label'     => Yii::t('app', 'General'),
            'content'   => $this->render('_default_tab', [
                'model' => $model,
                'form'  => $form,                
            ]),
        ],
        [
            'label'     => Yii::t('app', 'Data'),
            'content'   => $this->render('_data_tab', [
                'model'             => $model,
                'form'              => $form,
                'pages'             => $pages,
                'levelSelect'       => $levelSelect,
                'linkableEntities'  => $linkableEntities,
                'entityTypes'       => $entityTypes
            ]),
            'active'    => true,
        ],
    ];
    
    // Display the tabs
    echo Tabs::widget(['items' => $tabs]);   
    ?>

    <div class="form-group buttons">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Create & close') : Yii::t('app', 'Update & close'), ['class' => 'btn btn-default', 'name' => 'close']) ?>
        <?= Html::submitButton(Yii::t('app', $model->isNewRecord ? 'Create & new' : 'Update & new'), ['class' => 'btn btn-default', 'name' => 'new']) ?>
        <?= Html::a(Yii::t('app', 'Close'), ['index'], ['class' => 'btn btn-danger']) ?>
    </div>

    <?php ActiveForm::end(); ?>


    <div class="create-page" <?php if ($model->entity != $model::ENTITY_PAGE) : ?>style="display: none;"<?php endif; ?>>
        <?php \yii\bootstrap\Modal::begin([
            'id' => 'create-page-modal',
        ]); ?>

        <?php // Init the form
        $form = ActiveForm::begin([
            'action'                    => \yii\helpers\Url::toRoute('/pages/page/create'),
            'options'                   => [
                'class' => 'tabbed-form',
                'id'                        => 'page-form',
            ],
            'enableAjaxValidation'      => true,
            'enableClientValidation'    => false
        ]); ?>

        <?php echo Yii::$app->runAction('pages/page/create', ['modal' => true]) ?>

        <?php ActiveForm::end(); ?>

        <?php \yii\bootstrap\Modal::end(); ?>

    </div>

</div>