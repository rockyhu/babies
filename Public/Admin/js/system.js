$(function () {
    //系统参数
    var system_action = {
        //系统参数设置
        system_set : function () {
            $('#system-set').length && $('#system-set').validate({
                submitHandler : function (form) {
                    $(form).ajaxSubmit({
                        url: ThinkPHP['MODULE'] + '/System/setSystem',
                        type: 'POST',
                        beforeSubmit: function(formData, jqForm, options){
                            $('#system-set-btn').addClass('disabled');
                            $('#loading').addClass('show');
                        },
                        success: function(responseText, statusText){
                            if (responseText>0) {
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
                                $('#loading').find('p').html('保存成功~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show');
                                    location.reload();
                                },1000);
                            }else{
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('保存失败~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#system-set-btn').removeClass('disabled');
                                },3000);
                            }
                        }
                    });
                },
                showErrors : function(errorMap,errorList) {
                    var errors = this.numberOfInvalids();
                    this.defaultShowErrors();
                },
                highlight : function (element,errorClass) {
                    //错误样式
                    $(element).parent().addClass('has-error');
                },
                unhighlight : function (element,errorClass) {
                    //取消错误样式
                    $(element).parent().removeClass('has-error');
                },
                //自定义错误显示的位置
                errorPlacement : function (error, element) {
                    //error.appendTo($('#error-wrap'));
                },
                errorLabelContainer:"#error-wrap",
                rules : {
                    webname : {
                        required : true,
                        rangelength : '[2,80]'
                    },
                    keywords : {
                        required : true,
                        rangelength : '[2,100]'
                    },
                    description : {
                        required : true,
                        rangelength : '[2,100]'
                    },
                    copyright : {
                        required : true,
                        rangelength : '[2,255]'
                    },
                    beian : {
                        required : true,
                        rangelength : '[2,255]'
                    },
                    shutdowntitle : {
                        required : true
                    },
                    shutdowncontent : {
                        required : true
                    }
                },
                messages : {
                    webname : {
                        required : '请输入站点名称',
                        rangelength : $.validator.format('站点名称必须在{0}-{1}位之间')
                    },
                    keywords : {
                        required : '请输入站点关键字',
                        rangelength : $.validator.format('站点关键字必须在{0}-{1}位之间')
                    },
                    description : {
                        required : '请输入站点描述',
                        rangelength : $.validator.format('站点描述必须在{0}-{1}位之间')
                    },
                    copyright : {
                        required : '请输入站点版权信息',
                        rangelength : $.validator.format('站点版权信息必须在{0}-{1}位之间')
                    },
                    beian : {
                        required : '请输入站点备案信息',
                        rangelength : $.validator.format('站点备案信息必须在{0}-{1}位之间')
                    },
                    shutdowntitle : {
                        required : '请输入维护页面标题'
                    },
                    shutdowncontent : {
                        required : '请输入维护页面内容'
                    }
                }
            });
        },
        //页面初始化
        init:function(){
            system_action.system_set();
        }
    };
    //运行
    system_action.init();
});