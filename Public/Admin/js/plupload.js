$(function () {
	var plupload_action = {
		//页面初始化
		init:function(){
		    //缩略图上传
            Plupload.create({
                buttonid : 'plupload-thumb-btn',
                multi : false
            });
		    //批量图片上传
            Plupload.create({
                buttonid : 'plupload-select-files',
                multi : true,
                total : 9
            });
		}
	};
	//运行
	plupload_action.init();
});