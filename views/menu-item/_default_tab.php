<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\SwitchInput;
use infoweb\menu\models\Menu;
?>
<div class="tab-content default-tab">
    
    <?= Html::hiddenInput('MenuItem[menu_id]', $model->menu_id); ?>
    
    <div class="form-group field-menuitem-parent_id">
        <label for="menuitem-parent_id" class="control-label"><?= Yii::t('infoweb/menu', 'Level'); ?></label>
        <select autofocus class="form-control" name="MenuItem[parent_id]" id="menuitem-parent_id">
            <option value="0">Root</option>
            <?= $levelSelect ?>
        </select>
        <div class="help-block"></div>
    </div>
    
    <?= $form->field($model, 'entity')->dropDownList([
        'page'          => Yii::t('infoweb/pages', 'Page'),
        'menu-item'     => Yii::t('infoweb/menu', 'Menu item'),
        'url'           => Yii::t('app', 'Url')
    ],[
        'prompt' => Yii::t('app', 'Choose a type')
    ]); ?>

    <div class="form-group field-menuitem-entity_id attribute page-attribute" <?php if ($model->entity != $model::ENTITY_PAGE) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-entity_id" class="control-label"><?= Yii::t('infoweb/pages', 'Page'); ?></label>
        <?= Html::dropDownList('MenuItem[entity_id]', $model->entity_id, ArrayHelper::map($pages, 'id', 'name'), [
            'class'     => 'form-control',
            'id'        => 'menuitem-entity_id',
            'prompt'    => Yii::t('infoweb/alias', 'Choose a page'),
            'options'   => [
                'disabled'  => ($model->entity != $model::ENTITY_PAGE) ? true : false
            ]
        ]) ?>
        <div class="help-block"></div>
    </div>

    <div class="form-group field-menuitem-url attribute url-attribute" <?php if ($model->entity != $model::ENTITY_URL) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-url" class="control-label"><?php echo Yii::t('app', 'Url'); ?></label>
        <?= Html::input('url', 'MenuItem[url]', $model->url, [
            'maxlength' => 255,
            'class'     => 'form-control',
            'id'        => 'menuitem-url'
        ]); ?>
        <div class="help-block"></div>
    </div>

    <div class="form-group field-menuitem-entity_id attribute menu-item-attribute" <?php if ($model->entity != $model::ENTITY_MENU_ITEM) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-entity_id" class="control-label"><?= Yii::t('infoweb/menu', 'Menu item'); ?></label>
        <select name="MenuItem[entity_id]" class="form-control" id="menuitem-entity_id" <?php if ($model->entity != $model::ENTITY_MENU_ITEM) : ?>disabled<?php endif; ?>>
            <option value="">-- <?php echo Yii::t('infoweb/menu', 'Choose a menu item'); ?> --</option>
            <?php foreach (Menu::find()->all() as $menu): ?>
            <option value="" disabled="disabled">* <?php echo $menu->name; ?> *</option>
            <?php echo $menu->menu_items_select(['menu-item-id' => $model->id, 'selected' => ($model->entity == $model::ENTITY_MENU_ITEM) ? $model->entity_id : 0]); ?>
            <?php endforeach; ?>
        </select>
        <div class="help-block"></div>
    </div>

</div>