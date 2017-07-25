<?php
namespace Home\Controller;

use Think\Verify;

class UserController extends HomeController {

    /**
     * 会员中心
     */
    public function index() {
        if($this->userLogined()) {
            D('User')->updateUserSession(session('user_auth.id'));
            $this->assign('isWechat', isWechat() ? 'true' : 'false');
            $this->assign('oneUser', D('User')->getUserInfoIndex(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     *
     */
    public function complete() {
        if($this->userLogined()) {
            $userid = session('user_auth.id');
            $oneuser = M('User')->field('phone,realname,isbindphone')->where("id='{$userid}'")->find();
            $this->assign('oneUser', $oneuser);
            if($oneuser['isbindphone']==0) {
                $this->display();
            }else {
                $this->redirect('Index/index');
            }
        }
    }

    public function setUserComplete() {
        if(IS_AJAX) {
            echo D('User')->setUserComplete(strTrim(I('post.phone')), I('post.realname'), session('user_auth.id'));
        }
    }

    /**
     * 个人订单
     */
    public function order() {
        if($this->userLogined()) {
            $this->assign('orderlist', D('Order')->getUserOrderList(I('get.order_state'), session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 我的足迹
     */
    public function history() {
        if($this->userLogined()) {
            $this->display();
        }
    }

    /**
     * 绑定手机号
     */
    public function phone() {
        if($this->userLogined()) {
            $this->display();
        }
    }

    /**
     * 绑定手机号码
     */
    public function setUserPhone() {
        if(IS_AJAX){
            $phone = I('post.phone');
            $activecode = I('post.activecode');
            if(!empty($phone) && isNumeric($activecode)){//验证码必须存在
                echo D('User')->setUserPhone($phone, session('user_auth.id'));
            }else{//非法操作
                echo 0;
            }
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 我的资料
     */
    public function info() {
        if($this->userLogined()) {
            $this->assign('oneUser', D('User')->getUserInfo(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 我的资料
     */
    public function setUserInfo() {
        if(IS_AJAX){
            echo D('User')->setUserInfo(I('post.realname'), I('post.phone'), I('post.weixin'), I('post.province'), I('post.city'), I('post.town'), session('user_auth.id'));
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 会员注册
     */
    public function register() {
        $this->display();
    }

    /**
     * 注册会员
     */
    public function userRegister() {
        if(IS_AJAX){
            $userid = D('User')->register(strTrim(I('post.phone')), I('post.realname'), I('post.password'));
            echo $userid>0 ? $userid : 0;
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 会员登录
     */
    public function login() {
        if(IS_AJAX){
            echo D('User')->login(strTrim(I('post.phone')), I('post.password'), I('post.auto'));
        }else{
            if($this->isshutdown()) {
                if(!session('?user_auth')){
                    $this->display();
                }else{
                    $this->redirect('Index/index');
                }
            }
        }
    }

    /**
     * 退出登录
     */
    public function logout() {
        session('user_auth', null);
        $this->redirect('Index/index');
    }

    /**
     * 余额提现
     */
    public function withdraw() {
        if($this->userLogined()) {
            //判断是否绑定手机号,真实姓名信息
            $this->assign('isbindphone', session('user_auth.isbindphone'));
            //先判断是否已经绑定银行卡,如果没有绑定银行卡,那么先绑定银行卡
            $this->assign('userBank', D('Bank')->getOneUserBank(session('user_auth.id')));
            $this->assign('oneUser', D('User')->getUserInfoForwithdraw(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 提交余额提现
     */
    public function withdrawdo() {
        if(IS_AJAX){
            $money = I('post.money');
            if($money< 100) {
                echo -1;
                exit();
            }
            echo D('User')->withdrawdo(I('post.money'), I('post.cashoutfee'), session('user_auth.id'));

        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 余额明细 - 财务明细
     */
    public function yuedetail() {
        if($this->userLogined()) {
            $this->assign('userPurselist', D('Purse')->getUserPurseList(session('user_auth.id'), 'all'));
            $this->display();
        }
    }

    /**
     * 获取指定页的 - 财务明细
     */
    public function getPagePurse() {
        if (IS_AJAX) {
            echo D('Purse')->getPageUserPurseList(session('user_auth.id'), I('post.page'));
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 充值明细
     */
    public function rechargedetail() {
        if($this->userLogined()) {
            $this->assign('userRechargelist', D('Purse')->getUserPurseList(session('user_auth.id'), 'recharge'));
            $this->display();
        }
    }

    /**
     * 提现记录
     */
    public function cashoutdetail() {
        if($this->userLogined()) {
            $this->assign('userRechargelist', D('Purse')->getUserPurseList(session('user_auth.id'), 'cashout'));
            $this->display();
        }
    }

    /**
     * 银行卡设置
     */
    public function bank() {
        if($this->userLogined()){
            $this->assign('oneBank', D('Bank')->getOneUserBank(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 银行卡设置
     */
    public function setUserBank() {
        if(IS_AJAX){
            echo D('Bank')->addUserBank(I('post.cardno'), I('post.bankname'), I('post.cardname'), I('post.bankaddress'), session('user_auth.id'));
        }else{
            $this->error('非法操作~');
        }
    }

    /**
     * 我的账户详情
     */
    public function account() {
        if($this->userLogined()){
            $this->assign('userAccount', D('User')->getUserAccountInfo(I('get.type'), session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 获取指定页的 - 我的账户
     */
    public function getPageAccount() {
        if (IS_AJAX) {
            echo D('Purse')->getPageUserTypePurseList(I('post.type'), session('user_auth.id'), I('post.page'));
        } else {
            $this->error('非法操作！');
        }
    }

    /**
     * 线下订单
     */
    public function storeorder() {
        if($this->userLogined()){
            $this->assign('orderlist', D('StoreOrder')->getUserStoreOrderList(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 问题列表
     */
    public function question() {
        $this->assign('questionlist', D('Question')->getAllQuestionList(I('get.tag')));
        $this->display();
    }

    /**
     * 问题列表详情
     */
    public function questiondetail() {
        $this->assign('oneQuestion', D('Question')->getOneQuestion(I('get.id'), session('user_auth.id')));
        $this->display();
    }

    /**
     * 我的收藏列表
     */
    public function favorite() {
        if($this->userLogined()){
            $this->assign('favoritelist', D('ProductLike')->getUserProductLikeList(session('user_auth.id')));
            $this->display();
        }
    }

}