<?php

namespace thefx\blocks\forms\search;

use thefx\blocks\models\blocks\BlockCategory;
use thefx\blocks\models\blocks\BlockItem;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * BlockCategorySearch represents the model behind the search form of `app\shop\entities\Block\BlockCategory`.
 */
class BlockCategorySearch extends BlockCategory
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'block_id', 'parent_id', 'lft', 'rgt', 'depth', 'create_user', 'update_user', 'public', 'sort'], 'integer'],
            [['title', 'path', 'anons', 'text', 'photo', 'photo_preview', 'date', 'create_date', 'update_date'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }


    public function formName()
    {
        return '';
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $commonFields = [
            'id',
            'title',
            'anons',
            'text',
            'parent_id',
            'block_id',
            'public',
            'anons',
            'date',
            'create_user',
            'create_date',
            'update_user',
            'update_date',
            'photo_preview',
            'photo',
            'sort',
        ];

        $this->load($params);

        $query1 = (new Query())
            ->select(array_merge($commonFields, [ new Expression('"folder" as type') ], ['`lft`']))
            ->from(BlockCategory::tableName());

        $query2 = (new Query())
            ->select(array_merge($commonFields, [ new Expression('"item" as type') ], ['`sort` AS `lft`']))
            ->from(BlockItem::tableName());

        $unionQuery = BlockCategory::find()
            ->from($query1->union($query2));

        if ($this->block_id && $this->title) {
            $unionQuery->andFilterWhere(['block_id' => $this->block_id]);
        } else {
            $unionQuery->andFilterWhere(['parent_id' => $this->parent_id]);
        }

        $unionQuery->andFilterWhere(['or',
            ['like', 'anons', trim($this->title)],
            ['like', 'title', trim($this->title)],
            ['like', 'text', trim($this->title)]]);

        $dataProvider = new ActiveDataProvider([
            'query' => $unionQuery,
//            'pagination' => [
//                'pageSize' => 20,
//            ],
//            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'type' => SORT_ASC,
                    'update_date' => SORT_DESC,
                    'create_date' => SORT_DESC,
//                    'id' => SORT_ASC
                ],
                'attributes' => [
                    'id',
                    'update_date',
                    'create_date',
                    'public',
                    'title',
                    'anons',
                    'date',
                    'sort',
                    'lft',
                    'type',
                ]
            ]
        ]);

        return $dataProvider;
    }
}
