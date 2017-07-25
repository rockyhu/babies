<?php
namespace Home\Controller;

class FileController extends HomeController{
	//图片上传
	public function image() {
		$file = D('File');
		$this->ajaxReturn($file->image());
	}

	//证件上传
	public function uploaderpics() {
        echo D('File')->pics();
    }

    //图片上传
    public function plupload() {
        $this->ajaxReturn(D('File')->plupload());
    }

    //实体店铺评价分享
    public function uploaderimgs() {
        echo D('File')->imgs();
    }
	
	//头像上传
	public function face() {
		$file = D('File');
		$this->ajaxReturn($file->face());
	}
	
	//保存头像
	public function crop() {
		$file = D('File');
		$img = $file->crop(I('post.url'),I('post.x'),I('post.y'),I('post.w'),I('post.h'));
		
		$user = D('User');
		
		$user->updateFace(json_encode($img));
		
		//$this->ajaxReturn($img);
		echo json_encode($img);
	}
}