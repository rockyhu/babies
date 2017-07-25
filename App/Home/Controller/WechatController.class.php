<?php
namespace Home\Controller;

class WechatController extends HomeController
{

    // 推送事件
    public function index()
    {
        $wechat = A('Wexin');
        $wechat->run();
    }

    // 验证用户是否授权登陆，未登陆则先跳转到登陆授权页面 - 通用
    public function checkAuthInfo()
    {
        $redirectUri = C('SITE_URL')."wechat/getAuthInfo";
        $wechat = $this->getWeChat();
        return $wechat->getOauthRedirect($redirectUri);
    }

    //获取用户授权登陆后的信息 - 通用版
    public function getAuthInfo() {
        // 获取微信授权后的Code
        $code = I('get.code');
        $wechat = $this->getWeChat();
        // 授后权获取的access_token 和 openid
        $result = $wechat->getOauthAccessToken();
        if ($result) {
            $access_token = $result['access_token'];
            $openid = $result['openid'];
            // 根据token拉去用户信息
            $userInfo = $wechat->getOauthUserinfo($access_token, $openid);
            if ($userInfo) {
                $nickname = $userInfo['nickname'];
                $headimgurl = $userInfo['headimgurl'];
                $sex = $userInfo['sex'];
                $array = array(
                    'access_token' => $access_token,
                    'openid' => $openid,
                    'nickname' => $nickname,
                    'avatar' => $headimgurl,
                    'sex' => $sex
                );
                // 通过cookie的方式保存微信授权的个人信息
                cookie('wxdata', json_encode($array), 3600 * 24 * 365);
                //2.重定向跳转 - 通过Cookie来传值
                $url = cookie('AuthRedirectUrl');
                cookie('AuthRedirectUrl', null);
                header("Location: $url");
            }
        }
    }

    /**
     * 1.生成场景二维码
     * @param string|number $scenestr 场景ID/场景字符串
     * @param number $qrcodemode 二维码类型
     * @param number $expire_seconds
     * @return array
     */
    public function createQrcode($scenestr, $qrcodemode, $expire_seconds)
    {
        // 1.创建二维码ticket
        $qrcode = $this->getWeChat()->getQRCode($scenestr, $qrcodemode, $expire_seconds);
        // 2.获取二维码图片
        $qrcodeimgUrl = $this->getWeChat()->getQRUrl($qrcode['ticket']);
        // 3.获取二位的短链接
        $shortImgUrl = $this->getWeChat()->getShortUrl($qrcodeimgUrl);
        // 返回二维码数组
        return [
            'ticketurl'=>$qrcodeimgUrl,
            'url' => $qrcode['url'],
            'qrcodeimg' => $shortImgUrl
        ];
    }

    // 获取微信对象
    private function getWeChat()
    {
        // 配置信息
        $options = array(
            'token' => 'aishangyuan1806', // 填写你设定的key
            'encodingaeskey' => 'z51u1o2F526OSS111272USO07qs10Z32SS5OF02SSQs', // 填写加密用的EncodingAESKey
            'appid' => 'wxcf853a9ced1a8646', // 填写高级调用功能的app id
            'appsecret' => 'dbb6febd4bfbb35c08067c58eb73551e'// 填写高级调用功能的密钥
        );
        // 引入微信插件
        vendor('wechat.TPWechat', '', '.class.php');
        // 实例化类
        return new \TPWechat($options);
    }
}