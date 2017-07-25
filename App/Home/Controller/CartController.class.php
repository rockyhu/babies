<?php
namespace Home\Controller;

class CartController extends HomeController {

    /**
     * 购物车列表
     */
    public function index(){
        if($this->userLogined()) {
            $this->assign('cartlist', D('Cart')->getUserCartList(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 订单确认
     */
    public function orderconfirm() {
        if($this->userLogined()) {
            $cartids = I('get.cartids');
            $productid = I('get.productid');
            $this->assign('userDefaultAddress', D('Address')->getOneUserDefaultAddress(session('user_auth.id')));
            if(!empty($cartids)) {
                $this->assign('cartorderlist', D('Cart')->getUserCartOrderList($cartids, session('user_auth.id')));
                $this->display();
            }elseif (!empty($productid)) {
                $this->assign('cartorderlist', D('Cart')->getUserCartOrderListWithProductid($productid, session('user_auth.id')));
                $this->display();
            }else {
                $this->error('非法操作');
            }
        }
    }

    /**
     * 创建购物车
     */
    public function addCart() {
        if(IS_AJAX) {
            echo D('Cart')->addCart(I('post.productid'), I('post.num'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 更新购物车数量
     */
    public function updateCart() {
        if(IS_AJAX) {
            echo D('Cart')->updateCart(I('post.cartid'), I('post.num'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 删除购物车中的商品
     */
    public function delCart() {
        if(IS_AJAX) {
            echo D('Cart')->delCart(I('post.cartids'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

}