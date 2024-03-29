<?php

namespace thefx\blocks\models\blocks\queries;

use thefx\blocks\models\blocks\Block;
use yii\db\ActiveQuery;
use yii\web\NotFoundHttpException;

/**
 * This is the ActiveQuery class for [[thefx\blocks\models\blocks\Block]].
 *
 * @see Block
 */
class BlockQuery extends ActiveQuery
{
    /**
     * @inheritdoc
     * @return Block[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Block|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function byPath($path)
    {
        return $this->andWhere(['path' => $path]);
    }

    /**
     * @param $alias
     * @return array|Block|null
     * @throws NotFoundHttpException
     */
    public function oneOrFail($alias)
    {
        if (($model = $this->where(['path' => $alias])->one()) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('Блок не найден');
    }
}
