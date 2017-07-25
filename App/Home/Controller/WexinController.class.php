<?php
namespace Home\Controller;

use Think\Controller;

class WexinController extends Controller
{

    private $wechat;
    // 微信对象
    public function __construct()
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
        $this->wechat = new \TPWechat($options);
    }

    // 推送事件
    public function run()
    {
        // 校验签名
        $this->wechat->valid();
        // 获取微信服务器发来的信息来源
        $type = $this->wechat->getRev()->getRevType();
        //事件类型,$event为数组,['event'=>,'key'=>]
        $event = $this->wechat->getRevEvent();
        //操作用户的openid
        $fromusername = $this->wechat->getRevFrom();
        // 根据来源信息的类型使用不同的方法进行处理
        switch ($type) {
            case \TPWechat::MSGTYPE_TEXT:
                //获得用户发送过来的文字消息内容
                $content=$this->wechat->getRev()->getRevContent();
                $this->onText($content);
                break;
            case \TPWechat::MSGTYPE_EVENT:
                //$this->wechat->text('event:'.$event['event'].',key:'.$event['key'].',fromusername:'.$fromusername)->reply();
                /**
                 * 扫描场景二维码后,如果已经关注,则$event['event'] = SCAN,$event['key'] = reseller|13123
                 * 如果没有关注,则$event['event'] = subscribe,$event['key'] = qrscene_reseller|13123
                 */
                switch ($event['event']) {
                    case \TPWechat::EVENT_SUBSCRIBE:
                        // 关注
                        $this->onSubscribe($event['key'], $fromusername);
                        break;
                    case 'unsubscribe':
                        // 取消关注
                        $this->onUnsubscribe($fromusername);
                        break;
                    case \TPWechat::EVENT_MENU_CLICK:
                        // 点击菜单
                        $this->onClick($event['key']);
                        break;
                    case \TPWechat::EVENT_SCAN:
                        //扫描场景二维码,已经关注过的会员
                        $this->onScan($event['key'], $fromusername);
                        break;
                }
                break;
            default:
                $this->wechat->text('hello world')->reply();
                break;
        }
    }

    /**
     * 1.用户关注公众号时触发，回复「欢迎关注」
     */
    protected function onSubscribe($eventkey, $openid)
    {
        $this->actions($eventkey, $openid);
        $this->wechat->text('欢迎关注【中盛控股·爱尚缘消费共享商城】')->reply();
    }

    /**
     * 2.取消关注公众号
     */
    protected function onUnsubscribe($openid)
    {
        //设置会员是否关注公众号的字段
        M('User')->where("openid='{$openid}'")->setField('followed',2);
        $this->wechat->text('欢迎再回来')->reply();
    }

    /**
     * 3.点击菜单 - 收到自定义菜单消息时触发，回复菜单的EventKey
     */
    protected function onClick($key)
    {
        switch ($key) {
            case '区域代理':
                $html = '有关区域代理相关事宜,请联系【爱尚缘】的客服,客服电话是0755-21002719';
                break;
            case '联系我们':
                $html = '我们公司的客服电话是0755-21002719哟';
                break;
        }
        $this->wechat->text($html)->reply();
    }

    /**
     * 4.扫描场景二维码,已关注
     * @param $eventkey
     */
    public function onScan($eventkey, $openid) {
        $this->actions($eventkey, $openid);
    }

    /**
     * 分销商和招商员和商家的二维码判定
     * 扫描场景二维码后,如果已经关注,则$event['event'] = SCAN,$event['key'] = reseller|13123
     * 如果没有关注,则$event['event'] = subscribe,$event['key'] = qrscene_reseller|13123
     */
    public function actions($eventkey, $openid) {
        $keyArr = explode('_', $eventkey);
        if(count($keyArr) == 1) {//已关注者扫描
            $keyValueArr = explode('|', $keyArr[0]);
            $referee = $keyValueArr[1];
        }else if(count($keyArr) == 2) {//未关注者扫描
            $keyValueArr = explode('|', $keyArr[1]);
            $referee = $keyValueArr[1];
        }
        //判断当前关注会员用户是否是会员
        $userid = M('User')->where("openid='{$openid}'")->getField('id');
        //先判断当前$openid是否已经注册成为会员,如果没有,则自动注册成为会员,否则,将会员followed修改为1
        if(empty($userid)) {
            $userinfo = $this->wechat->getUserInfo($openid);
            //$this->wechat->text($openid.'--'.$userid.'---'.$userinfo['headimgurl'].'----'.$keyValueArr[1])->reply();
            //$keyValueArr[1]就是分销商的会员id
            //自动注册微信会员
            D('User')->autoRegister($openid, $userinfo['headimgurl'], $userinfo['nickname'], $userinfo['sex'], $referee);
        }else {
            M('User')->where("openid='{$openid}'")->setField('followed', 1);
        }
        /**
         * 接下来需要判断是分销商、招商员、商家
         * 分销商为reseller|13123,其中13123表示会员id
         * 招商员为merchant|13123,其中13123表示会员id
         * 商家为shop|13123,其中13123表示会员id
         */
        switch ($keyValueArr[0]) {
            case 'reseller':
                //无法直接做链接跳转,微信禁止
                break;
            case 'merchant':
                //更新当前会员的招商员为扫描二维码的会员
                M('User')->where("openid='{$openid}'")->setField('merchantreferee', $referee);
                break;
            case 'shop':
                break;
        }
    }

    /**
     * 输入文本
     * @param $text
     */
    protected function onText($text) {
        $this->wechat->text('我们公司的客服电话是0755-21002719哟')->reply();
    }

    //10.获取JsApi使用签名 - 自定义分享
    public function getJsSign($url) {
        return $this->wechat->getJsSign($url);
    }
}