<?php
namespace Admin\Controller;

use Think\Controller;

/**
 * 微信公众号对接
 * 
 * @author rockyhu
 *        
 */
class WechatController extends Controller
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
    
    // 验证
    public function index()
    {
        $this->wechat->valid();
    }

    /**
     * 1.生成场景二维码
     * 
     * @param string|number $scenestr
     *            场景ID/场景字符串
     * @param number $qrcodemode
     *            二维码类型
     * @param number $expire_seconds            
     * @return array
     */
    public function createQrcode($scenestr, $qrcodemode, $expire_seconds)
    {
        // 1.创建二维码ticket
        $qrcode = $this->wechat->getQRCode($scenestr, $qrcodemode, $expire_seconds);
        // 2.获取二维码图片
        $qrcodeimgUrl = $this->wechat->getQRUrl($qrcode['ticket']);
        // 3.获取二位的短链接
        $shortImgUrl = $this->wechat->getShortUrl($qrcodeimgUrl);
        // 返回二维码数组
        return [
            'url' => $qrcode['url'],
            'qrcodeimg' => $shortImgUrl
        ];
    }

    /**
     * 2.创建自定义菜单
     * 
     * @param array $data
     *            菜单数组数据
     */
    public function createMenu($data)
    {
        return $this->wechat->createMenu($data);
    }

    /**
     * 3.获取自定义菜单
     */
    public function getMenu()
    {
        return $this->wechat->getMenu();
    }

    /**
     * 4.删除自定义菜单
     */
    public function deleteMenu()
    {
        return $this->wechat->deleteMenu();
    }

    /**
     * 5.获取公众号平台永久素材
     */
    public function getForeverList()
    {
        return $this->wechat->getForeverList('news', 0, 20);
    }
}
