<?php
/**
 * 1.判断某一个值是否在数组中，替换，in_array() 这个在大数组时，效率不高；
 * @param type $item 检测的值
 * @param type $array 已存在数组
 * @return boolean
 */
function inArray($item,$array) {
	//array_flip 交换数组中的键和值
	$flipArray = array_flip($array);
	//如果 $flipArray[$item] 存在并且值不是 NULL 则返回 TRUE，否则返回 FALSE。
	return isset($flipArray[$item]);
}

/**
 * 2.检测图片验证码
 * @param string $code 图片验证码
 * @param number $id
 * @return boolean
 */
function check_verify($code,$id=1){
	$verify = new \Think\Verify();
	$verify->reset = false;//验证成功后不清空session
	return $verify->check($code,$id);
}

/**
 * 6.验证是否是合法的手机号码
 * @param string $phone 手机号码
 * @return boolean
 */
function isPhone($phone) {
	return preg_match("/^(0|86|17951)?(13[0-9]|15[012356789]|18[0-9]|14[57])[0-9]{8}$/", $phone) ? true :false;
}

/**
 * 判断当前浏览器是否是微信内置浏览器,是返回true,否则返回false
 */
function isWechat() {
    return strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ? true : false;
}

/**
 * 7.生成唯一标识符
 * @return string 40个长度的字符串
 */
function getUniqid () {
	return sha1(uniqid(rand(),true));
}

/**
 * 8.数字后缀补.00
 * @param string $number 数字字符串
 * @return string
 */
function setZero($number){
	$pos = strpos($number, '.');
	if($pos !== false){
		$str = mb_substr($number, $pos);
		$len = strlen($str);
		if($len>3) $number = mb_substr($number, 0, $pos+3);
		else if($len == 2) $number .= '0';
	}else{
		$number .= '.00';
	}
	return $number;
}

/**
 * 10.数组转对象
 * @param object $e
 */
function arrayToObject($e){
    if(gettype($e)!='array') return;
    foreach($e as $k=>$v){
        if(gettype($v)=='array' || getType($v)=='object') $e[$k]=(object)arrayToObject($v);
    }
    return (object)$e;
}

/**
 * 11.对象转数组
 * @param object $e
 * @return void|array
 */
function objectToArray($e){
    $e=(array)$e;
    foreach($e as $k=>$v){
        if(gettype($v)=='resource') return;
        if(gettype($v)=='object' || gettype($v)=='array') $e[$k]=(array)objectToArray($v);
    }
    return $e;
}

/**
 * 12.删除用,分割的字符串中指定的单个元素
 * @param string $str 用,分隔的字符串集合
 * @param string $find 待删除的字符串
 * @return string 删除后的新字符串
 */
function strRemove($str,$find) {
    $arr = explode(',', $str);
    $key = array_search($find, $arr);
    if($key !== false) array_splice($arr, $key, 1);
    return implode(',', $arr);
}

/**
 * 13.判断字符串是否为数字
 * @param string $_string 字符串
 * @return boolean
 */
function isNumeric($_string){
    return is_numeric($_string) ? true : false;
}

/**
 * 14.http JSON数据请求的解析
 * @param string $requestJSON 待解析的json字符串
 * @param boolean $type 默认为false，true表示解析成数组，false表示解析成对象
 * @return mixed（array|object）
 */
function parseJSON($requestJSON,$type = false) {
    return $type ? json_decode($requestJSON,true) : json_decode($requestJSON);
}

/**
 * 15.返回JSON格式数据
 * @param array $requestArray 待转换的数组数据
 * @param string $type $type默认为true，将空数组转换成对象，即将数组转换成对象JSON；$type为false时将数组转换成数组JSON
 * @return string
 */
function toJSON($requestArray,$type = true) {
    header('Content-type: application/json');
    return $type ? json_encode($requestArray,JSON_FORCE_OBJECT) : json_encode($requestArray);
}

/**
 * 16.根据uid来生成订单号
 * @return string 订单编号
 */
