<div class="tab-content language-tab">
    <?= $form->field($model, "[{$model->language}]name")->textInput([
        'maxlength' => 255,
        'name' => "MenuItemLang[{$model->language}][name]"
    ]); ?>

    <?= $form->field($model, "[{$model->language}]params")->textarea([
        'name' => "MenuItemLang[{$model->language}][params]",
        'readonly' => Yii::$app->user->can('Superadmin') ? false : true,
    ]); ?>
</div>