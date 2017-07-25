$(function () {
	//首页
	var index_action = {
	    index : {
            //图片延迟加载
            imageLazyLoad : function (index) {
                var index = index || 0,
                    img_loading = index ? $("img.img-loading"+index) : $("img.img-loading");
                img_loading.lazyload({
                    threshold:100,//提前多少像素(px)加载
                    effect:"fadeIn",//加载使用的动画效果，如 show, fadeIn, slideDown 等jQuery自带的效果.
                    effectspeed:5,//动画时间.
                });
            },
            //初始化
            init : function () {
                //this.imageLazyLoad();
            }
        },
		init:function(){
            this.index.init();
		}
	};
	index_action.init();
});