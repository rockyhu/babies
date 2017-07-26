<?php
namespace Admin\Controller;

class SystemController extends AuthController {
        
    public function index() {
        $this->assign('oneSystem', D('System')->getOneSystem());
        $this->display('System/system');
    }
    
    /**
     * 系统参数设置
     */
    public function setSystem() {
    	if (IS_AJAX) {
    		echo D('System')->setSystem(I('post.id'), I('post.webname'), I('post.keywords'), I('post.description'), I('post.copyright'), I('post.beian'), I('post.shutdownstate'), I('post.shutdowntitle'), I('post.shutdowncontent'));
    	} else {
    		$this->error('非法操作！');
    	}
    }
    
}