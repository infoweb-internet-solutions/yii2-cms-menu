<?php
use yii\bootstrap\Tabs;

$tabs = [];

// Add the language tabs
foreach (Yii::$app->params['languages'] as $languageId => $languageName) {
    $tabs[] = [
        'label'     => $languageName,
        'content'   => $this->render('_default_language_tab', ['model' => $model->getTranslation($languageId), 'form' => $form]),
        'active'    => ($languageId == Yii::$app->language) ? true : false
    ];
}
?>
<div class="tab-content default-tab">
    <?= Tabs::widget(['items' => $tabs]); ?>
</div>