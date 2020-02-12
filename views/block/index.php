<?php

use thefx\blocks\models\blocks\Block;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\modules\admin\forms\BlockSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blocks';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="block-index">

<!--    --><?php //Pjax::begin(); ?>

    <p>
        <?= Html::a('Добавить блок', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'id',
                'headerOptions' => ['style' => 'width:85px; text-align:center'],
                'contentOptions' => ['style' => 'width:85px; text-align:center'],
            ],
            [
                'attribute' => 'title',
                'format' => 'html',
                'value' => static function(Block  $model) {
                    return Html::a($model->title, ['block/view', 'id' => $model->id]);
                },
            ],
            'title',
            'path',
//            'table',
//            'template',
            //'pagination',
            //'create_user',
            //'create_date',
            //'update_user',
            //'update_date',

//            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]) ?>

<!--    --><?php //Pjax::end(); ?>

</div>
