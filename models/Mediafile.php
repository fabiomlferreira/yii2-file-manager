<?php

namespace fabiomlferreira\filemanager\models;

use Yii;
use yii\web\UploadedFile;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\imagine\Image;
use yii\helpers\Html;
use yii\helpers\Inflector;
use fabiomlferreira\filemanager\Module;
use fabiomlferreira\filemanager\models\Owners;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;

/**
 * This is the model class for table "filemanager_mediafile".
 *
 * @property integer $id
 * @property string $filename
 * @property string $type
 * @property string $url
 * @property string $alt
 * @property integer $size
 * @property string $description
 * @property string $thumbs
 * @property integer $created_at
 * @property integer $updated_at
 * @property Owners[] $owners
 * @property Tag[] $tags
 */
class Mediafile extends ActiveRecord
{
    public $file;
    
    public $moduleName = 'filemanager';

    public static $imageFileTypes = ['image/gif', 'image/jpeg', 'image/png'];

    /**
     * @var array|null
     */
    protected $tagIds = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $linkTags = function ($event) {
            if ($this->tagIds === null) {
                return;
            }
            if (!is_array($this->tagIds)) {
                $this->tagIds = [];
            }
            $whereIds = $models = $newTagIds = [];
            foreach ($this->tagIds as $tagId) {
                if (empty($tagId)) {
                    continue;
                }
                if (preg_match("/^\d+$/", $tagId)) {
                    $whereIds[] = $tagId;
                    continue;
                }
                // если tagId не число, то значит надо создать новый тег
                if (!$tag = Tag::findOne(['name' => $tagId])) {
                    $tag = new Tag();
                    $tag->name = $tagId;
                    if (!$tag->save()) {
                        continue;
                    }
                }
                $newTagIds[] = $tag->id;
                $models[] = $tag;
            }

            $this->unlinkAll('tags', true);
            if ($whereIds) {
                $models = array_merge($models, Tag::find()->where(['id' => $whereIds])->all());
            }
            foreach ($models as $model) {
                $this->link('tags', $model);
            }
            // что бы после сохранения в значение были новые теги
            $this->tagIds = array_merge($whereIds, $newTagIds);
        };

