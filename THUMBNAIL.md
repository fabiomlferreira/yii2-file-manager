# Yii2 image thumbnail

Create image thumbnails use Imagine. Thumbnail created and cached automatically.
It allows you to create placeholder with service [http://placehold.it/](http://placehold.it/) or holder.js.

#### Features:
- Easy to use
- Use Imagine
- TinyPng compression
- Automaticly thumbnails caching
- Cache sorting to subdirectories
- Caching placeholder from URL (placehold.it)
- Random placeholder color
- Use placehold.it & holder.js

## Installation

### Composer

Installed with the Yii2-file-manager.


### Config

Attach the component in your config file:

```php
'components' => [
    'thumbnail' => [
        'class' => 'fabiomlferreira\filemanager\Thumbnail',
    ],
],
```

#### Parameters
- string `basePath` = `null` - Base path
- string `prefixPath` = `null` - Prefix path
- string `cachePath` = `@runtime/thumbnails` - Cache path alias
- integer `cacheExpire` = `604800` - Cache expire time
- array `options` - Other options (placeholder/quality/additional compression)

#### Default options:

```php
'options' => [
    'placeholder' => [
        'type' => fabiomlferreira\filemanager\Thumbnail::PLACEHOLDER_TYPE_URL,
        'backgroundColor' => '#f5f5f5',
        'textColor' => '#cdcdcd',
        'textSize' => 30,
        'text' => 'Ooops!',
        'random' => false
        'cache' => true
    ],
    'quality' => 92,
    'tinyPng' => [
        'apiKey' => null
    ]
]
```

#### Attention
```
Cache only: PLACEHOLDER_TYPE_URL
If both "random" and "cache" are enabled, each color will be cached
```

#### Placeholder type
- 1. fabiomlferreira\filemanager\Thumbnail::PLACEHOLDER_TYPE_JS - holder.js
- 2. fabiomlferreira\filemanager\Thumbnail::PLACEHOLDER_TYPE_URL - get placeholder by url

## Using

### Get cache image
```php
echo Yii::$app->thumbnail->img($file, $params, $options);
```
This method returns Html::img()

#### Parameters
- string `$file` required - Image file path
- array `$params` - Image manipulation methods. See Methods
- array `$options` - options for Html::img()

#### For example:
```php
<?= Yii::$app->thumbnail->img(IMAGE_SRC, [
    'thumbnail' => [
        'width' => 320,
        'height' => 230,
    ],
    'placeholder' => [
        'width' => 320,
        'height' => 230
    ]
]); ?>
```

### Get cache image url
```php
echo Yii::$app->thumbnail->url($file, $params);
```
This method returns cache image url

#### Parameters
- string `$file` required - Image file path
- array `$params` - Image manipulation methods. See Methods

#### For example:
```php
<?= Yii::$app->thumbnail->url(IMAGE_SRC, [
    'thumbnail' => [
        'width' => 320,
        'height' => 230,
    ],
    'placeholder' => [
        'width' => 320,
        'height' => 230
    ]
]); ?>
```

### Get placeholder image
```php
echo Yii::$app->thumbnail->placeholder($params, $options);
```
This method returns Html::img()

#### Parameters
- array `$params` required - Placeholder options. See Placeholder method
- array `$options` - options for Html::img()

#### For example:
```php
<?= Yii::$app->thumbnail->placeholder([
    'width' => 320,
    'height' => 230
]); ?>
```

## Method

### Resize
```php
'resize' => [
    'width' => 320,
    'height' => 200
]
```
#### Parameters
- integer `width` required - New width
- integer `height` required - New height

### Crop
```php
'crop' => [
    'width' => 250,
    'height' => 200,
]
```
#### Parameters
- integer `width` required - New width
- integer `height` required - New height
- integer `x` = `0` - X start crop position
- integer `y` = `0` - Y start crop position

### Thumbnail
```php
'thumbnail' => [
    'width' => 450,
    'height' => 250,
]
```
#### Parameters
- integer `width` required - New width
- integer `height` required - New height
- string `mode` = `THUMBNAIL_OUTBOUND` - Thumbnail mode `THUMBNAIL_OUTBOUND` or `THUMBNAIL_INSET`

### Placeholder
```php
'placeholder' => [
    'width' => 450,
    'height' => 250,
]
```
This method return image placeholder if image file doesn't exist.

#### Parameters
- integer `width` required - Placeholder image width
- integer `height` required - Placeholder image height
- string `backgroundColor` = `#f5f5f5` - Background color
- string `textColor` = `#cdcdcd` - Text color
- string `text` = `Ooops!` - Text
- string `random` = `false` - Random color
- string `cache` = `true` - Cache placeholder


#### Attention
```
Cache only: PLACEHOLDER_TYPE_URL
If both "random" and "cache" are enabled, each color will be cached
```

### Watermark
```php
'watermark' => [
    'image' => IMAGE_SRC
    'posX' => 0,
    'posY' => 0,
    'width' => 50,
    'height' => 50
]
```

#### Parameters
- string `image` required - watermark path
- integer `posX` required - X/-X watermark position
- integer `posY` required - Y/-Y watermark position
- integer `width` - Watermark width
- integer `height` - Watermark height
- string `mode` = `THUMBNAIL_OUTBOUND` - Thumbnail mode `THUMBNAIL_OUTBOUND` or `THUMBNAIL_INSET`

### Compression (TinyPng)

```php
'compress' => true
```