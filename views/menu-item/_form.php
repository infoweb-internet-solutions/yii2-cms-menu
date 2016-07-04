<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Tabs;
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
                'model'                   => $model,
                'form'                    => $form,
                'allowContentDuplication' => $allowContentDuplication
            ]),
        ],
        [
            'label'     => Yii::t('app', 'Data'),
            'content'   => $this->render('_data_tab', [
                'model'             => $model,
                'form'              => $form,
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
        <?= $this->render('@infoweb/cms/views/ui/formButtons', ['model' => $model]) ?>
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