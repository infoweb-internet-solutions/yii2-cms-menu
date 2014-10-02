<input type="hidden" name="<?php echo $language; ?>[MenuItemLang][language]" value="<?php echo $language; ?>">

<?php // @todo autofocus ?>

<?= $form->field($model, 'name')->textInput([
    'maxlength' => 255,
    'name' => "{$language}[MenuItemLang][name]",
    'id' => "name-{$language}",
]); ?>