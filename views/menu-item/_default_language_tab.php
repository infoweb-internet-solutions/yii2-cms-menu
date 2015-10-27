<div class="tab-content language-tab">
    <?= $form->field($model, "[{$model->language}]name")->textInput([
        'maxlength' => 255,
        'name' => "MenuItemLang[{$model->language}][name]",
        'data-duplicateable' => Yii::$app->getModule('menu')->allowContentDuplication ? 'true' : 'false'
    ]); ?>

    <?= $form->field($model, "[{$model->language}]params")->textarea([
        'name' => "MenuItemLang[{$model->language}][params]",
        'readonly' => Yii::$app->user->can('Superadmin') ? false : true,
        'data-duplicateable' => Yii::$app->getModule('pages')->allowContentDuplication ? 'true' : 'false',
    ]); ?>
</div>