function getOrderNum($userid) {
    return 'SL'.date('Ymdis').$userid.rand(100,999);
}

/**
 * 生成支付订单
 * @param $userid 会员id
 */
function getSignOrderNum($userid) {
    return $userid ? date('YmdHis').$userid.mt_rand(11, 99) : date('YmdHis').mt_rand(11111, 99999);
}

/**
 * 17.时间戳转星期几，eg:1443996000是1,表示周一，7表示周天
 * @param string $time 日期时间戳
 * @return string
 */
function timeToWeekday($time) {
	$weekday = $time ? date('N', $time) : date('N', time());
	switch ($weekday){
		case 1:
			$week = '周一';
			break;
		case 2:
			$week = '周二';
			break;
		case 3:
			$week = '周三';
			break;
		case 4:
			$week = '周四';
			break;
		case 5:
			$week = '周五';
			break;
		case 6:
			$week = '周六';
			break;
		case 7:
			$week = '周日';
			break;
	}
	return $week;
}

/**
 * 18.判断日期时间戳是否是节假日,工作日对应结果为 0, 休息日对应结果为 1, 节假日对应的结果为 2；
 * @param string $time 日期时间戳或日期时间戳集合，集合是用,隔开的
 * @return void|mixed
 * 百度apistore地址：http://apistore.baidu.com/apiworks/servicedetail/1116.html
 */
function whetherHoliday($time) {
	if($time == '') return; 
	//时间戳转日期
	if(strpos($time, ',') !== false){//多个查询
		$timeArr = explode(',', $time);
		$timeStr = '';
		foreach ($timeArr as $key=>$value){
			$timeStr .= date('Ymd', $value).',';
		}
		$date = substr($timeStr, 0 , -1);
	}else{
		$date = date('Ymd', $time);
	}
	$ch = curl_init();
	$url = 'http://apis.baidu.com/xiaogg/holiday/holiday?d='.$date;
	$header = array(
		//'apikey: 您自己的apikey',
		'apikey:5ed6d2accf8e6f30142806f5874e1a14'
	);
	// 添加apikey到header
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 执行HTTP请求
	curl_setopt($ch , CURLOPT_URL , $url);
	$res = curl_exec($ch);
	return json_decode($res);
}

/**
 * 19.根据时间戳获取时间戳所在的本周和下周的日期时间戳集合
 * @param string $time 时间戳，为空时，表示当前的时间戳
 * @return multitype:multitype:string
 */
function getCurrentAndNextWeekdayAsTime($time = '') {
	$time = $time ? $time : strtotime(date('Y-m-d', time()));
	$weekday = date('N', $time);
	/**
	 * 算法分析：
	 * 1,7-1计算未来6天日期
	 * 2,7-2计算未来5天日期，2-1计算过去1天日期
	 * 3,7-3计算未来4天日期，3-1计算过去2天日期
	 * ...
	 */
	$nowWeekday = array();
	$afterWeekday = array();
	//计算本周
	for($i=1;$i<=7;$i++){
		if($i < $weekday) $nowWeekday[] = strtotime('-'.($weekday-$i).'day', $time);//计算过去的N天
		else if($i == $weekday) $nowWeekday[] = $time;//计算今天
		else if($i > $weekday) $nowWeekday[] = strtotime('+'.($i-$weekday).'day', $time);//计算未来的N天
	}
	$afterTimeStart = $nowWeekday[6];
	//计算未来一周的N天
	for($i=1;$i<=7;$i++){
		$afterWeekday[] = strtotime('+'.$i.'day', $afterTimeStart);
	}
	return array(
		'nowWeek'=>$nowWeekday,
		'afterWeek'=>$afterWeekday
	);
}

/**
 * 20.根据时间戳获取时间戳所在的本周的日期时间戳集合
 * @param string $time 时间戳，为空时，表示当前的时间戳
 * @return multitype:multitype:string
 */
