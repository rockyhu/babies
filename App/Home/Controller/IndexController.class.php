<?php
namespace Home\Controller;

class IndexController extends HomeController {

    public function index(){
        //if($this->userLogined()) {
            //$this->assign('isbindphone', session('user_auth.isbindphone'));
            $this->display();
        //}
    }
    
}

