<?php

namespace fabiomlferreira\filemanager\models;

use Yii;

use fabiomlferreira\filemanager\Module;


/**
 * This is the model class for table "filemanager_tag".
 *
 * @property integer $id
 * @property string $name
 *
 * relations
 * @property Mediafile[] $mediafiles
 */
class Tag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'filemanager_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => Module::t('main', 'Tag'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMediafiles() {
        return $this->hasMany(Mediafile::className(), ['id' => 'mediafile_id'])
            ->viaTable('filemanager_mediafile_tag', ['tag_id' => 'id']);
    }

	/**
	 * Removes unused tags
	 *
	 * @return int
	 * @throws \yii\db\Exception
	 */
	public static function removeUnusedTags()
	{
		return Yii::$app->db->createCommand(
			'DELETE filemanager_tag
			FROM
				filemanager_tag
			LEFT JOIN filemanager_mediafile_tag ON id = tag_id
			WHERE
				ISNULL(mediafile_id)'
		)->execute();
	}
}
