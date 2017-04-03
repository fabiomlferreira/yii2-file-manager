Yii2 file manager
================
This module provide interface to collect and access all mediafiles in one place. This module have an option to enable on the fly thumbnail generation

Features
------------
* Integrated with TinyMCE editor.
* Automatically create actually directory for uploaded files like "2017/03".
* Automatically create thumbs for uploaded images or on the fly
* Unlimited number of sets of miniatures
* All media files are stored in a database that allows you to attach to your object does not link to the image, and the id of the media file. This provides greater flexibility since in the future will be easy to change the size of thumbnails.
* If your change thumbs sizes, your may resize all existing thumbs in settings.

Screenshots
------------
<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-upload.png">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-image-select.png">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-index.png">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-files-in-admin.png">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-settings.png">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-selected-image.png">

<img src="http://zabolotskikh.com/wp-content/uploads/2014/12/yii2-filemanager-selected-image-without-input.png">

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist fabiomlferreira/yii2-file-manager "*"
```

or add

```
"fabiomlferreira/yii2-file-manager": "*"
```

to the require section of your `composer.json` file.

Apply migration
```sh
yii migrate --migrationPath=vendor/fabiomlferreira/yii2-file-manager/migrations
```

Configuration:

```php
'modules' => [
    'filemanager' => [
        'class' => 'fabiomlferreira\filemanager\Module',
        'rename' => true, //enable upload multiple images with the same name, this will rewrite the images name
        'optimizeOriginalImage' => true, //Optimize the original image
        'maxSideSize' => 1200, //limit the maximum size for the original image only work if 'optimizeOriginalImage' => true
        'originalQuality' => 80, // quality for the original image  only work if 'optimizeOriginalImage' => true
        'thumbnailOnTheFly' => false,  //if is true will generate the thumbnails on the fly, is required that you set the component "thumbnail"
        // Upload routes
        'routes' => [
            // Base absolute path to web directory
            'baseUrl' => '',
            // Base web directory url
            'basePath' => '@frontend/web', //for yii2 advanced template
            // Path for uploaded files in web directory
            'uploadPath' => 'uploads',
        ],
        // Thumbnails info
        'thumbs' => [
            'small' => [
                'name' => 'Small',
                'size' => [100, 100],
            ],
            'medium' => [
                'name' => 'Regular',
                'size' => [300, 200],
            ],
            'large' => [
                'name' => 'Large',
                'size' => [500, 400],
            ],
        ],
    ],
],
```
By default, thumbnails are resized in "outbound" or "fill" mode. To switch to "inset" or "fit" mode, use `mode` parameter and provide. Possible values: `outbound` (`\Imagine\Image\ImageInterface::THUMBNAIL_OUTBOUND`) or `inset` (`\Imagine\Image\ImageInterface::THUMBNAIL_INSET`):

```php
'thumbs' => [
    'small' => [
        'name' => 'Small',
        'size' => [100, 100],
    ],
    'medium' => [
        'name' => 'Regular',
        'size' => [300, 200],
    ],
    'large' => [
        'name' => 'Large',
        'size' => [500, 400],
        'mode' => \Imagine\Image\ImageInterface::THUMBNAIL_INSET
    ],
],
```

If you set the 'thumbnailOnTheFly' to true you need to configure the component Thumbnail

```php
'components' => [
    'thumbnail' => [
        'class' => 'fabiomlferreira\filemanager\Thumbnail',
        'cachePath' => '@webroot/assets/thumbnails', // path for the folder for temporary thumbnails 
        'basePath' => '@webroot',
        'cacheExpire' => 2592000, // time that the thumbnails keeps in cache
        'options' => [
            'placeholder' => [
                'type' => fabiomlferreira\filemanager\Thumbnail::PLACEHOLDER_TYPE_JS,
                'backgroundColor' => '#f5f5f5',
                'textColor' => '#cdcdcd',
                'text' => 'Ooops',
                'random' => true,
                'cache' => false,
            ],
            'quality' => 75
        ]
    ],
]
```
[Read the Documentation for Thumbnail component](THUMBNAIL.md)

Usage
------------
Simple standalone field:

```php
use fabiomlferreira\filemanager\widgets\FileInput;

echo $form->field($model, 'original_thumbnail')->widget(FileInput::className(), [
    'buttonTag' => 'button',
    'buttonName' => 'Browse',
    'buttonOptions' => ['class' => 'btn btn-default'],
    'options' => ['class' => 'form-control'],
    // Widget template
    'template' => '<div class="input-group">{input}<span class="input-group-btn">{button}</span></div>',
    // Optional, if set, only this image can be selected by user
    'thumb' => 'original',
    // Optional, if set, in container will be inserted selected image
    'imageContainer' => '.img',
    // Default to FileInput::DATA_URL. This data will be inserted in input field
    'pasteData' => FileInput::DATA_URL,
    // JavaScript function, which will be called before insert file data to input.
    // Argument data contains file data.
    // data example: [alt: "some description", description: "123", url: "/uploads/2017/03/vedma-100x100.jpeg", id: "45"]
    'callbackBeforeInsert' => 'function(e, data) {
        console.log( data );
    }',
]);

echo FileInput::widget([
    'name' => 'mediafile',
    'buttonTag' => 'button',
    'buttonName' => 'Browse',
    'buttonOptions' => ['class' => 'btn btn-default'],
    'options' => ['class' => 'form-control'],
    // Widget template
    'template' => '<div class="input-group">{input}<span class="input-group-btn">{button}</span></div>',
    // Optional, if set, only this image can be selected by user
    'thumb' => 'original',
    // Optional, if set, in container will be inserted selected image
    'imageContainer' => '.img',
    // Default to FileInput::DATA_IDL. This data will be inserted in input field
    'pasteData' => FileInput::DATA_ID,
    // JavaScript function, which will be called before insert file data to input.
    // Argument data contains file data.
    // data example: [alt: "Ведьма с кошкой", description: "123", url: "/uploads/2014/12/vedma-100x100.jpeg", id: "45"]
    'callbackBeforeInsert' => 'function(e, data) {
        console.log( data );
    }',
]);
```

With TinyMCE:
```php
use fabiomlferreira\filemanager\widgets\TinyMCE;

<?= $form->field($model, 'content')->widget(TinyMCE::className(), [
    'clientOptions' => [
           'language' => 'ru',
        'menubar' => false,
        'height' => 500,
        'image_dimensions' => false,
        'plugins' => [
            'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code contextmenu table',
        ],
        'toolbar' => 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | code',
    ],
]); ?>
```

In model you must set mediafile behavior like this example:

```php
use fabiomlferreira\filemanager\behaviors\MediafileBehavior;

public function behaviors()
{
    return [
        'mediafile' => [
            'class' => MediafileBehavior::className(),
            'name' => 'post',
            'attributes' => [
                'thumbnail',
            ],
        ]
    ];
}
```

Than, you may get mediafile from your owner model.
See example:

```php
use fabiomlferreira\filemanager\models\Mediafile;

$model = Post::findOne(1);
$mediafile = Mediafile::loadOneByOwner('post', $model->id, 'thumbnail');

// Ok, we have mediafile object! Let's do something with him:
// return url for small thumbnail, for example: '/uploads/2014/12/flying-cats.jpg'
echo $mediafile->getThumbUrl('small');
// return image tag for thumbnail, for example: '<img src="/uploads/2017/03/flying-dogs.jpg" alt="ypload">'
echo $mediafile->getThumbImage('small'); // return url for small thumbnail
```
