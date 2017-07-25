<?php
namespace Home\Controller;

class OrderController extends HomeController {

    /**
     * 创建订单
     */
    public function createUserOrder() {
        if(IS_AJAX) {
            $cartidsArray = I('post.cartids');//购物车id数组集合
            if(empty($cartidsArray)) {
                $ids = I('post.productid');
            }else {
                $ids = $cartidsArray;
            }
            //如果$ids为数组的话,那么就是通过购物车创建订单;否则就是通过产品id创建订单的。
            echo D('Order')->createUserOrder($ids, I('post.shopid'), I('post.frominfo'), I('post.addressid'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 订单支付
     */
    public function orderpay() {
        if($this->userLogined()) {
            $id = I('get.id');
            if(!empty($id)) {
                $this->assign('orderpayinfo', D('Order')->getOrderPayinfoWithId($id, session('user_auth.id')));
            }else {
                $this->assign('orderpayinfo', D('Order')->getOrderPayinfoWithOrdersn(I('get.ordersn_general'), session('user_auth.id')));
            }
            $this->display();
        }
    }

    /**
     * 订单支付 - 消费积分+钱包余额支付
     */
    public function setOrderPay() {
        if(IS_AJAX) {
            echo D('Order')->setOrderPay(I('post.payway'), I('post.orderid'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 取消订单
     */
    public function cancelOrder() {
        if(IS_AJAX) {
            echo D('Order')->cancelOrder(I('post.ordersn_general'), I('post.reason'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 删除订单
     */
    public function delOrder() {
        if(IS_AJAX) {
            echo D('Order')->delOrder(I('post.ordersn_general'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 确认收货
     */
    public function completeOrder() {
        if(IS_AJAX) {
            echo D('Order')->completeOrder(I('post.ordersn_general'), session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 查看物流 - 下一步完善
     * location.href='{:U("Order/express", array("orderid"=>$list["id"]))}'
     */
    public function express() {
        if($this->userLogined()) {
            //$this->assign('oneOrder', D('Order')->getOneOrder(I('get.orderid'), session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 订单详情
     */
    public function detail() {
        if($this->userLogined()) {
            $this->assign('userDefaultAddress', D('Address')->getOneUserDefaultAddress(session('user_auth.id')));
            $this->assign('oneOrder', D('Order')->getOneOrderDetail(I('get.ordersn_general'), session('user_auth.id')));
            $this->display();
        }
    }

}