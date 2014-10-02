<?php

use yii\helpers\Html;
use infoweb\pages\models\Page;
use yii\helpers\ArrayHelper;
use infoweb\menu\models\Menu;
//use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model infoweb\menu\models\MenuItem */
/* @var $form yii\widgets\ActiveForm */

// Render growl messages
$this->render('_growl_messages');

?>

<div class="menu-item-form">

    <div class="form-group">&nbsp;</div>

    <?= Html::hiddenInput('MenuItem[menu_id]', $menu_id); ?>

    <div class="form-group field-menuitem-parent_id">
        <label for="menuitem-parent_id" class="control-label"><?= Yii::t('app', 'Level'); ?></label>
        <select autofocus class="form-control" name="MenuItem[parent_id]" id="menuitem-parent_id">
            <option value="0">Root</option>
            <?= $level_select ?>
        </select>
        <div class="help-block"></div>
    </div>

    <div class="form-group field-menuitem-type">
        <label for="menuitem-type" class="control-label"><?= Yii::t('app', 'Type'); ?></label>
        <?= Html::dropDownList('type', $model->entity, [ 'page' => Yii::t('app', 'Page'), 'menu-item' => Yii::t('app', 'Menu link'), 'url' => yii::t('app', 'Url'), ], ['class' => 'form-control', 'id' => 'menuitem-entity', 'prompt' => Yii::t('app', 'Choose a type')]) ?>
    </div>

    <div class="form-group field-menuitem-page attribute page-attribute" <?php if ($model->entity != $model::ENTITY_PAGE) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-page" class="control-label"><?= Yii::t('app', 'Page'); ?></label>
        <?= Html::dropDownList('page_id', $model->entity_id, $pages, ['class' => 'form-control', 'id' => 'menuitem-page', 'prompt' => Yii::t('app', 'Choose a page')]) ?>
        <?php /*
        echo Select2::widget([
            'name' => 'page_id',
            'data' => $pages,
            'options' => ['placeholder' => Yii::t('app', 'Select a page'), 'class' => 'form-control', 'id' => 'menuitem-page'],
            'pluginOptions' => [
                'allowClear' => true,
                'initSelection' => ['val', 12162], // This is not working
            ],
        ]);
        */ ?>
        <div class="help-block"></div>
    </div>

    <div class="form-group field-menuitem-url attribute url-attribute" <?php if ($model->entity != $model::ENTITY_URL) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-url" class="control-label">Url</label>
        <?= Html::input('url', 'url', $model->url, ['maxlength' => 255, 'class' => 'form-control', 'id' => 'menuitem-url']); ?>
        <div class="help-block"></div>
    </div>

    <div class="form-group field-menuitem-menu_id attribute menu-item-attribute" <?php if ($model->entity != $model::ENTITY_MENU_ITEM) : ?>style="display: none;"<?php endif; ?>>
        <label for="menuitem-menu_id" class="control-label"><?= Yii::t('app', 'Menu-item'); ?></label>
        <select name="menu_id" class="form-control" id="menuitem-menu_id">
            <option value="">-- Kies een menu-item --</option>
            <option value="" disabled="disabled">&nbsp;</option>
            <?php foreach (Menu::find()->all() as $menu): ?>
            <option value="" disabled="disabled"><?php echo $menu->name; ?></option>
            <?php echo $menu->menu_items_select(['menu-item-id' => $model->id, 'selected' => ($model->entity == $model::ENTITY_MENU_ITEM) ? $model->entity_id : 0]); ?>
            <option value="" disabled="disabled">&nbsp;</option>
            <?php endforeach; ?>
        </select>
        <div class="help-block"></div>
    </div>

</div>