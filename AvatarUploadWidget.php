<?php
namespace zhangzhaowy\avatar;

use Yii;
use yii\bootstrap\Widget;
use zhangzhaowy\avatar\assets\AvatarAsset;
use yii\base\Object;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use yii\web\View;

class AvatarUploadWidget extends Widget
{
    public $options = ['class' => 'form-control'];
    public $imageOptions = [];
    
    // 头像地址
    public $imageUrl = '';
    // 定义文件上传form表单的action
    public $saveAction = 'crop';

    public function run()
    {
        parent::init();
        $this->registerClientScript();

        $model = new UploadForm();
        return $this->render('upload', [
            'saveAction' => $this->saveAction,
            'model' => $model,
            'imageUrl' => $this->imageUrl,
        ]);
    }

    public function registerClientScript()
    {
        $this->view = $this->getView();
        AvatarAsset::register($this->view);
        $script = "var def_pic = $('#'+target_tip).val();
        var def_pic1 = '" . $this->imageUrl . "';

        if(def_pic==''){
            $('#pre_avatar').attr('src',def_pic1);
        }else{
            $('#pre_avatar').attr('src',''+def_pic);
        }
        $('#pre_avatar').on('click',function(){
            $('.avatar-view').click();
        });";
        $this->view->registerJs($script, View::POS_READY);
    }

}