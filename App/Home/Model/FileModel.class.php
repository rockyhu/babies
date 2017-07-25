<?php
namespace Home\Model;
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

    //证件上传
    public function pics() {
        $Upload = new Upload();
        $Upload->rootPATH = C('UPLOAD_PATH');
        $Upload->maxSize = C('Image_Max_Size');//1M，最大允许上传的图片大小
        $info = $Upload->upload();
        /**
        Array
        (
        [imgFile0] => Array
        (
        [name] => IMG_21761.jpg
        [type] => image/jpeg
        [size] => 176729
        [key] => imgFile0
        [ext] => jpg
        [md5] => 1688b059bff55bcb2e5cc999cabb3079
        [sha1] => f1eeff90635f52b8b2ee4ae3446aec81c4b112a3
        [savename] => 58a1b5ca775f9.jpg
        [savepath] => 2017-02-13/
        )

        )
         */
        if($info){
            $savePath = $info['imgFile0']['savepath'];
            $saveName = $info['imgFile0']['savename'];
            $imgPath = C('UPLOAD_PATH').$savePath.$saveName;
            return json_encode(array(
                'total' => 1,
                'success' => 1,
                'src' => $imgPath,
                'showsrc'=>C('SITE_URL').substr($imgPath, 2)
            ));
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

    //证件上传
    public function imgs() {
        $Upload = new Upload();
        $Upload->rootPATH = C('UPLOAD_PATH');
        $Upload->maxSize = C('Image_Max_Size');//1M，最大允许上传的图片大小
        $info = $Upload->upload();
        /**
        Array
        (
        [imgFile0] => Array
        (
        [name] => IMG_21761.jpg
        [type] => image/jpeg
        [size] => 176729
        [key] => imgFile0
        [ext] => jpg
        [md5] => 1688b059bff55bcb2e5cc999cabb3079
        [sha1] => f1eeff90635f52b8b2ee4ae3446aec81c4b112a3
        [savename] => 58a1b5ca775f9.jpg
        [savepath] => 2017-02-13/
        )

        )
         */
        if($info){
            $savePath = $info['imgFile0']['savepath'];
            $saveName = $info['imgFile0']['savename'];
            $imgPath = C('UPLOAD_PATH').$savePath.$saveName;
            //$image = new Image();
            //$image->open($imgPath);
            //$thumbPath = C('UPLOAD_PATH').$savePath.$saveName;
            //$image->thumb(720,720)->save($thumbPath);
            return json_encode(array(
                'total' => 1,
                'success' => 1,
                'src' => $imgPath,
                //'thumb' => C('SITE_URL').substr($thumbPath, 2),
                'showsrc'=>C('SITE_URL').substr($imgPath, 2)
            ));
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
	
}