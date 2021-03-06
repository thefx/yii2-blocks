<?php

namespace thefx\blocks\forms\search;

use thefx\blocks\models\blocks\BlockItem;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BlockItemSearch represents the model behind the search form of `app\shop\entities\Block\BlockItem`.
 */
class BlockItemSearch extends BlockItem
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'parent_id', 'public', 'sort', 'create_user', 'update_user'], 'integer'],
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BlockItem::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'date' => $this->date,
            'parent_id' => $this->parent_id,
            'public' => $this->public,
            'sort' => $this->sort,
            'create_user' => $this->create_user,
            'create_date' => $this->create_date,
            'update_user' => $this->update_user,
            'update_date' => $this->update_date,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'path', $this->path])
            ->andFilterWhere(['like', 'anons', $this->anons])
            ->andFilterWhere(['like', 'text', $this->text])
            ->andFilterWhere(['like', 'photo', $this->photo])
            ->andFilterWhere(['like', 'photo_preview', $this->photo_preview]);

        return $dataProvider;
    }
}
