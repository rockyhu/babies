<?php
namespace Admin\Controller;

use Think\Controller;
class FileController extends Controller{
	//图片上传
	public function image() {
		$this->ajaxReturn(D('File')->image());
	}

    //图片上传
    public function plupload() {
        $this->ajaxReturn(D('File')->plupload());
    }
	
	//头像上传
	public function face() {
		$this->ajaxReturn(D('File')->face());
	}
	
	//保存头像
	public function crop() {
		$img = D('File')->crop(I('post.url'),I('post.x'),I('post.y'),I('post.w'),I('post.h'));
        D('User')->updateFace(json_encode($img));
		echo json_encode($img);
	}
}