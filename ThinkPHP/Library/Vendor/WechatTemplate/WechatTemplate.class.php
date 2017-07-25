<?php
/**
 * Created by PhpStorm.
 * User: rockyhu
 * Date: 2017/3/15
 * Time: 08:30
 */
class WechatTemplate {
    private $appid = 'wxcf853a9ced1a8646';
    private $appsecret = 'dbb6febd4bfbb35c08067c58eb73551e';
    private $lasttime = 0;//最后一次获取access token的时间点
    private $access_token = '';//保存access token

    //构造函数,获取access token
    public function __construct($appid = NULL, $appsecret = NULL)
    {
        if($appid && $appsecret)
        {
            $this->appid = $appid;
            $this->appsecret = $appsecret;
        }

        if(!empty(S('access_token')) && !empty(S('lasttime')))
        {
            $this->access_token = S('access_token');
            $this->lasttime = S('lasttime');
        }

        if(($this->lasttime == 0 || $this->access_token == '') || (time() > ($this->lasttime + 7200)))
        {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='. $this->appid .'&secret='.$this->appsecret;
            $res = $this->http_request($url);
            $result = json_decode($res, true);
            $this->access_token = $result['access_token'];
            $this->lasttime = time();
            //Save to database OR Memcache
            S('access_token', $this->access_token);
            S('lasttime', $this->lasttime);
        }
    }

    /**
     * 发送模板消息
     */
    public function sendTemplateMessage($data)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$this->access_token;
        $res = $this->http_request($url, $data);
        return json_decode($res, true);
    }

    /**
     * http请求(支持GET和POST)
     */
    public function http_request($url, $data = null)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if(!empty($data))
        {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}