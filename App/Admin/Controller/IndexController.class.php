<?php
namespace Admin\Controller;

class IndexController extends AuthController {
    
	//显示后台框架
	public function index(){
        $this->display();
    }
    
}