<?php
namespace zhangzhaowy\avatar;

use Yii;
use yii\base\Action;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;

/**
 * 头像上传组件
 */
class CropAction extends Action
{
    public $config = [];
    
    public function init()
    {
        $config = [
            // 上传根目录（注：目录前后不能加"/"）
            'saveRootPath' => 'uploads/avatar',
            // 定义上传目录命名格式：
            //     0-直接放到根目录
            //     1-YYYYMMDD
            //     2-直接指定子目录
            //     3-指定子目录+YYYYMMDD
            'savePathFormat' => 1,
            // 上传子目录（注：目录前后不能加"/"）
            'savePath' => '',
            // 定义上传文件命名格式：
            //     0-原名保存
            //     1-指定名称
            //     2-自动生成唯一字符串
            'saveFilenameFormat' => 2,
            // 文件名
            'saveFilename' => '',
            // 是否需要缩略图
            'thumbnail' => false,
            // 缩略图命名格式(name => 原名+name，suffix => 原名+suffix)
            'thumbnailFormat' => 'suffix',
            // 定义缩略图尺寸
            'imageSize' => [
                // name => 图片尺寸名，width => 图片宽度，height => 图片高度，suffix => 图片后缀名
                ['name' => 'small', 'width' => 50, 'height' => 50, 'suffix' => 's'],
                ['name' => 'middle', 'width' => 100, 'height' => 100, 'suffix' => 'm'],
                ['name' => 'big', 'width' => 200, 'height' => 200, 'suffix' => 'b'],
            ],
        ];
        $this->config = ArrayHelper::merge($config, $this->config);
        parent::init();
    }
    
    public function run()
    {
        $model = new UploadForm();
        
        if (Yii::$app->request->isPost) {
            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');
            $post = Yii::$app->request->post();
            $model->avatarData = $post['UploadForm']['avatarData'];
            $model->config = $this->config;
            if ($model->upload()) {
                // 文件上传成功
                return json_encode([
                    'state' => 200,
                    'message' => '上传成功！',
                    'result' => $model->imageUrl,
                ]);
            }
        }
    }
}