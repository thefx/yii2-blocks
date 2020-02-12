<?php

use app\shop\entities\Block\BlockCategory;
use app\shop\entities\Block\BlockItem;
use app\shop\entities\Block\BlockItemPropAssignments;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/* @var $model BlockItemPropAssignments */
/* @var string $attributeName */

//$model->{$attributeName} = ($model->{$attributeName} !== null) ? $model->{$attributeName} : 1;

$relBlockItemList = $model->prop->getAssignBlockCatList();
$blockItems = BlockItem::find()->where(['parent_id' => $model->value])->all();

$inputId = Html::getInputId($model, $attributeName);
$this->registerJs(" $('#{$inputId}').select2(/*{placeholder: '', allowClear: true}*/);", View::POS_READY);
?>

<div class="form-group">

    <?= HTML::label($model->prop->title); ?>

    <div class="hide">
        <?= Html::activeDropDownList($model, $attributeName, $relBlockItemList, ['class' => 'form-control select2', 'style'=> 'width: 100%;', 'multiple' => $model->prop->isMulti(), 'prompt' => 'Не выбрано', 'disabled' => 'disabled']); ?>
    </div>

    <?php if ($blockItems) : ?>
        <table class="table table-bordered">
            <tbody>
            <?php foreach ($blockItems as $item) : ?>
                <tr>
                    <td style="width: 10px" class="text-center"><i class="<?= $item->getPropValue('STRING_ICON') ?>"></i></td>
                    <td><?= $item->title; ?></td>
                    <td><?= strip_tags($item->anons) ?></td>
                    <td><?= $item->public ? '<span class="label label-success">Активен</span>' : '<span class="label label-default">Скрыт</span>'; ?></td>
                    <td style="width: 20px">
                        <div role="presentation" class="dropdown navbar-right" style="margin: 0">
                            <a href="#" class="dropdown-toggle" id="drop4" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="fa fa-bars"></i> </a>
                            <ul class="dropdown-menu" id="menu1" aria-labelledby="drop4">
                                <li><a href="<?= Url::to(['block-item/update', 'id' => $item->id, 'parent_id' => $item->parent_id]) ?>" data-pjax="0" target="_blank">
                                        <span class="fa fa-edit position-left"></span>Редактировать</a></li>
                                <li class="divider"></li>
                                <li><a href="<?= Url::to(['block-item/update', 'id' => $item->id, 'parent_id' => $item->parent_id]) ?>" data-pjax="0" data-confirm="Вы уверены, что хотите удалить запись?" data-method="post" target="_blank">
                                        <span class="fa fa-trash text-danger position-left"></span><span class="text-danger">Удалить</span></a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?php if ($model->value) : ?>
        <div>
            <?= Html::a('Добавить', ['block-item/create', 'parent_id' => $model->value, 'model_id' => $_GET['id']], ['class' => 'btn btn-primary', 'target' => '_blank']); ?>
        </div>
    <?php endif; ?>

</div>
