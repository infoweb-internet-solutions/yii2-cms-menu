<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
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
                'entityTypes'       => $entityTypes,
                'allowContentDuplication' => $allowContentDuplication
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

    <div id="create-entity-modal" class="fade modal" role="dialog" tabindex="-1">
        <div class="modal-dialog" style="width:70%">
            <div class="modal-content">
                <div class="modal-body">
                    <?php /* Keep blank ajax */ ?>
                </div>
            </div>
        </div>
    </div>
</div>