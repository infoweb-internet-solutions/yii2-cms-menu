<?php
use yii\helpers\Html;
use yii\widgets\Pjax;
?>
<?php Pjax::begin(['id' => $pjaxId]) ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Naam</th>
            <th class="actions">Acties</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <div class="dd">
                    <?php echo $this->render('_nestableList', ['items' => $items, 'privateItemsEnabled' => $privateItemsEnabled]); ?>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<?php Pjax::end() ?>