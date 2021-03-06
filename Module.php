<?php

namespace fabiomlferreira\filemanager;

use Yii;

class Module extends \yii\base\Module
{
    const DEFAULT_THUMB_ALIAS = 'fm';

    public $controllerNamespace = 'fabiomlferreira\filemanager\controllers';

    /**
     *  Set true if you want to rename files if the name is already in use 
     * @var boolean
     */
    public $rename = false;
    
     /**
     *  Set true to enable autoupload
     * @var boolean
     */
    public $autoUpload = false;
    
    /**
     *  Set true to enable optimization of original image
     * @var boolean
     */
    public $optimizeOriginalImage = false;
    
    /**
     * Maximum image quality
     * @var integer 
     */
    public $originalQuality = 80;
    
    /**
     * Maximum size in pixeis for a side of the image, if 0 don't change image size
     * @var integer 
     */
    public $maxSideSize = null;

    /**
     * Thumbnails name template.
     * Possible vars: {original}, {width}, {height}, {alias}, {extension}
     * Note: "fm" alias is reserved for default thumbnails
     * @var string
     */
    public $thumbFilenameTemplate = '{original}-{alias}.{extension}';
    
    /**
     * @var array upload routes
     */
    public $routes = [
        // base absolute path to web directory
        'baseUrl' => '',
        // base web directory url
        'basePath' => '@webroot',
        // path for uploaded files in web directory
        'uploadPath' => 'uploads',
    ];

    /**
     * @var array thumbnails info
     */
    public $thumbs = [
        'small' => [
            'name' => 'Small size',
            'size' => [120, 80],
        ],
        'medium' => [
            'name' => 'Medium size',
            'size' => [400, 300],
        ],
        'large' => [
            'name' => 'Large size',
            'size' => [800, 600],
        ],
    ];
    
    /**
     * If we instead of using multiple thumbnails create them on the fly with the
     * size that we want
     * @var boolean
     */
    public $thumbnailOnTheFly = false;

    /**
     * @var array default thumbnail size, using in filemanager view.
     */
    private static $defaultThumbSize = [128, 128];

    public function init()
    {
        parent::init();
        $this->registerTranslations();
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/filemanager/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@vendor/fabiomlferreira/yii2-file-manager/messages',
            'fileMap' => [
                'modules/filemanager/main' => 'main.php',
            ],
        ];
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        if (!isset(Yii::$app->i18n->translations['modules/filemanager/*'])) {
            return $message;
        }

        return Yii::t("modules/filemanager/$category", $message, $params, $language);
    }

    /**
     * @return array default thumbnail size. Using in filemanager view.
     */
    public static function getDefaultThumbSize()
    {
        return self::$defaultThumbSize;
    }
}