function getCurrentWeekdayAsTime($time = '') {
	$time = $time ? $time : strtotime(date('Y-m-d', time()));
	$weekday = date('N', $time);
	/**
	 * 算法分析：
	 * 1,7-1计算未来6天日期
	 * 2,7-2计算未来5天日期，2-1计算过去1天日期
	 * 3,7-3计算未来4天日期，3-1计算过去2天日期
	 * ...
	*/
	$nowWeekday = array();
	//计算本周
	for($i=1;$i<=7;$i++){
		if($i < $weekday) $nowWeekday[] = strtotime('-'.($weekday-$i).'day', $time);//计算过去的N天
		else if($i == $weekday) $nowWeekday[] = $time;//计算今天
		else if($i > $weekday) $nowWeekday[] = strtotime('+'.($i-$weekday).'day', $time);//计算未来的N天
	}
	return array(
		'nowWeek'=>$nowWeekday
	);
}

/**
 * 21.根据时间戳获取时间戳的下周的日期时间戳集合
 * @param string $time 时间戳，为空时，表示当前的时间戳
 * @return multitype:multitype:string
 */
function getNextWeekdayAsTime($time = '') {
	$time = $time ? $time : strtotime(date('Y-m-d', time()));
	$weekday = date('N', $time);
	/**
	 * 算法分析：
	 * 1,7-1计算未来6天日期
	 * 2,7-2计算未来5天日期，2-1计算过去1天日期
	 * 3,7-3计算未来4天日期，3-1计算过去2天日期
	 * ...
	*/
	$nowWeekday = array();
	$afterWeekday = array();
	//计算本周
	for($i=1;$i<=7;$i++){
		if($i < $weekday) $nowWeekday[] = strtotime('-'.($weekday-$i).'day', $time);//计算过去的N天
		else if($i == $weekday) $nowWeekday[] = $time;//计算今天
		else if($i > $weekday) $nowWeekday[] = strtotime('+'.($i-$weekday).'day', $time);//计算未来的N天
	}
	$afterTimeStart = $nowWeekday[6];
	//计算未来一周的N天
	for($i=1;$i<=7;$i++){
		$afterWeekday[] = strtotime('+'.$i.'day', $afterTimeStart);
	}
	return array(
		'afterWeek'=>$afterWeekday
	);
}

/**
 * 20.将课时字符串转换成数组
 * @param string $period 类似于'07:00|09:00'的课时字符串
 * @return void|multitype:array
 */
function busyOrBookedPeriodToarray($period = '') {
	if($period == '') return;
	return explode('|', $period);
}

/**
 * 21.判断当前两个周的日期是否是节假日,工作日对应结果为 0, 休息日对应结果为 1, 节假日对应的结果为 2；
 * @param array $oneCoachPeriod 当前的两个周日期时间戳
 * @return array eg:Array ( [20151005] => 2 [20151006] => 2 [20151007] => 2 [20151008] => 0 [20151009] => 0 [20151010] => 0 [20151011] => 1 [20151012] => 0 [20151013] => 0 [20151014] => 0 [20151015] => 0 [20151016] => 0 [20151017] => 1 [20151018] => 1 )
 */
function theDayisHoliday($oneCoachPeriod = array()) {
	//1.将日期时间戳数组转换成时间戳字符串
	$searchCoachPeriod = '';//远程请求字符串
	foreach ($oneCoachPeriod as $key=>$value){
		foreach ($value as $k=>$v) {
			$searchCoachPeriod .= $v.',';
		}
	}
	$searchCoachPeriod = substr($searchCoachPeriod, 0, -1);
	//2.判断日期时间戳是否是节假日
	$daysHoliday = whetherHoliday($searchCoachPeriod);
	return objectToArray($daysHoliday);
}

//高精度计算（加，减）
function numberFormat($action, $number1, $number2, $decimal=0) {
	$decimal = $decimal>0 ? $decimal : getFloatLength($number1) > getFloatLength($number2) ? getFloatLength($number1) : getFloatLength($number2);
	if(!$decimal){
		switch ($action){
			case '-':
				return $number1 - $number2;
				break;
			case '+':
				return $number1 + $number2;
				break;
		}
	}else{
		switch ($action){
			case '-':
				return bcsub($number1,$number2,$decimal);
				break;
			case '+':
				return bcadd($number1,$number2,$decimal);
				break;
		}
	}
}

