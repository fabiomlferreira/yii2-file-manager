<?php

namespace fabiomlferreira\filemanager\assets;

/**
 * Class AssetBundle
 * @package fabiomlferreira\filemanager\assets
 */
class ThumbnailAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@bower/holderjs';

    public $js = [
        'holder.min.js',
    ];
}