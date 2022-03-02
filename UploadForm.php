<?php
namespace zhangzhaowy\avatar;

use Yii;
use yii\base\Model;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;
    
    public $avatarData;
    
    public $config;
    
    public $imageUrl;

    public $_lastError;
    // 裁切完成后的原始图片尺寸
    public $originalSize = [];
    
    public function rules()
    {
        return [
            [['avatarData','config'], 'required'],
            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg, gif'],
        ];
    }

    /**
     * 图片上传
     * @throws \Exception
     * @return boolean
     */
    public function upload()
    {
        try{
            // 获取保存路径
            $path = $this->getSaveDir();
            // 创建目录
            if(!$this->mkDirs($path)){
                throw new \Exception('上传目录生成失败！');
            }
            
            // 图片裁剪
            $originalImage = $this->crop();
            // 图片地址
            $originalImageUrl = $this->getImageUrl($path);
            // 图片移动到对应目录
            if (!imagepng($originalImage, $originalImageUrl)) {
                throw new \Exception('图片上传失败！');
            }

            // 图片缩略图
            $this->thumbnail($originalImage, $originalImageUrl);
            
            // 图片地址
            $this->imageUrl = '/' . $originalImageUrl;
            return true;
        }catch (\Exception $e){
            $this->_lastError = $e->getMessage();
            return false;
        }

    }

    // 获取保存的目录
    public function getSaveDir()
    {
        // 根目录
        $path = $this->config['saveRootPath'] . '/';
        // 子目录
        switch ($this->config['savePathFormat']) {
            case 1:
                // YYYYMMDD
                $path .= date('Ymd') . '/';
                break;
            case 2:
                // 直接指定子目录
                if (!empty($this->config['savePath'])) {
                    $path .= $this->config['savePath'] . '/';
                }
                break;
            case 3:
                // 指定子目录+YYYYMMDD
                if (!empty($this->config['savePath'])) {
                    $path .= $this->config['savePath'] . '/';
                }
                $path .= date('Ymd') . '/';
                break;
            default:
                // default
                break;
        }
        return $path;
    }

    // 创建目录
    public function mkDirs($dir, $mode = 0777){
        if (!is_dir($dir)){
            if(!$this->mkDirs(dirname($dir), $mode)){
                return false;
            }
            if(!mkdir($dir, $mode)){
                return false;
            }
        }
        return true;
    }
    
    /**
     * bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
     * $dst_image：新建的图片
     * $src_image：需要载入的图片
     * $dst_x：设定需要载入的图片在新图中的x坐标
     * $dst_y：设定需要载入的图片在新图中的y坐标
     * $src_x：设定载入图片要载入的区域x坐标
     * $src_y：设定载入图片要载入的区域y坐标
     * $dst_w：设定载入的原图的宽度（在此设置缩放）
     * $dst_h：设定载入的原图的高度（在此设置缩放）
     * $src_w：原图要载入的宽度
     * $src_h：原图要载入的高度
     */
    
    public function crop()
    {     
        $data = json_decode($this->avatarData);
        $size = getimagesize($this->imageFile->tempName);
        
        switch ($size['mime']) {
            case 'image/gif':
                $src_img = imagecreatefromgif($this->imageFile->tempName);
                break;
        
            case 'image/jpeg':
                $src_img = imagecreatefromjpeg($this->imageFile->tempName);
                break;
            
            case 'image/jpg':$dm = imagecreatefromjpeg($this->imageFile->tempName);
                break;
        
            case 'image/png':
                $src_img = imagecreatefrompng($this->imageFile->tempName);
                break;
        }
        
                
        $size_w = $size[0]; // natural width
        $size_h = $size[1]; // natural height
        
        $src_img_w = $size_w;
        $src_img_h = $size_h;
        
        $tmp_img_w = $data->width;
        $tmp_img_h = $data->height;
        // 生成缩略图中最大的图 尺寸
        // $maxSize = $this->getMaxImageSize();
        // $dst_img_w = empty($maxSize) ? 200 : $maxSize['width'];
        // $dst_img_h = empty($maxSize) ? 200 : $maxSize['height'];
        // 生成裁切大小的图 尺寸
        $dst_img_w = $data->width;
        $dst_img_h = $data->height;
        // 记录最终的尺寸
        $this->originalSize = ['width' => $dst_img_w, 'height' => $dst_img_h];
        
        $src_x = $data->x;
        $src_y = $data->y;
        
        if ($src_x <= -$tmp_img_w || $src_x > $src_img_w) {
            $src_x = $src_w = $dst_x = $dst_w = 0;
        } else if ($src_x <= 0) {
            $dst_x = -$src_x;
            $src_x = 0;
            $src_w = $dst_w = min($src_img_w, $tmp_img_w + $src_x);
        } else if ($src_x <= $src_img_w) {
            $dst_x = 0;
            $src_w = $dst_w = min($tmp_img_w, $src_img_w - $src_x);
        }
        
        if ($src_w <= 0 || $src_y <= -$tmp_img_h || $src_y > $src_img_h) {
            $src_y = $src_h = $dst_y = $dst_h = 0;
        } else if ($src_y <= 0) {
            $dst_y = -$src_y;
            $src_y = 0;
            $src_h = $dst_h = min($src_img_h, $tmp_img_h + $src_y);
        } else if ($src_y <= $src_img_h) {
            $dst_y = 0;
            $src_h = $dst_h = min($tmp_img_h, $src_img_h - $src_y);
        }
        
        $ratio = $tmp_img_w / $dst_img_w;
        $dst_x /= $ratio;
        $dst_y /= $ratio;
        $dst_w /= $ratio;
        $dst_h /= $ratio;
        
        $dst_img = imagecreatetruecolor($dst_img_w, $dst_img_h);
        imagefill($dst_img, 0, 0, imagecolorallocatealpha($dst_img, 0, 0, 0, 127));
        imagesavealpha($dst_img, true);
        $result = imagecopyresampled($dst_img, $src_img, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);        
        
        if ($result) {
            return $dst_img;
        } else {
            return null;
        }
    }

    // 获取缩略图中最大尺寸
    public function getMaxImageSize()
    {
        // 最大图片的宽高
        $max = [];
        foreach ((array)$this->config['imageSize'] as $info) {
            // 判断是否定义宽高
            if (!isset($info['width']) || !isset($info['height'])) {
                continue;
            }
            // 计算面积
            $area = intval($info['width']) * intval($info['height']);
            $maxArea = empty($max) ? 0 : intval($max['width']) * intval($max['height']);
            // 最大面积图片
            if ($area > $maxArea) {
                $max = $info;
            }
        }
        return $max;
    }

    // 获取图片地址
    public function getImageUrl($path)
    {
        // 图片名称定义
        $saveFilename = '';
        switch ($this->config['saveFilenameFormat']) {
            case 1:
                // 指定名称
                $saveFilename = $this->config['saveFilename'];
                break;
            case 2:
                // 自动生成
                $saveFilename = uniqid() . '.' . $this->imageFile->getExtension();
                break;
            default:
                // 原名
                $saveFilename = $this->imageFile->name;
                break;
        }
        return $path . $saveFilename;
    }

    /**
     * 生成缩略图
     * @params $image  object  图片资源
     * @params $imageUrl  string  图片地址
     */
    public function thumbnail($image, $imageUrl)
    {
        // 判断是否需要生成缩略图
        if (isset($this->config['thumbnail']) && $this->config['thumbnail']) {
            // 命名规则
            $format = $this->config['thumbnailFormat'];
            // 目录信息
            $imageUrlInfo = pathinfo($imageUrl);
            // 图片名称（不含后缀）
            $imageName = basename($imageUrl, '.' . $imageUrlInfo['extension']);
            // 获取原始图片的大小
            // 缩略图列表
            foreach ($this->config['imageSize'] as $info) {
                // 缩略图地址
                $targetUrl = $imageUrlInfo['dirname'] . '/' . $imageName . '_' . $info[$format] . '.' . $imageUrlInfo['extension'];
                // 生成缩略图
                $targetImage = imagecreatetruecolor($info['width'], $info['height']);
                imagecopyresampled($targetImage, $image, 0, 0, 0, 0, $info['width'], $info['height'], $this->originalSize['width'], $this->originalSize['height']);
                //图片移动到对应目录
                if (!imagepng($image, $targetUrl)) {
                    throw new \Exception('缩略图生成失败！');
                }
            }
        }

        return true;
    }
}