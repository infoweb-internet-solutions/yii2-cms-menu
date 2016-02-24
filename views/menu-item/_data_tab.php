<?php
use yii\helpers\Html;
use yii\helpers\StringHelper;
use kartik\widgets\SwitchInput;
use kartik\select2\Select2;
use infoweb\menu\models\Menu;
use infoweb\menu\models\MenuItem;
use infoweb\pages\models\Page;
?>
<div class="tab-content default-tab">

    <?= Html::hiddenInput('MenuItem[menu_id]', $model->menu_id); ?>

    <?= $form->field($model, 'type')->dropDownList([
        'system' => Yii::t('app', 'System'),
        'user-defined' => Yii::t('app', 'User defined')
    ], [
        'readonly' => Yii::$app->user->can('Superadmin') ? false : true,
    ]); ?>

    <?php // Level ?>
    <?= $form->field($model, 'parent_id')->dropDownList($model->menu->getAllForLevelDropDownList(), [
        'readonly' => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
        'options' => [$model->id => ['disabled' => true]]
    ])->label($model->getAttributeLabel('level')); ?>

    <?php // Entity types ?>
    <?= $form->field($model, 'entity')->dropDownList($entityTypes, [
        'prompt' => Yii::t('app', 'Choose a type'),
        'readonly' => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false
    ]); ?>

    <?php // Linkable entities ?>
    <?php foreach ($linkableEntities as $k => $v) : ?>

    <?= $form->field($model, 'entity_id', ['options' => ['class' => 'attribute '.StringHelper::basename($k).'-attribute', 'style' => ($model->entity != $k) ? 'display: none;' : '']])->widget(Select2::className(), [
        'data' => $v['data'],
        'options' => [
            'placeholder' => Yii::t('infoweb/menu', 'Choose a {entity}', ['entity' => strtolower($v['label'])]),
            'id' => StringHelper::basename($k).'-select2'
        ],
        'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
        'disabled'  => ($model->entity != $k) ? true : false,
        'pluginOptions' => [
            'allowClear' => true
        ],
    ])->label($v['label']); ?>

    <?php endforeach; ?>

    <?php // Url ?>
    <div class="form-group field-menuitem-url attribute url-attribute" <?php if ($model->entity != $model::ENTITY_URL) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-url" class="control-label"><?php echo Yii::t('app', 'Url'); ?></label>
        <?= Html::input('url', 'MenuItem[url]', $model->url, [
            'maxlength' => 255,
            'class'     => 'form-control',
            'id'        => 'menuitem-url',
            'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
        ]); ?>

    <?php // None ?>
    <?= Html::hiddenInput('MenuItem[entity_id]', 0, [
        'class' => 'attribute none-attribute',
        'style' => ($model->entity != $model::ENTITY_NONE) ? 'display: none;' : '',
        'disabled' => ($model->entity != $model::ENTITY_NONE) ? 'true' : '',
    ]) ?>

    <?php // Page anchors ?>
    <?= $form->field($model, 'anchor', ['options' => ['class' => 'menu-item-anchor-container',
            'style' => (($model->entity != Page::className()) || !isset($model->entityModel) || ($model->entity == Page::className() && !count($model->entityModel->htmlAnchors))) ? 'display: none;' : ''
        ]])->dropDownList(array_merge(
            ['' => Yii::t('app', '-- Choose an {item} --', ['item' => Yii::t('infoweb/menu', 'anchor')])],
            ($model->entity == Page::className() && isset($model->entityModel)) ? $model->entityModel->htmlAnchors : []
        ), [
        'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
    ]); ?>

    <?php if (Yii::$app->getModule('menu')->enablePrivateMenuItems) : ?>
    <?php echo $form->field($model, 'public')->widget(SwitchInput::classname(), [
        'inlineLabel' => false,
        'pluginOptions' => [
            'onColor' => 'success',
            'offColor' => 'danger',
            'onText' => Yii::t('app', 'Yes'),
            'offText' => Yii::t('app', 'No'),
        ],
        'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
    ]); ?>
    
    <?php endif; ?>

    <?= $form->field($model, 'active')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onColor' => 'success',
            'offColor' => 'danger',
            'onText' => Yii::t('app', 'Yes'),
            'offText' => Yii::t('app', 'No'),
        ],
        'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
    ]) ?>

</div>