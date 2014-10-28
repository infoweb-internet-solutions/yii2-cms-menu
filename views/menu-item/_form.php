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
        'id'                        => 'menu-item-form',
        'options'                   => ['class' => 'tabbed-form'],
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
                'pages' => $pages,
                'levelSelect' => $levelSelect
            ]),
        ]
    ];
    
    // Add the language tabs
    foreach (Yii::$app->params['languages'] as $languageId => $languageName) {
        $tabs[] = [
            'label' => $languageName,
            'content' => $this->render('_language_tab', ['model' => $model->getTranslation($languageId), 'form' => $form]),
            'active' => ($languageId == Yii::$app->language) ? true : false
        ];
    } 
    
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

</div>