        $this->on(static::EVENT_AFTER_INSERT, $linkTags);
        $this->on(static::EVENT_AFTER_UPDATE, $linkTags);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'filemanager_mediafile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filename', 'type', 'url', 'size'], 'required'],
            [['url', 'alt', 'description', 'thumbs'], 'string'],
            [['created_at', 'updated_at', 'size'], 'integer'],
            [['filename', 'type'], 'string', 'max' => 255],
            [['file'], 'file'],
            [['tagIds'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Module::t('main', 'ID'),
            'filename' => Module::t('main', 'filename'),
            'type' => Module::t('main', 'Type'),
            'url' => Module::t('main', 'Url'),
            'alt' => Module::t('main', 'Alt attribute'),
            'size' => Module::t('main', 'Size'),
            'description' => Module::t('main', 'Description'),
            'thumbs' => Module::t('main', 'Thumbnails'),
            'created_at' => Module::t('main', 'Created'),
            'updated_at' => Module::t('main', 'Updated'),
            'tagIds' => Module::t('main', 'Tags'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwners()
    {
        return $this->hasMany(Owners::className(), ['mediafile_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])
            ->viaTable('filemanager_mediafile_tag', ['mediafile_id' => 'id']);
    }

    /**
     * @return array|null
     */
    public function getTagIds() {
        return $this->tagIds !== null ? $this->tagIds : array_map(function ($tag) {
            return $tag->id;
        }, $this->tags);
    }

    /**
     * @param $value
     */
    public function setTagIds($value) {
        $this->tagIds = $value;
    }

    public function beforeDelete()
    {
        if (parent::beforeDelete()) {

            foreach ($this->owners as $owner) {
                $owner->delete();
            }

            return true;
        } else {
            return false;
        }
    }

	public function afterDelete()
	{
		parent::afterDelete();
		Tag::removeUnusedTags();
	}

	public function afterSave($insert, $changedAttributes)
	{
		parent::afterSave($insert, $changedAttributes);
		Tag::removeUnusedTags();
	}

    /**
     * Save just uploaded file
     * @param array $routes routes from module settings
     * @param bool $rename
     * @return bool
     */
    public function saveUploadedFile(array $routes, $rename = false)
    {
        $year = date('Y', time());
        $month = date('m', time());
        $structure = "$routes[baseUrl]/$routes[uploadPath]/$year/$month";
        $basePath = Yii::getAlias($routes['basePath']);
        $absolutePath = "$basePath/$structure";

        // create actual directory structure "yyyy/mm"
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0777, true);
        }

        // get file instance
        $this->file = UploadedFile::getInstance($this, 'file');
        //if a file with the same name already exist append a number
        $counter = 0;
        do{
            if($counter==0)
                $filename = Inflector::slug($this->file->baseName).'.'. $this->file->extension;
            else{
                //if we don't want to rename we finish the call here
                if($rename == false)
                    return false;
                $filename = Inflector::slug($this->file->baseName). $counter.'.'. $this->file->extension;
            }
            $url = "$structure/$filename";
            $counter++;
        } while(self::findByUrl($url)); // checks for existing url in db

        // save original uploaded file
        $this->file->saveAs("$absolutePath/$filename");
        $this->filename = $filename;
        $this->type = $this->file->type;
        $this->size = $this->file->size;
        $this->url = $url;

        return $this->save();
    }
    
    /**
     * Save just the file passed
     * @param type $file
     * @param array $routes routes from module settings
     * @param bool $rename
     * @return boolean
     */
    public function saveCurrentFile($file, array $routes, $rename = false)
    {
        $year = date('Y', time());
        $month = date('m', time());
        $structure = "$routes[baseUrl]/$routes[uploadPath]/$year/$month";
        $basePath = Yii::getAlias($routes['basePath']);
        $absolutePath = "$basePath/$structure";

        // create actual directory structure "yyyy/mm"
        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0777, true);
        }

        // get file instance
        $this->file = $file;
        //if a file with the same name already exist append a number
        $counter = 0;
        do{
            if($counter==0)
                $filename = Inflector::slug($this->file->baseName).'.'. $this->file->extension;
            else{
                //if we don't want to rename we finish the call here
                if($rename == false)
                    return false;
                $filename = Inflector::slug($this->file->baseName). $counter.'.'. $this->file->extension;
            }
            $url = "$structure/$filename";
            $counter++;
        } while(self::findByUrl($url)); // checks for existing url in db

        // save original uploaded file
        $this->file->saveAs("$absolutePath/$filename");
        $this->filename = $filename;
        $this->type = $this->file->type;
        $this->size = $this->file->size;
        $this->url = $url;

        return $this->save();
    }
    
    
    /**
     * Function to optimize a image and reduce the size to a a maximum width/height
     * @param array $routes
     * @param type $quality
     * @param type $size this is the maximum size for width or height
     * @return boolean
     */
    public function optimizeOriginal(array $routes, $quality, $size)
    {
        if($this->isImage()){
            $basePath = Yii::getAlias($routes['basePath']);
            $originalFile = pathinfo($this->url);
            $dirname = $originalFile['dirname'];
            $filename = $originalFile['filename'];
            $extension = $originalFile['extension'];

            Image::$driver = [Image::DRIVER_GD2, Image::DRIVER_GMAGICK, Image::DRIVER_IMAGICK];

            $image = Image::getImagine()->open("$basePath/{$this->url}");
            if($size === null){
                $image->rotate($this->getOrientation("$basePath/{$this->url}"))->save("$basePath/{$this->url}", [
                    'quality' => $quality
                ]);
            }else{
                $image->rotate($this->getOrientation("$basePath/{$this->url}"))->thumbnail(new Box($size, $size))->save("$basePath/{$this->url}", [
                    'quality' => $quality
                ]); 
            }
            clearstatcache(false, "$basePath/{$this->url}"); //clear the cache for filesize work
            $this->size = filesize("$basePath/{$this->url}");
            return $this->save(false);
        }else
            return false;
    }
    
    /**
     * Return the orientation of an image
     * @param type $filename
     * @return int
     */
    private function getOrientation($filename) {
        $exif = @exif_read_data($filename);
        if($exif === null)
            return 0;
        $rotation = 0;
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3:
                    $rotation = 180;
                    break;
                case 6:
                    $rotation = 90;
                    break;
                case 8:
                    $rotation = -90;
                    break;
            }
        }
        return $rotation;
    }

    /**
     * Create thumbs for this image
     *
     * @param array $routes see routes in module config
     * @param array $presets thumbs presets. See in module config
     * @return bool
     */
    public function createThumbs(array $routes, array $presets)
    {
        $module = Module::getInstance();
        if($module===null)
            $module = \Yii::$app->getModule($this->moduleName);
        //is thumbnailOnTheFly is disable then create the thumbnails
        if($module->thumbnailOnTheFly == false){
            $thumbs = [];
            $basePath = $basePath = Yii::getAlias($routes['basePath']);
            $originalFile = pathinfo($this->url);
            $dirname = $originalFile['dirname'];
            $filename = $originalFile['filename'];
            $extension = $originalFile['extension'];

            Image::$driver = [Image::DRIVER_GD2, Image::DRIVER_GMAGICK, Image::DRIVER_IMAGICK];

            foreach ($presets as $alias => $preset) {
                $width = $preset['size'][0];
                $height = $preset['size'][1];
                $mode = (isset($preset['mode']) ? $preset['mode'] : ImageInterface::THUMBNAIL_OUTBOUND);

                $thumbUrl = "$dirname/" . $this->getThumbFilename($filename, $extension, $alias, $width, $height);

                $image = new Image();
                if(isset($preset['thumbnailBackgroundColor']))
                    $image::$thumbnailBackgroundColor = $preset['thumbnailBackgroundColor'];
                if(isset($preset['thumbnailBackgroundAlpha']))
                    $image::$thumbnailBackgroundAlpha = $preset['thumbnailBackgroundAlpha'];
                if(isset($preset['keepAspectRatio']) && $preset['keepAspectRatio'] == true){
                    $image = $image::getImagine()->open("$basePath/{$this->url}");
                    $image->thumbnail(new Box($width, $height), $mode)->save("$basePath/$thumbUrl"); 
                }else{
                    if(isset($preset['forceUpscale']) && $preset['forceUpscale'] == true){
                        $image::thumbnail("$basePath/{$this->url}", $width, $height, $mode)->resize(new Box($width, $height))->save("$basePath/$thumbUrl");
                    }else{
                        $image::thumbnail("$basePath/{$this->url}", $width, $height, $mode)->save("$basePath/$thumbUrl");
                    }
                }

                $thumbs[$alias] = $thumbUrl;
            }

            $this->thumbs = serialize($thumbs);
            $this->detachBehavior('timestamp');

            // create default thumbnail
            $this->createDefaultThumb($routes);

            return $this->save();
        }else{
            return true;
        }
    }

    /**
     * Create default thumbnail
     *
     * @param array $routes see routes in module config
     */
    public function createDefaultThumb(array $routes)
    {
        $originalFile = pathinfo($this->url);
        $dirname = $originalFile['dirname'];
        $filename = $originalFile['filename'];
        $extension = $originalFile['extension'];

        Image::$driver = [Image::DRIVER_GD2, Image::DRIVER_GMAGICK, Image::DRIVER_IMAGICK];

        $size = Module::getDefaultThumbSize();
        $width = $size[0];
        $height = $size[1];
        $thumbUrl = "$dirname/" . $this->getThumbFilename($filename, $extension, Module::DEFAULT_THUMB_ALIAS, $width, $height);
        $basePath = Yii::getAlias($routes['basePath']);
        Image::thumbnail("$basePath/{$this->url}", $width, $height)->save("$basePath/$thumbUrl");
    }

    /**
     * Add owner to mediafiles table
     *
     * @param int $owner_id owner id
     * @param string $owner owner identification name
     * @param string $owner_attribute owner identification attribute
     * @return bool save result
     */
    public function addOwner($owner_id, $owner, $owner_attribute)
    {
        $mediafiles = new Owners();
        $mediafiles->mediafile_id = $this->id;
        $mediafiles->owner = $owner;
        $mediafiles->owner_id = $owner_id;
        $mediafiles->owner_attribute = $owner_attribute;

        return $mediafiles->save();
    }

    /**
     * Remove this mediafile owner
     *
     * @param int $owner_id owner id
     * @param string $owner owner identification name
     * @param string $owner_attribute owner identification attribute
     * @return bool delete result
     */
    public static function removeOwner($owner_id, $owner, $owner_attribute)
    {
        $mediafiles = Owners::findOne([
            'owner_id' => $owner_id,
            'owner' => $owner,
            'owner_attribute' => $owner_attribute,
        ]);

        if ($mediafiles) {
            return $mediafiles->delete();
        }

        return false;
    }

    /**
     * @return bool if type of this media file is image, return true;
     */
    public function isImage()
    {
        return in_array($this->type, self::$imageFileTypes);
    }

    /**
     * @param $baseUrl
     * @return string default thumbnail for image
     */
    public function getDefaultThumbUrl($baseUrl = '')
    {
        if ($this->isImage()) {
            $module = Module::getInstance();
            if($module===null)
                $module = \Yii::$app->getModule($this->moduleName);
            $size = Module::getDefaultThumbSize();
            $width = $size[0];
            $height = $size[1];
            //is thumbnailOnTheFly is disable then create the thumbnails
            if($module->thumbnailOnTheFly == false){
                $originalFile = pathinfo($this->url);
                $dirname = $originalFile['dirname'];
                $filename = $originalFile['filename'];
                $extension = $originalFile['extension'];

                return "$dirname/" . $this->getThumbFilename($filename, $extension, Module::DEFAULT_THUMB_ALIAS, $width, $height);
            }else{
                return Yii::$app->thumbnail->url("$this->url", [
                    'thumbnail' => [
                        'width' =>  $width,
                        'height' => $height,
                    ],
                    'placeholder' => [
                        'width' =>  $width,
                        'height' => $height
                    ]
                ]);
            }
        }
        return "$baseUrl/images/file.png";
    }

    /**
     * @param $baseUrl
     * @return string default thumbnail for image
     */
    public function getDefaultUploadThumbUrl($baseUrl = '')
    {
        $module = Module::getInstance();
        if($module===null)
            $module = \Yii::$app->getModule($this->moduleName);
        $size = Module::getDefaultThumbSize();
        $width = $size[0];
        $height = $size[1];
        $originalFile = pathinfo($this->url);
        $dirname = $originalFile['dirname'];
        $filename = $originalFile['filename'];
        $extension = $originalFile['extension'];
        
        if($module->thumbnailOnTheFly == false){
            return "$dirname/" . $this->getThumbFilename($filename, $extension, Module::DEFAULT_THUMB_ALIAS, $width, $height);
        }else{
            return Yii::$app->thumbnail->url("$this->url", [
                    'thumbnail' => [
                        'width' =>  $width,
                        'height' => $height,
                    ],
                    'placeholder' => [
                        'width' =>  $width,
                        'height' => $height
                    ]
                ]);
        }
    }

	/**
	 * Returns thumbnail name
	 *
	 * @param $original
	 * @param $extension
	 * @param $alias
	 * @param $width
	 * @param $height
	 *
	 * @return string
	 */
	protected function getThumbFilename($original, $extension, $alias, $width, $height)
	{
		/** @var Module $module */
		$module = Module::getInstance();

		return strtr($module->thumbFilenameTemplate, [
			'{width}'     => $width,
			'{height}'    => $height,
			'{alias}'     => $alias,
			'{original}'  => $original,
			'{extension}' => $extension,
		]);
	}

    /**
     * @return array thumbnails
     */
    public function getThumbs()
    {
        return unserialize($this->thumbs) ?: [];
    }

    /**
     * @param string $alias thumb alias
     * @return string thumb url
     */
    public function getThumbUrl($alias)
    {
        $module = Module::getInstance();
        if($module===null)
            $module = \Yii::$app->getModule($this->moduleName);
        //if is to use the regular thumbnail generation
        if($module->thumbnailOnTheFly == false){
            $thumbs = $this->getThumbs();
           
            if ($alias === 'original') {
                return $this->url;
            }

            return !empty($thumbs[$alias]) ? $thumbs[$alias] : '';
        }else{
            $sizes = !empty($module->thumbs[$alias]) ? $module->thumbs[$alias] : '';
            if($sizes == '')
                return '';
            
            $width = $sizes['size'][0];
            $height = $sizes['size'][1];
            $mode = isset($sizes['mode']) ? $sizes['mode'] : ImageInterface::THUMBNAIL_OUTBOUND;
            return Yii::$app->thumbnail->url("$this->url", [
                'thumbnail' => [
                    'width' =>  $width,
                    'height' => $height,
                    'mode' => $mode 
                ],
                'placeholder' => [
                    'width' =>  $width,
                    'height' => $height
                ]
            ]);
        }
    }

    /**
     * Thumbnail image html tag
     *
     * @param string $alias thumbnail alias
     * @param array $options html options
     * @return string Html image tag
     */
    public function getThumbImage($alias, $options=[])
    {
        $url = $this->getThumbUrl($alias);

        if (empty($url)) {
            return '';
        }

        if (empty($options['alt'])) {
            $options['alt'] = $this->alt;
        }

        return Html::img($url, $options);
    }

    /**
     * @param Module $module
     * @return array images list
     */
    public function getImagesList(Module $module)
    {
        $thumbs = $this->getThumbs();
        $list = [];
        $originalImageSize = $this->getOriginalImageSize($module->routes);
        $list[$this->url] = Module::t('main', 'Original') . ' ' . $originalImageSize;

        foreach ($thumbs as $alias => $url) {
            $preset = $module->thumbs[$alias];
            $list[$url] = $preset['name'] . ' ' . $preset['size'][0] . ' × ' . $preset['size'][1];
        }
        return $list;
    }

    /**
     * Delete thumbnails for current image
     * @param array $routes see routes in module config
     */
    public function deleteThumbs(array $routes)
    {
        $basePath = Yii::getAlias($routes['basePath']);

        foreach ($this->getThumbs() as $thumbUrl) {
            if(is_file("$basePath/$thumbUrl")) {
            	unlink("$basePath/$thumbUrl");
            }
        }
        
        $defaultThumbPath = "$basePath/{$this->getDefaultThumbUrl()}";
        if(is_file($defaultThumbPath)) {
            unlink($defaultThumbPath);
        }
    }

    /**
     * Delete file
     * @param array $routes see routes in module config
     * @return bool
     */
    public function deleteFile(array $routes)
    {
        $basePath = Yii::getAlias($routes['basePath']);
        $filePath = "$basePath/{$this->url}";
        return is_file($filePath) ? unlink($filePath) : false;
    }

    /**
     * @return int last changes timestamp
     */
    public function getLastChanges()
    {
        return !empty($this->updated_at) ? $this->updated_at : $this->created_at;
    }

    /**
     * This method wrap getimagesize() function
     * @param array $routes see routes in module config
     * @param string $delimiter delimiter between width and height
     * @return string image size like '1366x768'
     */
    public function getOriginalImageSize(array $routes, $delimiter = ' × ')
    {
        $imageSizes = $this->getOriginalImageSizes($routes);
        return "$imageSizes[0]$delimiter$imageSizes[1]";
    }

    /**
     * This method wrap getimagesize() function
     * @param array $routes see routes in module config
     * @return array
     */
    public function getOriginalImageSizes(array $routes)
    {
        $basePath = Yii::getAlias($routes['basePath']);
        return getimagesize("$basePath/{$this->url}");
    }

    /**
     * @return string file size
     */
    public function getFileSize()
    {
        Yii::$app->formatter->sizeFormatBase = 1000;
        return Yii::$app->formatter->asShortSize($this->size, 0);
    }

    /**
     * Find model by url
     *
     * @param $url
     * @return static
     */
    public static function findByUrl($url)
    {
        return self::findOne(['url' => $url]);
    }

    /**
     * Search models by file types
     * @param array $types file types
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function findByTypes(array $types)
    {
        return self::find()->filterWhere(['in', 'type', $types])->all();
    }

    public static function loadOneByOwner($owner, $owner_id, $owner_attribute)
    {
        $owner = Owners::findOne([
            'owner' => $owner,
            'owner_id' => $owner_id,
            'owner_attribute' => $owner_attribute,
        ]);

        if ($owner) {
            return $owner->mediafile;
        }

        return false;
    }
}