<div class="tab-content language-tab">
    <?= $form->field($model, "[{$model->language}]name")->textInput([
        'maxlength' => 255,
        'name' => "MenuItemLang[{$model->language}][name]"
    ]); ?>
</div>