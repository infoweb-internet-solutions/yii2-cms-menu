<?php
use yii\helpers\Url;
use infoweb\menu\models\MenuItem;
?>
<ol class="dd-list">
    <?php foreach ($items as $k => $item) : ?>
    <li class="dd-item<?php if (($k % 2) == 0) : ?> even<?php else : ?> odd<?php endif; ?>" data-id="<?= $item['item']->id ?>">
        <div class="dd-handle"></div>
        <div class="dd-content">
            <div class="dd-label"><?= $item['item']->name ?></div>
            <div class="dd-actions">
                <?php // Update ?>
                <a href="<?= URL::to(['update', 'id' => $item['item']->id]) ?>" data-toggle="tooltip" title="<?= Yii::t('app', 'Update') ?>" data-pjax="0">
                    <span class="glyphicon glyphicon-pencil"></span>
                </a>

                <?php // Delete ?>
                <a href="<?= Url::to(['delete', 'id' => $item['item']->id]) ?>" id="delete-<?php echo $item['item']->id; ?>" data-toggle="tooltip" title="<?= Yii::t('app', 'Delete') ?>" data-method="post" data-confirm="<?php echo Yii::t('app', 'Are you sure you want to delete this item?'); ?>" data-pjax="0">
                    <span class="glyphicon glyphicon-trash"></span>
                </a>

                <?php // Active ?>
                <a href="#" data-toggler="active" data-id="<?php echo $item['item']->id; ?>" data-uri="<?= Url::toRoute('menu-item/active') ?>" data-toggle="tooltip" data-pjax="0" title="<?= Yii::t('app', 'Toggle active') ?>">
                    <?php if ($item['item']->active == 1) : ?>
                        <span class="glyphicon glyphicon-eye-open"></span>
                    <?php else : ?>
                        <span class="glyphicon glyphicon-eye-close"></span>
                    <?php endif; ?>
                </a>

                <?php // Public ?>
                <?php if ($privateItemsEnabled) : ?>
                <a href="#" data-toggler="public" data-id="<?php echo $item['item']->id; ?>" data-uri="<?= Url::toRoute('menu-item/public') ?>" data-toggle="tooltip" data-pjax="0" title="<?= Yii::t('infoweb/menu', 'Toggle public visiblity') ?>">
                    <?php if ($item['item']->public == 1) : ?>
                    <span class="glyphicon glyphicon-lock icon-disabled"></span>
                    <?php else : ?>
                    <span class="glyphicon glyphicon-lock"></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>

                <?php // Weburl ?>
                <a href="<?= Yii::getAlias('@baseUrl/') . MenuItem::findOne($item['item']->id)->getUrl(false, true); ?>" data-toggle="tooltip" target="_blank" title="<?= Yii::t('app', 'View') ?>">
                    <span class="glyphicon glyphicon-globe"></span>
                </a>

                <?php // Manage page entity ?>
                <?php if ($item['item']->entity == MenuItem::ENTITY_PAGE): ?>
                <a href="<?= Url::toRoute(['/pages/page/update', 'id' => $item['item']->entity_id, 'referrer' => 'menu-items']) ?>" data-toggle="tooltip" title="<?= Yii::t('app', 'Edit {modelClass}', ['modelClass' => strtolower(Yii::t('infoweb/pages', 'Page'))]) ?>">
                    <span class="glyphicon glyphicon-book"></span>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php // Children ?>
        <?php if (isset($item['children'])) : ?>
        <?php echo $this->render('_nestableList', ['items' => $item['children'], 'privateItemsEnabled' => $privateItemsEnabled]); ?>
        <?php endif; ?>
    </li>
    <?php endforeach; ?>
</ol>