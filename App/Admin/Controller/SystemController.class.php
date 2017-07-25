<?php
namespace Admin\Controller;

class SystemController extends AuthController {
        
    public function index() {
        $this->assign('oneSystem', D('System')->getOneSystem());
        $this->display('User/system');
    }
    
    /**
     * 系统参数设置
     */
    public function setSystem() {
    	if (IS_AJAX) {
    		echo D('System')->setSystem(I('post.id'), I('post.merchantpay'), I('post.resellerpay'), I('post.minitransfer'), I('post.minicashout'), I('post.cashoutfee'), I('post.epursefee'), I('post.gouwubifee'), I('post.shutdownstate'), I('post.shutdowntitle'), I('post.shutdowncontent'));
    	} else {
    		$this->error('非法操作！');
    	}
    }
    
}