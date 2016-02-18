<?php
use yii\helpers\Html;
use kartik\widgets\SwitchInput;
use infoweb\menu\models\Menu;
use kartik\select2\Select2;
use yii\bootstrap\Modal;
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
    <div class="form-group field-menuitem-parent_id">
        <label for="menuitem-parent_id" class="control-label"><?= Yii::t('infoweb/menu', 'Level'); ?></label>
        <select <?= ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? 'readonly="true"' : '' ?> autofocus class="form-control" name="MenuItem[parent_id]" id="menuitem-parent_id">
            <option value="0">Root</option>
            <?= $levelSelect ?>
        </select>
        <div class="help-block"></div>
    </div>
    
    <?php // Entity types ?>
    <?= $form->field($model, 'entity')->dropDownList($entityTypes, [
        'prompt' => Yii::t('app', 'Choose a type'),
        'readonly' => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false
    ]); ?>

    <?php // Pages ?>
    <div class="form-group field-menuitem-entity_id attribute page-attribute" <?php if ($model->entity != $model::ENTITY_PAGE) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-entity_id" class="control-label"><?= Yii::t('infoweb/pages', 'Page'); ?></label>
        <?= Select2::widget([
            'model' => $model,
            'attribute' => 'entity_id',
            'data' => $pages,
            'options' => [
                'placeholder' => Yii::t('infoweb/alias', 'Choose a page'),
            ],
            'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
            'disabled'  => ($model->entity != $model::ENTITY_PAGE) ? true : false,
            'pluginOptions' => [
                'allowClear' => true
            ],
        ]); ?>
        <div class="help-block"></div>
    </div>

    <div class="add-page" <?php if ($model->entity != $model::ENTITY_PAGE) : ?>style="display: none;"<?php endif; ?>>
        <?php Modal::begin([
            'id' => 'duplicateable-modal',
            'toggleButton' => ['label' => \kartik\icons\Icon::show('plus')],

        ]); ?>

        <?php echo Yii::$app->runAction('pages/page/create', ['test' => true]) ?>

        <?php Modal::end(); ?>

    </div>

    <?php // Linkable entities ?>
    <?php foreach ($linkableEntities as $k => $v) : ?>
    
    <div class="form-group field-<?= $k ?>-entity_id attribute <?= $k ?>-attribute" <?php if ($model->entity != $k) : ?>style="display: none;"<?php endif; ?>>
        <label for="<?= $k ?>-entity_id" class="control-label"><?= $v['label'] ?></label>
        <?= Html::dropDownList('MenuItem[entity_id]', $model->entity_id, $v['data'], [
            'class'     => 'form-control',
            'id'        => 'menuitem-entity_id',
            'prompt'    => Yii::t('infoweb/menu', 'Choose a {entity}', ['entity' => strtolower($v['label'])]),
            'disabled'  => ($model->entity != $k) ? true : false,
            'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
        ]) ?>
        <div class="help-block"></div>
    </div>
        
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
        <div class="help-block"></div>
    </div>

    <?php // Menu items ?>
    <div class="form-group field-menuitem-entity_id attribute menu-item-attribute" <?php if ($model->entity != $model::ENTITY_MENU_ITEM) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-entity_id" class="control-label"><?= Yii::t('infoweb/menu', 'Menu item'); ?></label>
        <select name="MenuItem[entity_id]" class="form-control" id="menuitem-entity_id" <?php if ($model->entity != $model::ENTITY_MENU_ITEM) : ?>disabled<?php endif; ?> <?php if ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) : ?>readonly<?php endif; ?>>
            <option value="">-- <?php echo Yii::t('infoweb/menu', 'Choose a menu item'); ?> --</option>
            <?php foreach (Menu::find()->all() as $menu): ?>
            <option value="" readonly="true">* <?php echo $menu->name; ?> *</option>
            <?php echo $menu->menu_items_select(['menu-item-id' => $model->id, 'selected' => ($model->entity == $model::ENTITY_MENU_ITEM) ? $model->entity_id : 0]); ?>
            <?php endforeach; ?>
        </select>
        <div class="help-block"></div>
    </div>

    <?php // None ?>
    <?= Html::hiddenInput('MenuItem[entity_id]', 0, [
        'class' => 'attribute none-attribute',
        'style' => ($model->entity != $model::ENTITY_NONE) ? 'display: none;' : '',
        'disabled' => ($model->entity != $model::ENTITY_NONE) ? 'true' : '',
    ]) ?>

    <?php // Page anchors ?>
    <div class="menu-item-anchor-container"<?php if (($model->entity != $model::ENTITY_PAGE) || !isset($model->entityModel) || ($model->entity == $model::ENTITY_PAGE && !count($model->entityModel->htmlAnchors))) : ?> style="display: none;"<?php endif; ?>>
        <?= $form->field($model, 'anchor')->dropDownList(array_merge(
                ['' => Yii::t('app', '-- Choose an {item} --', ['item' => Yii::t('infoweb/menu', 'anchor')])],
                ($model->entity == $model::ENTITY_PAGE && isset($model->entityModel)) ? $model->entityModel->htmlAnchors : []
            ), [
            'readonly'  => ($model->type == $model::TYPE_SYSTEM && !Yii::$app->user->can('Superadmin')) ? true : false,
        ]); ?>
    </div>
    
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