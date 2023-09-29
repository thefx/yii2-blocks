<?php

use kartik\select2\Select2;
use thefx\blocks\models\BlockItemPropertyAssignment;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $model BlockItemPropertyAssignment */
/* @var $form ActiveForm */
/* @var $attributeName string */
/* @var $label string */

$propertyElements = $model->property->elements;
$propertyElementList = ArrayHelper::map($propertyElements, 'id', 'title');

$this->registerCss("
    .select2-container--krajee-bs4 .select2-selection--multiple .select2-selection__choice__remove {
        padding: 3px 3px 0 0.2rem;
    }
", [], 'pi.list');

?>

<div class="form-group">

    <?= HTML::label($label) ?>

<!--    --><?php //= Select2::widget([
//        'model' => $model,
//        'bsVersion' => '4.x',
//        'attribute' => $attributeName,
//        'data' => $propertyElementList,
//        'options' => [
//            'prompt' => 'Не выбрано',
//            'multiple' => $model->property->isMultiple()
//        ],
//        'pluginOptions' => [
//            'allowClear' => true,
////            'tags' => true,
//        ],
//    ]) ?>
<!---->
<!--    --><?php //= Html::error($model, $attributeName, ['class' => 'invalid-feedback']) ?>

    <?= $form->field($model, $attributeName, ['enableClientValidation' => false])->widget(Select2::class, [
        'data' => $propertyElementList,
        'bsVersion' => '4.x',
//        'theme' => Select2::THEME_KRAJEE,
        'options' => [
            'placeholder' => '',
            'multiple' => $model->property->isMultiple(),
        ],
        'pluginOptions' => [
            'allowClear' => ! $model->property->isMultiple(),
//            'tags' => true,
        ],
    ])->label(false) ?>

</div>
