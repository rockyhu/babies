<?php
return array(
	//设置可访问目录
	'MODULE_ALLOW_LIST'=>array('Home','Admin'),
	//设置默认目录
	'DEFAULT_MODULE'=>'Home',
	//设置模板后缀
	'TMPL_TEMPLATE_SUFFIX'=>'.tpl',
	//设置默认主题目录
	'DEFAULT_THEME'=>'Default',
	//数据库配置
    'DB_TYPE'=>'mysql',
    'DB_HOST'=>'localhost',
    'DB_NAME'=>'babies',
    'DB_USER'=>'root',
    'DB_PWD'=>'',
    'DB_PREFIX'=>'babies_',
    'DB_CHARSET'=>'utf8',
    'DB_PARAMS'=>array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),
	//重写模式
	'URL_MODEL'=>2,
	//配置SESSION,将SESSION保存目录设置到App/Runtime/Temp下(很重要)
	'SESSION_OPTIONS'=>array(
		'path'=>RUNTIME_PATH.'Temp',
	),

    //显示页面Trace信息
    //'SHOW_PAGE_TRACE' =>true,
	
	//系统网址
    'SITE_URL'=>'http://localhost/babies/',

	//图片上传路径
	'UPLOAD_PATH'=>'./Uploads/',

		
	//缓存设置
	'DATA_CACHE_TYPE'=>'File',//缓存类型
	'DATA_CACHE_TIME'=>3600*24,//缓存时间,3600表示一个小时,3600*24*365表示缓存一年
	'DATA_CACHE_KEY'=>'http://www.gov-fund.com/',//文件缓存方式的安全机制

	//设置管理员邮箱
	'DEFAULT_MAIL'=>'916772355@qq.com',
    
    //云之讯短信服务信息配置
    'UCPAAS_CONFIG'=>array(
        'accountsid'=>'9143f6b35ad7a00a6c2d5d719f9fc092',//accountsid
        'token'=>'82c468819543efd3407e67d9753d37cf',//token
        'appid'=>'adeac17985354ed5987925e80f706a96'//应用id
    )
	
);