//求小数点的位数
function getFloatLength($num){
	$count = 0;
	$temp = explode ('.',$num);
	if (sizeof($temp)>1) {
		$decimal = end ($temp);
		$count = strlen ($decimal);
	}
	return $count;
}

//激活码生成
function createActivecode($ids, $time) {
	return sha1($ids.','.$time);
}

/**
 * 将二维数组转换成一维数组
 * @param array $array 待处理数组
 */
function doArrayAction($array = array()) {
    $newArr = [];
    foreach ($array as $key=>$value) {
        $newArr[] = $value['pos'];
    }
    return $newArr;
}

/**
 * 删除数组中小于给定值的元素
 */
function deleteMinArray($arr = array(), $min) {
    $nArr = [];
    foreach ($arr as $value) {
        if($value>=$min) $nArr[] = $value;
    }
    return $nArr;
}

/**
 * 判断$a是否大于$b，若大于返回true,小于返回false
 * @param string $a
 * @param string $b
 * @return boolean
 */
function isBig($a, $b) {
    return $a>$b ? true : false;
}

/**
 * 4.手机短信验证码
 * @param string $phone 手机号码
 * @param string $activecode 短信验证码
 * @param string $templateId 调取短信的模板ID
 * @param string $time 验证码有效时间，默认为60秒
 * @return boolean 返回请求的状态码，请求成功的话，则返回000000，否则返回不成功
 */
function requestSMSResponseCode($phone, $activecode, $templateId, $time = 120) {
    if(empty($phone) || empty($activecode) || empty($templateId)) return false;
    //短信插件初始化必填项
    $options['accountsid'] = C('UCPAAS_CONFIG')['accountsid'];
    $options['token'] = C('UCPAAS_CONFIG')['token'];

    //初始化 $options必填项
    import('Vendor.Ucpaas.Ucpaas');
    $ucpass = new Ucpaas($options);
    //短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
    $appId = C('UCPAAS_CONFIG')['appid'];
    $param = $activecode.','.$time;//$param="test,1256,3";参数可以自定义
    $json = $ucpass->templateSMS($appId, $phone, $templateId, $param);
    $responeArr = '['.$json.']';
    $reponse = json_decode($responeArr);
    return $reponse[0]->resp->respCode == '000000' ? true : false;//105111,短信模板没有通过验证
}

/**
 * 4.手机短信提示 - 管理员提现提示
 * @param string $phone 手机号码
 * @param string $activecode 短信验证码
 * @param string $templateId 调取短信的模板ID
 * @param array $data 数组，包括真实姓名，提现时间，提现金额，手续费，实际到账金额等五个参数。
 * @return boolean 返回请求的状态码，请求成功的话，则返回000000，否则返回不成功
 */
function requestManageResponseCode($phone, $templateId, $data = array()) {
    if(empty($phone) || empty($data) || empty($templateId)) return false;
    //短信插件初始化必填项
    $options['accountsid'] = C('UCPAAS_CONFIG')['accountsid'];
    $options['token'] = C('UCPAAS_CONFIG')['token'];

    //初始化 $options必填项
    import('Vendor.Ucpaas.Ucpaas');
    $ucpass = new Ucpaas($options);
    //短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
    $appId = C('UCPAAS_CONFIG')['appid'];
    $param = $data['realname'].','.$data['create'].','.$data['amount'].','.$data['fee'].','.$data['shiji'];//$param="test,1256,3";参数可以自定义
    $json = $ucpass->templateSMS($appId,$phone,$templateId,$param);
    $responeArr = '['.$json.']';
    $reponse = json_decode($responeArr);
    return $reponse[0]->resp->respCode == '000000' ? true : false;//105111,短信模板没有通过验证
}

