<?php
namespace Home\Controller;
use Think\Controller;

class CategoryController extends Controller {
	
    public function index(){
        $this->assign('categorylist', D('ProductNav')->getCategoryList());
        $this->display();
    }
    
}