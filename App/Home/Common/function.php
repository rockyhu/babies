<?php

/**
 * 1.新浪微博长链接转短链接接口
 * @param stirng $long_url 长链接url
 * @return string 短链接url
 */
function shortUrl($long_url){
	$apiUrl='http://api.t.sina.com.cn/short_url/shorten.json?source='.C('WEIBO_APIKEY').'&url_long='.$long_url;
	$response = file_get_contents($apiUrl);
	$json = json_decode($response);
	return $json[0]->url_short;
}

/**
 * 2.新浪微博短链接转长链接接口
 * @param string $short_url
 * @return string 长链接url
 */
function expandUrl($short_url){
	$apiUrl='http://api.t.sina.com.cn/short_url/expand.json?source='.C('WEIBO_APIKEY').'&url_short='.$short_url;
	$response = file_get_contents($apiUrl);
	$json = json_decode($response);
	return $json[0]->url_long;
}

/**
 * 3.用户名cookie加密
 * @param string $username 用户名
 * @param number $type,$type=0表示加密，1表示解密
 * @return string|boolean
 */
function encryption($username,$type=0) {
	$key = sha1(C('COOKIE_KEY'));
	if(!$type){//加密
		return base64_encode($username ^ $key);
	}
	return base64_decode($username) ^ $key;//解密
}

/**
 * 6.去掉字符串中的空格
 * @param  string $string 字符串
 * @return  string        处理后的字符串
 */
function strTrim($string) {
	return str_replace(' ', '', $string);
}

/**
 * 获取两个数中较小的一个
 * @param number $first 第一个数
 * @param number $two 第二个数
 * @return number 较小的一个
 */
function getMinNumber($first, $two) {
	return $first<$two ? $first : $two;
}

/**
 * 订单邮件内容模板
 */
function orderMailContent($username, $realname, $date, $oneOrder = array()) {
    $html = '<div><p>'.$username.'/'.$realname.'于'.$date.'购买了产品，产品订单如下：</p><table><thead><tr><th>产品名称</th><th>规格</th><th>单价</th><th>数量</th><th>总额</th></tr></thead><tbody>';
    foreach ($oneOrder['product'] as $key=>$value) {
        $html .= '<tr><td>'.$value['name'].'</td><td>'.$value['norms'].'</td><td>￥'.$value['price'].'</td><td>'.$value['num'].'</td><td>￥'.$value['price']*$value['num'].'</td></tr>';
    }
    $html .= '</tbody></table><p>订单总金额：￥<strong style="color: #dd4b39;">'.$oneOrder['price'].'</strong>元</p><p>订单已支付成功，请及时发货~</p></div>';
    return $html;
}

/**
 * 邮件发送
 * @param string $to 表示接收邮件的邮箱
 * @param string $subject 表示邮件主题
 * @param string $content 表示邮件的内容
 * @return boolean
 */
function sendMail($to, $subject, $content) {
	Vendor('PHPMailer.PHPMailerAutoload');
	$mail = new PHPMailer();									//实例化
	$mail->IsSMTP();											// 启用SMTP
	$mail->Host			= C('MAIL_CONFIG')['host']; 			//SMTP 服务器,"smtp.qq.com  ...465"
	$mail->Port     	= 465;                   				// SMTP服务器的端口号
	//$mail->SMTPDebug  = 1;                     				// 启用SMTP调试功能
	$mail->SMTPAuth 	= true; 								//启用smtp认证
	$mail->SMTPSecure 	= "ssl";                 				// 安全协议
	$mail->Username 	= C('MAIL_CONFIG')['username'];			//你的邮箱名
	$mail->Password 	= C('MAIL_CONFIG')['password'];			//邮箱密码
	$mail->From 		= C('MAIL_CONFIG')['from'];				//发件人地址（也就是你的邮箱地址）
	$mail->FromName 	= C('MAIL_CONFIG')['fromname'];			//发件人姓名
	
	if(is_array($to)) {//数组的情况
		if(count($to) > 1){
			foreach ($to as $v){
				$mail->AddBCC($v);
			}
		}else if (count($to) == 1){
			$mail->AddAddress($to[0],"name");
		}
	}else {//字符串的情况
		$mail->AddAddress($to,"name");
	}
	
	$mail->WordWrap 	= 50;									//设置每行字符长度
	$mail->IsHTML(true);										// 是否HTML格式邮件
	$mail->CharSet		= "UTF-8";								//设置邮件编码
	$mail->Subject 		= $subject;								//邮件主题
	$mail->Body 		= $content;								//邮件内容
	$mail->AltBody = "This is the body in plain text for non-HTML mail clients"; //邮件正文不支持HTML的备用显示
	return $mail->Send() ? true : false;
}