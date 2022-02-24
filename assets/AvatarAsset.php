<?php
namespace daimakuai\avatar\assets;

use Yii;
use yii\web\AssetBundle;

/**
 * 定义前端需要加载的资源文件
 */
class AvatarAsset extends AssetBundle
{
    public $css = [
        'css/cropper.min.css',
        'css/main.css',
        'css/site.css'
    ];
    
    public $js = [
        'js/cropper.min.js',
        'js/main.js',
        'js/site.js'
    ];
    
    public $depends = [
        'yii\web\YiiAsset',
    ];
    
    /**
     * 初始化
     * 定义sourcePath
     */
    public function init()
    {
        $this->sourcePath = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR . 'statics';
    }
}