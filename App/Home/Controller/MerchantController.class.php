<?php
namespace Home\Controller;

class MerchantController extends HomeController{

    public function index() {
        if($this->userLogined()) {
            D('User')->updateUserSession(session('user_auth.id'));
            if (session('user_auth.ismerchant') > 0) {
                $this->redirect('Merchant/merchant');
            } else {
                $merchantRegisterUrl = C('SITE_URL') . '?c=Payment&a=merchantRegister';
                header("Location: $merchantRegisterUrl");
            }
        }
    }

    /**
     * 招商中心
     */
    public function merchant() {
        if($this->userLogined()) {
            $this->assign('merchantinfo', D('User')->getMerchantUserInfo(session('user_auth.id')));
            $this->display('Merchant/index');
        }
    }

    /**
     * 我的订单
     */
    public function order() {
        if($this->userLogined()) {
            $this->assign('orderlist', D('Order')->getMerchantUserOrderList(I('get.order_state'), session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 我的商家
     */
    public function shop() {
        if($this->userLogined()) {
            $this->assign('shoplist', D('Merchant')->getMerchantShop(session('user_auth.id')));
            $this->display();
        }
    }

    /**
     * 我的团队
     */
    public function team() {
        if($this->userLogined()) {
            $this->assign('teamlist', D('Merchant')->getMerchantTeam(session('user_auth.id'), I('get.t')));
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

    //生成二维码
    public function doqrcode() {
        if(IS_AJAX) {
            echo $this->urls(session('user_auth.id'));
        }else{
            $this->error('非法操作');
        }
    }

    /**
     * 创建二维码,并与背景图片和头像进行合并
     * @param $userid 会员id
     */
    public function urls($userid) {
        $filename = "./Uploads/merchant/".md5('merchant'.$userid).".jpg";
        if(!file_exists(mb_convert_encoding($filename , 'gbk' , 'utf-8'))) {
            //获取用户头像
            $avatar = M('User')->where("id='{$userid}'")->getField('avatar');
            $logo = $avatar ? $avatar : './Public/Home/img/photo-mr.jpg';
            $userid = 'merchant|'.$userid;
            $wechat = A('Wechat');
            $dataarray = $wechat->createQrcode($userid, 1, '永久');
            if ($logo !== FALSE) {
                $QR = imagecreatefromstring(file_get_contents($dataarray['qrcodeimg']));
                $logo = imagecreatefromstring(file_get_contents($logo));
                $QR_width = imagesx($QR);//二维码图片宽度
                $QR_height = imagesy($QR);//二维码图片高度
                $logo_width = imagesx($logo);//logo图片宽度
                $logo_height = imagesy($logo);//logo图片高度
                $logo_qr_width = $QR_width / 5;
                $scale = $logo_width / $logo_qr_width;
                $logo_qr_height = $logo_height / $scale;
                $from_width = ($QR_width - $logo_qr_width) / 2;
                //重新组合图片并调整大小
                imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
            }
            //输出图片
            Header("Content-type: image/png");
            imagepng($QR, $filename);
            imagedestroy($QR);
            //"text/html; charset=UTF-8"
            Header("Content-type: text/html; charset=UTF-8");
        }
        return C('SITE_URL').substr($filename, 2);
    }

}