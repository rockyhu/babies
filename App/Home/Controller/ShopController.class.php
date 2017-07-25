<?php
namespace Home\Controller;

class ShopController extends HomeController{

    public function index() {
        if($this->userLogined()) {
            D('User')->updateUserSession(session('user_auth.id'));
            if (session('user_auth.isshop') > 0) {
                $this->redirect('Shop/shop');
            } else {
                $shopRegisterUrl = C('SITE_URL') . '?c=Payment&a=shopRegister';
                header("Location: $shopRegisterUrl");
            }
        }
    }

    /**
     * 商家中心
     */
    public function shop() {
        if($this->userLogined()) {
            $this->assign('shopinfo', D('Shop')->getShopInfo(session('user_auth.id')));
            $this->display('Shop/index');
        }
    }

    /**
     * 获取招商员的真实姓名
     */
    public function getMerchantRealname() {
        if(IS_AJAX){
            $phone = I('post.merchantphone');
            echo M('User')->where("phone='{$phone}' AND ismerchant=1")->getField('realname');
        }else{
            $this->error('非法访问!');
        }
    }

    /**
     * 重新提交商家入驻审核资料
     */
    public function updateShopInfo() {
        if(IS_AJAX){
            echo D('Shop')->updateShopInfo(I('post.id'), I('post.type'), I('post.email'), I('post.shopkind'), I('post.shopname'), I('post.realname'), str_replace(' ', '', I('post.phone')), I('post.province'), I('post.city'), I('post.town'), I('post.address'), I('post.images'), session('user_auth.id'));
        }else{
            $this->error('非法访问!');
        }
    }

    /**
     * 我的订单
     */
    public function order() {
        if($this->userLogined()) {
            //新判断当前的商家是否是实体商家,并且实体店铺是否开通
            $userid = session('user_auth.id');
            $shoptype = M('Shop')->where("userid='{$userid}'")->getField('type');
            if($shoptype == '线上店铺') {
                $this->assign('orderlist', D('Order')->getShopUserOrderList(I('get.order_state'), $userid));
                $this->display();
            }else if($shoptype == '实体店铺') {
                $this->assign('orderlist', D('StoreOrder')->getShopUserStoreOrderList($userid));
                $this->display('Store/order');
            }
        }
    }

    /**
     * 提现记录
     */
    public function logg() {
        if($this->userLogined()) {
            $this->assign('shoplogglist', D('ShopCashout')->getShopCashoutList(session('user_auth.id'), I('get.status')));
            $this->display();
        }
    }

    /**
     * 我的资料
     */
    public function info() {
        if($this->userLogined()) {
            $this->assign('shopkindlist', D('ShopKind')->getShopKindList());
            //获取会员信息
            $this->assign('shopinfo', D('Shop')->getRegisterShopInfo(session('user_auth.id'), '审核通过'));
            $this->display();
        }
    }

    /**
     * 绑定银行卡
     */
    public function bank() {
        if($this->userLogined()) {
            $this->assign('userbank', D('Bank')->getOneUserBank(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 8.设置银行卡 - old
     */
    public function setShopBank() {
        if(IS_AJAX){
            echo D('Bank')->addUserBank(I('post.cardno'), I('post.bankname'), I('post.cardname'), I('post.bankaddress'), session('user_auth.id'));
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 我的产品
     */
    public function product() {
        if($this->userLogined()) {
            //获取产品列表
            $productlist = D('Product')->getShopProductList(
                session('user_auth.id'),
                !empty(I('get.key')) ? I('get.key') : '',
                !empty(I('get.pnid')) ? I('get.pnid') : 0,
                !empty(I('get.nid')) ? I('get.nid') : 0,
                !empty(I('get.order')) ? I('get.order') : 'sales',
                !empty(I('get.order')) ? I('get.by') : 'desc'
            );
            $this->assign('productlist',$productlist);
            $this->display();
        }
    }

    /**
     * 货款余额提现
     */
    public function withdraw() {
        if($this->userLogined()) {
            //判断是否绑定手机号,真实姓名信息
            $this->assign('isbindphone', session('user_auth.isbindphone'));
            //先判断是否已经绑定银行卡,如果没有绑定银行卡,那么先绑定银行卡
            $this->assign('userBank', D('Bank')->getOneUserBank(session('user_auth.id')));
            $this->assign('oneUser', D('User')->getShopInfoForwithdraw(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 提交货款余额提现
     */
    public function withdrawdo() {
        if(IS_AJAX){
            echo D('User')->shopwithdrawdo(I('post.money'), I('post.cashoutfee'), I('post.shopid'), session('user_auth.id'));
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 店铺入驻
     */
    public function registerShop() {
        if(IS_AJAX){
            echo D('Shop')->registerShop(
                I('post.merchantphone'),
                I('post.type'),
                I('post.email'),
                I('post.shoppassword'),
                I('post.shopkind'),
                I('post.shopname'),
                I('post.realname'),
                I('post.phone'),
                I('post.province'),
                I('post.city'),
                I('post.town'),
                I('post.address'),
                I('post.images'),
                session('user_auth.id'));
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 我的团队
     */
    public function team() {
        if($this->userLogined()) {
            $this->assign('storeTeam', D('User')->getStoreUserTeam(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 招商佣金详情
     */
    public function detail() {
        if($this->userLogined()) {
            $this->assign('userAccount', D('User')->getUserAccountInfo(I('get.type'), session('user_auth.id')));
            $this->display();
        }
    }

}