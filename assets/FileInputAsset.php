<?php

namespace fabiomlferreira\filemanager\assets;

use yii\web\AssetBundle;

class FileInputAsset extends AssetBundle
{
    public $sourcePath = '@vendor/fabiomlferreira/yii2-file-manager/assets/source';

    public $js = [
        'js/fileinput.js',
    ];

    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        'yii\web\JqueryAsset',
        'fabiomlferreira\filemanager\assets\ModalAsset',
    ];
}