/**
 * 4.手机短信提示 - 系统充值提示
 * @param string $phone 手机号码
 * @param string $activecode 短信验证码
 * @param string $templateId 调取短信的模板ID
 * @param array $data 数组，包括真实姓名，提现时间，提现金额，手续费，实际到账金额等五个参数。
 * @return boolean 返回请求的状态码，请求成功的话，则返回000000，否则返回不成功
 */
function requestChargeResponseCode($phone, $templateId, $data = array()) {
    if(empty($phone) || empty($data) || empty($templateId)) return false;
    //短信插件初始化必填项
    $options['accountsid'] = C('UCPAAS_CONFIG')['accountsid'];
    $options['token'] = C('UCPAAS_CONFIG')['token'];

    //初始化 $options必填项
    import('Vendor.Ucpaas.Ucpaas');
    $ucpass = new Ucpaas($options);
    //短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
    $appId = C('UCPAAS_CONFIG')['appid'];
    $param = $data['realname'].','.$data['create'].','.$data['amount'];//$param="test,1256,3";参数可以自定义
    $json = $ucpass->templateSMS($appId,$phone,$templateId,$param);
    $responeArr = '['.$json.']';
    $reponse = json_decode($responeArr);
    return $reponse[0]->resp->respCode == '000000' ? true : false;//105111,短信模板没有通过验证
}

/**
 * 5.根据字段获取短信的模板ID,$type表示操作字段
 * @param string $type 短信模板类型
 * @return number 服务器短信模板id
 */
function autoGetTemplateId($type){
    switch ($type) {
        case 'phone'://绑定手机号
            $templateId = 37248;
            break;
        case 'register'://会员注册
            $templateId = 37249;
            break;
        case 'resellersignup'://分销商申请
            $templateId = 37250;
            break;
        case 'merchantsignup'://招商员申请
            $templateId = 37251;
            break;
        case 'shopsignup'://商家入驻申请
            $templateId = 37252;
            break;
        case 'shopcashout'://商家提现提醒
            $templateId = 37968;
            break;
        case 'huokuan'://商家货款到账
            $templateId = 39163;
            break;
        case 'cashout'://余额提现提醒
            $templateId = 39652;
            break;
    }
    return $templateId;
}

/**
 * 获取当前月的上一月或下一月
 * @param string $sign 标记，默认为1，表示获取上一个月的月份，0表示下一个月的月份
 * @return string 月份
 */
function GetMonth($sign=1){
    //得到系统的年月
    $tmp_date=date("Ym");
    //切割出年份
    $tmp_year=substr($tmp_date,0,4);
    //切割出月份
    $tmp_mon =substr($tmp_date,4,2);
    $tmp_nextmonth=mktime(0,0,0,$tmp_mon+1,1,$tmp_year);
    $tmp_forwardmonth=mktime(0,0,0,$tmp_mon-1,1,$tmp_year);
    if($sign==0){
        //得到当前月的下一个月
        return $fm_next_month=date("Y-m",$tmp_nextmonth);
    }else{
        //得到当前月的上一个月
        return $fm_forward_month=date("Y-m",$tmp_forwardmonth);
    }
}

/**
 * 根据IP地址获取当前所在的城市区
 * stdClass Object
 (
 [errNum] => 0
 [errMsg] => success
 [retData] => stdClass Object
 (
 [ip] => 58.250.95.199
 [country] => 中国
 [province] => 广东
 [city] => 深圳
 [district] => 宝安
 [carrier] => 中国联通
 )
 )
 */
function getPositionCitywithIP() {
    $ip = get_client_ip(0,true);
    $ch = curl_init();
    $url = 'http://apis.baidu.com/apistore/iplookupservice/iplookup?ip='.$ip;
    $header = array(
        'apikey: 5ed6d2accf8e6f30142806f5874e1a14',
    );
    // 添加apikey到header
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 执行HTTP请求
    curl_setopt($ch, CURLOPT_URL, $url);
    $res = curl_exec($ch);
    $object = json_decode($res);
    return $object->retData->country.' '.$object->retData->province.' '.$object->retData->city.' '.$object->retData->district;
}

