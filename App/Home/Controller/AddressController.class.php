<?php
namespace Home\Controller;
use Think\Controller;

class AddressController extends Controller {
	
    public function getUserAddress(){
        if (IS_AJAX) {
            $this->assign('addresslist', D('Address')->getUserAddress(I('get.addressid'), session('user_auth.id')));
            $this->display();
        }
    }

    public function addAddress(){
        if (IS_AJAX) {
            $this->display();
        }
    }

    public function editAddress() {
        if (IS_AJAX) {
            $this->assign('oneAddress', D('Address')->getOneUserAddress(I('get.id')));
            $this->display();
        }
    }

    public function addUserAddress() {
        if (IS_AJAX) {
            echo D('Address')->addUserAddress(I('post.realname'), I('post.phone'), I('post.province'), I('post.city'), I('post.town'), I('post.address'), session('user_auth.id'));
        }
    }

    public function editUserAddress() {
        if (IS_AJAX) {
            echo D('Address')->editUserAddress(I('post.id'), I('post.realname'), I('post.phone'), I('post.province'), I('post.city'), I('post.town'), I('post.address'), session('user_auth.id'));
        }
    }

    public function addresslist() {
        $this->assign('addresslist', D('Address')->getUserAddress(0, session('user_auth.id')));
        $this->display('Address/list');
    }

    public function edit() {
        $this->assign('oneAddress', D('Address')->getOneUserAddress(I('get.id')));
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function remove() {
        if(IS_AJAX){
            echo D('Address')->remove(I('post.id'));
        }else{
            $this->error('非法操作！');
        }
    }

    public function issetAddress() {
        if(IS_AJAX){
            echo D('Address')->issetAddress(I('post.id'), session('user_auth.id'));
        }else{
            $this->error('非法操作！');
        }
    }
    
}