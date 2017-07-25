<?php
namespace Admin\Model;
use Think\Model;
use Think\Upload;
use Think\Image;

class FileModel extends Model{
    
    //开启设置虚拟模型,虚拟模型是指虽然是模型类，但并不会真正的操作数据库的模型。
    protected $autoCheckFields = false;
    
    //图片上传（将图片上传到本地文件夹）
    public function image() {
    	$Upload = new Upload();
    	$Upload->rootPATH = C('UPLOAD_PATH');
    	$Upload->maxSize = C('Image_Max_Size');//1M，最大允许上传的图片大小
    	$info = $Upload->upload();
    	if($info){
    		$savePath = $info['Filedata']['savepath'];
    		$saveName = $info['Filedata']['savename'];
    		$imgPath = C('UPLOAD_PATH').$savePath.$saveName;
    		//$image = new Image();
    		//$image->open($imgPath);
    		//$thumbPath = C('UPLOAD_PATH').$savePath.'100_'.$saveName;
    		//$image->thumb(100,100)->save($thumbPath);
    		$imageArr = array(
    			//'thumb'=>$thumbPath,//100
    			'source'=>$imgPath//原图
    		);
    		return $imageArr;
    	}else{
    		return $Upload->getError();
    	}
    }

    //图片上传（将图片上传到本地文件夹）
    public function plupload() {
        $Upload = new Upload();
        $Upload->rootPATH = C('UPLOAD_PATH');
        $Upload->maxSize = C('Image_Max_Size');//1M，最大允许上传的图片大小
        $info = $Upload->upload();
        if($info){
            $savePath = $info['file']['savepath'];
            $saveName = $info['file']['savename'];
            $imgPath = C('UPLOAD_PATH').$savePath.$saveName;
            $imageArr = array(
                'source'=>$imgPath//原图
            );
            return $imageArr;
        }else{
            return $Upload->getError();
        }
    }
	
	//个人头像上传
	public function face() {
		$Upload = new Upload();
		$Upload->rootPATH = C('UPLOAD_PATH');
		$Upload->maxSize = 1048576;//1M
		$info = $Upload->upload();
		if($info){
			$savePath = $info['Filedata']['savepath'];
			$saveName = $info['Filedata']['savename'];
			$imgPath = C('UPLOAD_PATH').$savePath.$saveName;
			$image = new Image();
			$image->open($imgPath);
			$image->thumb(500,500)->save($imgPath);
			return $imgPath;
		}else{
			return $Upload->getError();
		}
	}
	
	//保存头像
	public function crop($url,$x,$y,$w,$h) {
		$bigPath = C('FACE_PATH').session('user_auth')['id'].'.jpg';
		$smallPath = C('FACE_PATH').session('user_auth')['id'].'_small.jpg';
		$image = new Image();
		$image->open($url);
		$image->crop($w, $h, $x, $y)->save($url);
		$image->thumb(200, 200,Image::IMAGE_THUMB_FIXED)->save($bigPath);
		$image->thumb(50, 50,Image::IMAGE_THUMB_FIXED)->save($smallPath);
		$imageArr = array(
			'big'=>$bigPath,//200
			'small'=>$smallPath,//50
		);
		return $imageArr;
	}
	
}