//图片对象格式化,将图片信息保存到数据库需要，$type为false时表示是图片集合的处理，true时表示普通图片的处理，保存大图和小图
function imgObjFormat($str,$type=true) {
    if(empty($str)) return;
    if($type){
        if(is_array($str)){//数组的情况
            $obj1 = json_decode($str[0]);
            if(!empty($str[1])) $obj2 = json_decode($str[1]);
            if($obj1->url){
                if(!empty($str[1])){
                    $obj2 = json_decode($str[1]);
                    if($obj2->url){
                        return '{"big":'.$str[0].',"small":'.$str[1].'}';
                    }else{
                        $small = objectToArray($obj2)['image'];
                        return '{"big":'.$str[0].',"small":'.json_encode($small).'}';
                    }
                }else{
                    return '{"big":'.$str[0].'}';
                }
            }else if ($obj2 && $obj2->url){
                $big = objectToArray($obj1)['image'];
                return '{"big":'.json_encode($big).',"small":'.$str[1].'}';
            }else{
                $big = objectToArray($obj1)['image'];
                if(!empty($str[1])){
                    $obj2 = json_decode($str[1]);
                    $small = objectToArray($obj2)['images'];
                    return json_encode(array(
                        'big'=>$big,
                        'small'=>$small
                    ));
                }else{
                    return json_encode(array(
                        'big'=>$big
                    ));
                }
            }
        }else{//字符串的情况
            $obj = json_decode($str);
            return $obj->url ? $str : json_encode(objectToArray($obj)['images']);
        }
    }else{//图片集合处理
        if(is_array($str)){
            $array = array();
            foreach ($str as $key=>$value){
                $obj = json_decode($value);
                if(is_object($obj) && $obj->source){
                    $array[] = $obj;
                }else if(is_array($obj)){
                    $array = $obj;
                }else{
                    $array[] = $obj->source ? $value : objectToArray($obj)['images'];
                }
            }
            return json_encode($array);
        }
    }
}

/**
 * 去掉奖金后的.00
 * @param $data 数组或字符串数字
 * 返回格式化后的数组或数字
 */
function numberToFloatval($data) {
    if(is_array($data)) {//数组
        foreach ($data as $key=>$value) {
            if(is_numeric($value)) $data[$key] = floatval($value);
        }
        return $data;
    }else if(is_numeric($data)) {//字符串数字
        return floatval($data);
    }
}

/**
 * @param string $uniqid
 */
function isUniqidEq($uniqid = '') {
    if(!empty($_SESSION['form-token']) && !empty($uniqid) && $_SESSION['form-token'] == $uniqid) {
        return true;
    }else {
        return false;
    }
}

/**
 * 验证是否有跳转的权限
 */
function isAuthRedirect() {
    if(!empty(session('auth_redirect')) && strlen(session('auth_redirect')) == 40) {
        return true;
    }else {
        return false;
    }
}

/**
 * 促销券有效期
 */
function promotionExpire($expireupgrade = 3) {
    return strtotime(date('Y-m-d', strtotime('+'.$expireupgrade.' month +1 day', NOW_TIME)))-1;
}

/**
 * 获取当前网页的URL
 * @return string
 */
function getWebUrl() {
    $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
    return $http_type.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

/**
 * 创建店铺买单二维码
 * @param $storeid 店铺id
 */
function createQrcode($url, $storeid) {
    $qrimg = C('STORE_PATH').md5($storeid).'.png';
    if(!file_exists(mb_convert_encoding($qrimg , 'gbk' , 'utf-8'))) {
        $level = 3;
        $size = 10;
        Vendor('phpqrcode.phpqrcode');
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        //生成二维码图片
        $object = new \QRcode();
        $object->png($url, $qrimg, $errorCorrectionLevel, $matrixPointSize, 2);
    }
    return C('SITE_URL').substr($qrimg, 2);
}

/**
 * 下载微信公众号生成的场景二维码
 * @param $url 链接 如:https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=gQFK8DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL3kweXE0T3JscWY3UTltc3ZPMklvAAIEG9jUUgMECAcAAA%3d%3d
 * @return array
 */
function downloadImageFromWechat($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_NOBODY, 0);    //只取body头
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $package = curl_exec($ch);
    $httpinfo = curl_getinfo($ch);
    curl_close($ch);
    return array_merge(array('body' => $package), array('header' => $httpinfo));
}

