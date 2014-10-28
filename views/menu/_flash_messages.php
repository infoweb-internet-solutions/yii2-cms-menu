<?php if (Yii::$app->getSession()->hasFlash('menu')): ?>
<div class="alert alert-success">
    <?= Yii::$app->getSession()->getFlash('menu') ?>
</div>
<?php endif; ?>

<?php if (Yii::$app->getSession()->hasFlash('menu-error')): ?>
<div class="alert alert-danger">
    <?= Yii::$app->getSession()->getFlash('menu-error') ?>
</div>
<?php endif; ?>