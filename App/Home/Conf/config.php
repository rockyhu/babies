<?php
return array(
	//设置模板替换变量
	'TMPL_PARSE_STRING'=>array(
		'__BASE__'=>__ROOT__.'/Public/Base',
		'__JS__'=>__ROOT__.'/Public/'.MODULE_NAME.'/js',
		'__CSS__'=>__ROOT__.'/Public/'.MODULE_NAME.'/css',
		'__IMG__'=>__ROOT__.'/Public/'.MODULE_NAME.'/img'
	),

    //最大允许上传图片的大小
    'Image_Max_Size'=>10485760,//10M
    //图片上传路径
    'UPLOAD_PATH'=>'./Uploads/',
    
	//页面调试
	//'SHOW_PAGE_TRACE'=>true,
	//COOKIE密钥
	'COOKIE_KEY'=>'http://www.gov-fund.com/',
	//默认错误跳转错误的模板页面
	//'TMPL_ACTION_ERROR'=>'Public/jump',
	//默认成功跳转的模板页面
	//'TMPL_ACTION_SUCCESS'=>'Public/jump',
	
	//启用路由功能
	'URL_ROUTER_ON'=>true,
	//配置路由规则
	'URL_ROUTE_RULES'=>array(
		//每条键值对，对应一个路由规则
		'i/:domain'=>'Space/index',
		//对应的URL地址是Space/index/i/xiaoxin
	),
	
	//新浪apiKey,用于长链接和短链接之间转换
	'WEIBO_APIKEY'=>'1619959663',
	
	//邮件服务器设置
	'MAIL_CONFIG'=>array(
		'host'=>'smtp.exmail.qq.com',//smtp.qq.com
		'username'=>'p2p@qq959.com',//邮箱名
		'from'=>'p2p@qq959.com',//发件邮箱地址，即邮箱名
		'fromname'=>'数神传奇文化',//发件人姓名
		'password'=>'sky33654811',//发送邮件账号密码
	),
	
	//接受系统订单提示邮箱,可以是多个
	'RECEIVE_MAIL'=>array(
		'916772355@qq.com'
	),
);