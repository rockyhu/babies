<?php
namespace Home\Controller;
use Think\Controller;

/**
 * 工具类控制器
 * @author rockyhu
 */
class ToolController extends Controller {


    /**
     * 1.发送短信验证码,需要接收两个参数，一个phone,一个type
     */
    public function sendActiveCode(){
        if(IS_AJAX){
            //生成6位短信验证码
            $activecode = mt_rand(111111,999999);
            //将此会员的短信验证码保存到数据库中
            $sid = D('Sms')->addSms(strTrim(I('post.phone')), $activecode);
            if($sid >0){
                //发送验证码
                $state = requestSMSResponseCode(strTrim(I('post.phone')), $activecode, autoGetTemplateId(I('post.type')));
                echo $state ? 1 : 0;
            }else{
                echo 0;
            }
        }else{
            $this->error('非法访问!');
        }
    }

    /**
     * 2.Ajax验证短信激活码
     * I('post.phone') 接受phone参数
     * I('post.activecode') 接受activecode参数
     */
    public function checkActiveCode() {
        if(IS_AJAX){
            $sms = D('Sms');
            $smsState = $sms->checkSMS(strTrim(I('post.phone')), I('post.activecode'));
            echo $smsState ? 'true' : 'false';
        }else{
            $this->error('非法访问!');
        }
    }

	/**
     * 3.取消提现申请
     */
	public function cancelCashout() {
        if(IS_AJAX){
            echo D('Cashout')->cancelCashout(I('post.id'), session('user_auth.id'));
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 4.删除充值记录
     */
    public function removeUserPay() {
        if(IS_AJAX){
            echo D('Userpay')->removeUserPay(I('post.id'), session('user_auth.id'));
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 5.添加收货地址
     */
    public function addAddress() {
        if(IS_AJAX){
            echo D('Address')->addAddress(I('post.consignee'), I('post.phone'), I('post.prov').' '.I('post.city').' '.I('post.town'), I('post.address'), session('user_auth.id'));
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 6.编辑收货地址
     */
    public function updateAddress() {
        if(IS_AJAX){
            echo D('Address')->updateAddress(I('post.addressid'), I('post.consignee'), I('post.phone'), I('post.prov').' '.I('post.city').' '.I('post.town'), I('post.address'));
        }else{
            $this->error('非法访问~');
        }
    }

    /**
     * 7.删除收货地址
     */
    public function removeAddress() {
        if(IS_AJAX){
            echo D('Address')->removeAddress(I('post.id'));
        }else{
            $this->error('非法访问~');
        }
    }
    
}