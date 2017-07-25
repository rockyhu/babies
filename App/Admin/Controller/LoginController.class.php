<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {

    //显示登录页
	public function index(){
    	if(session('admin')){
    		$this->redirect('Index/index');
    	}else{
            $_SESSION['form-token'] = getUniqid();
			$this->display();
    	}
    }
    
	//验证管理员
	public function checkManager() {
		if(IS_AJAX){
            if(isUniqidEq(I('post.token'))) {
                echo D('Manage')->checkManager(I('post.manager'), I('post.password'));
            }else {
                $this->error('请不要重复提交数据~');
            }
		}else{
			$this->error('非法操作');
		}
	}
	
	//退出登录
	public function logout() {
		session('admin',null);
        session('admininit', null);
        session('pageNavDos', null);//当前页面的菜单操作数组
		$this->redirect('Login/index');
	}
	
}