<?php

namespace fabiomlferreira\filemanager\models;

use Yii;
use yii\data\ActiveDataProvider;


/**
 *
 */
class MediafileSearch extends Mediafile
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tagIds'], 'safe'],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->orderBy('created_at DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->tagIds) {
            $query->joinWith('tags')->andFilterWhere(['in', Tag::tableName() . '.id', $this->tagIds]);
        }

        return $dataProvider;
    }
}