/**
 * 获取评分html
 * @param $star 评分
 */
function getStarHtml($star) {
    if($star > 4.5) {
        $nstar = 5;
    }else if($star <= 4.5 && $star > 4) {
        $nstar = 4.5;
    }else if($star <= 4 && $star > 3.5) {
        $nstar = 4;
    }else if($star <= 3.5 && $star > 3) {
        $nstar = 3.5;
    }else if($star <= 3 && $star > 2.5) {
        $nstar = 3;
    }else if($star <= 2.5 && $star > 2) {
        $nstar = 2.5;
    }else if($star <= 2 && $star > 1.5) {
        $nstar = 2;
    }else if($star <= 1.5 && $star > 1) {
        $nstar = 1.5;
    }else if($star <= 1 && $star > 0.5) {
        $nstar = 1;
    }else {
        $nstar = 0.5;
    }
    $starhtml = '';
    switch ($nstar) {
        case 5:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><em class="star-text">'.$star.'</em>';
            break;
        case 4.5:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"><i class="ion-android-star icon-star-half"></i></i><em class="star-text">'.$star.'</em>';
            break;
        case 4:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 3.5:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"><i class="ion-android-star icon-star-half"></i></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 3:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 2.5:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"><i class="ion-android-star icon-star-half"></i></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 2:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 1.5:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"><i class="ion-android-star icon-star-half"></i></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 1:
            $starhtml = '<i class="ion-android-star icon-star"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
        case 0.5:
            $starhtml = '<i class="ion-android-star icon-star-gray"><i class="ion-android-star icon-star-half"></i></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><i class="ion-android-star icon-star-gray"></i><em class="star-text">'.$star.'</em>';
            break;
    }
    return $starhtml;
}

/**
 * 根据模板字段获取模板ID
 * @param $type 模板类型
 * @return int
 */
function autoGetWXTemplateId($type){
    switch ($type) {
        case 'TX001'://提现提交通知
            $templateId = 'W5A6l5AQPqWT7Ft1N7KpvzuEfUZlSm6OeMk2h26YDHk';
            break;
        case 'TX002'://提现成功通知
            $templateId = 'fI21j5b3oboacFUDgcZhmEvhU7jpAeGjx_xrj8QxIQ4';
            break;
        case 'Order001'://创建订单成功通知
            $templateId = '5Vlh8fT8xeTdNHXHLZyftTJ0dpRGKVXFIj75l7IWjiM';
            break;
        case 'Order002'://订单支付成功通知
            $templateId = 'bHo1Z1AmKzZ7OUJ7BrALdCGPLeoIWDlXQX86wKuLLlU';
            break;
        case 'Order003'://订单确认发货通知
            $templateId = '68L0PKhjYAzffwI3xIDs1Kxx39aS2qfm4bFW45S3doI';
            break;
        case 'Order004'://订单确认收货通知
            $templateId = 'n5HzSCIl5uehMx-uKBJpWJ1feSrgDBa4SGvcNWA8Fpg';
            break;
        case 'Re001'://充值成功通知
            $templateId = 'su6iYc9lkay5MKw1AqJ1DS8D-aUqlxF3FTX-E1gU1Ws';
            break;
    }
    return $templateId;
}

/**
 * 计算两个日期之间的相差天数 今天与指定日期相差多少天
 * @param $a
 * @param $b
 * @return float
 */
function count_days($a,$b){
    $a_dt = getdate($a);
    $b_dt = getdate($b);
    $a_new = mktime(12, 0, 0, $a_dt['mon'], $a_dt['mday'], $a_dt['year']);
    $b_new = mktime(12, 0, 0, $b_dt['mon'], $b_dt['mday'], $b_dt['year']);
    return round(abs($a_new-$b_new)/86400);
}