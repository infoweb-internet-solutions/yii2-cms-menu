<?php if (Yii::$app->getSession()->hasFlash('menu-item')): ?>
<div class="alert alert-success">
    <?= Yii::$app->getSession()->getFlash('menu-item') ?>
</div>
<?php endif; ?>

<?php if (Yii::$app->getSession()->hasFlash('menu-item-error')): ?>
<div class="alert alert-danger">
    <?= Yii::$app->getSession()->getFlash('menu-item-error') ?>
</div>
<?php endif; ?>