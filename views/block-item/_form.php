<?php

use thefx\blocks\assets\Select2Asset\Select2Asset;
use thefx\blocks\models\blocks\Block;
use thefx\blocks\models\blocks\BlockItem;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model BlockItem */
/* @var $form yii\widgets\ActiveForm */
/* @var $block Block */

Select2Asset::register($this);
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="card card-primary card-outline card-outline-tabs">

    <div class="card-header p-0 border-bottom-0">
        <ul class="nav nav-tabs" role="tablist">

            <?php $i = 0 ?>

            <?php foreach ($block->getFieldsTemplates() as $tab => $items) : ?>
                <?php $selected = ($i === 0) ? 'true' : 'false' ?>
                <?php $class = ($i === 0) ? 'active' : '' ?>
                <?php $i++ ?>

                <li class="nav-item">
                    <a class="nav-link <?= $class ?>"
                       id="custom-tabs-<?= $i ?>-tab"
                       data-toggle="pill"
                       href="#custom-tabs-<?= $i ?>" role="tab"
                       aria-controls="custom-tabs-<?= $i ?>"
                       aria-selected="<?= $selected ?>"><?= $tab ?></a>
                </li>

            <?php endforeach; ?>

        </ul>
    </div>

    <div class="card-body">
        <div class="tab-content">

            <?php $i = 0 ?>

            <?php foreach ($block->getFieldsTemplates() as $tab => $items) : ?>
                <?php $class = ($i === 0) ? 'active show' : '' ?>
                <?php $i++ ?>

                <div class="tab-pane fade <?= $class ?>" id="custom-tabs-<?= $i ?>" role="tabpanel" aria-labelledby="custom-tabs-<?= $i ?>-tab">
                    <?php foreach ($items as $item) : ?>
                        <?= $this->render('_type_' . $item['type'], ['form' => $form, 'model' => $model, 'block' => $block, 'value' => $item['value']]) ?>
                    <?php endforeach; ?>
                </div>

            <?php endforeach; ?>

        </div>
    </div>

    <div class="card-footer clearfix">
        <?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
    </div>

</div>
<?php ActiveForm::end(); ?>

