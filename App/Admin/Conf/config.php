<?php
return array(
	//设置模板替换变量
	'TMPL_PARSE_STRING'=>array(
		'__BASE__'=>__ROOT__.'/Public/Base',
		'__JS__'=>__ROOT__.'/Public/'.MODULE_NAME.'/js',
		'__CSS__'=>__ROOT__.'/Public/'.MODULE_NAME.'/css',
		'__IMG__'=>__ROOT__.'/Public/'.MODULE_NAME.'/img',
	    '__UPLOADIFY__'=>__ROOT__.'/Public/'.MODULE_NAME.'/uploadify',
	    '__KINDEDITOR__'=>__ROOT__.'/Public/'.MODULE_NAME.'/kindeditor'
	),
	//最大允许上传图片的大小
	'Image_Max_Size'=>1048576,
	//图片上传路径
	'UPLOAD_PATH'=>'./Uploads/',
);