$(function () {
    //系统参数
    var system_action = {
        //元素初始化
        elements_init : function  () {
            $('input[name="expireupgrade"]').keyup(function () {
                var expireupgradeval = $(this).val();
                if(expireupgradeval != '') {
                    expireupgradeval = parseInt(expireupgradeval)+1;
                }
                $('input[name="expireupgradenext"]').val(expireupgradeval);
            });
        },
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
                    doublerate : {
                        required : true,
                        number: true,
                        range: [0,1]
                    },
                    jicharate : {
                        required : true,
                        number: true,
                        range: [0,1]
                    },
                    maxzhekoufee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    minicashout : {
                        required : true,
                        digits: true,
                        range: [100,1000]
                    },
                    guanlifee : {
                        required : true,
                        digits: true,
                        range: [10,200]
                    },
                    cashoutfee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    minitransfer : {
                        required : true,
                        digits: true,
                        range: [0,1000]
                    },
                    wuliufee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    expirepromotion : {
                        required : true,
                        digits: true,
                        range: [1,12]
                    },
                    senioritynum : {
                        required : true,
                        digits: true,
                        range: [1,12]
                    },
                    expireupgrade : {
                        required : true,
                        digits: true,
                        range: [1,12]
                    },
                    hegepv : {
                        required : true,
                        digits: true,
                        range: [1,10000]
                    },
                    tichenggeshui : {
                        required : true,
                        digits: true,
                        range: [0,100000]
                    },
                    geshuifee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    tichenggouwu : {
                        required : true,
                        digits: true,
                        range: [0,100000]
                    },
                    gouwufee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    gouwumax : {
                        required : true,
                        digits: true,
                        range: [0,10000]
                    },
                    tichengpeixun : {
                        required : true,
                        digits: true,
                        range: [0,100000]
                    },
                    peixunfee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    maxdaili : {
                        required : true,
                        digits: true,
                        range: [1,500]
                    },
                    dailifee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    maxtouzi : {
                        required : true,
                        digits: true,
                        range: [1,500]
                    },
                    touzifee : {
                        required : true,
                        number: true,
                        range: [0,100]
                    },
                    shutdowntitle : {
                        required : true
                    },
                    shutdowncontent : {
                        required : true
                    }
                },
                messages : {
                    doublerate : {
                        required : '请输入代理商双规政策所在比例',
                        number: '代理商双规政策所在比例必须是数字',
                        range : $.validator.format('代理商双规政策所在比例必须在{0}-{1}之间')
                    },
                    jicharate : {
                        required : '请输入提现手续费',
                        number: '提现手续费必须是数字',
                        range : $.validator.format('提现手续费必须在{0}-{1}之间')
                    },
                    maxzhekoufee : {
                        required : '请输入购物折扣最大拨出比例',
                        number: '购物折扣最大拨出比例必须是数字',
                        range : $.validator.format('购物折扣最大拨出比例必须在{0}-{1}之间')
                    },
                    minicashout : {
                        required : '请输入最低提现金额',
                        digits: '最低提现金额必须是数字',
                        range : $.validator.format('最低提现金额必须是{0}')
                    },
                    cashoutfee : {
                        required : '请输入提现手续费',
                        number: '提现手续费必须是数字',
                        range : $.validator.format('提现手续费必须在{0}-{1}之间')
                    },
                    minitransfer : {
                        required : '请输入最低转账金额',
                        digits: '最低转账金额额必须是数字',
                        range : $.validator.format('最低转账金额必须是{0}')
                    },
                    wuliufee : {
                        required : '请输入物流补贴比例',
                        number: '物流补贴比例必须是数字',
                        range : $.validator.format('物流补贴比例必须在{0}-{1}之间')
                    },
                    guanlifee : {
                        required : '请输入网络管理费金额',
                        digits: '网络管理费金额必须是数字',
                        range : $.validator.format('网络管理费金额必须在{0}-{1}之间')
                    },
                    expirepromotion : {
                        required : '请输入电子促销币有效期',
                        digits: '电子促销币有效期必须是数字',
                        range : $.validator.format('电子促销币有效期必须在{0}-{1}之间')
                    },
                    senioritynum : {
                        required : '请输入经销商经营资格数量',
                        digits: '经销商经营资格数量必须是数字',
                        range : $.validator.format('经销商经营资格数量必须在{0}-{1}之间')
                    },
                    expireupgrade : {
                        required : '请输入补差额升级的月份',
                        digits: '补差额升级的月份必须是数字',
                        range : $.validator.format('补差额升级的月份必须在{0}-{1}之间')
                    },
                    hegepv : {
                        required : '请输入合格经销商业绩PV',
                        digits: '合格经销商业绩PV必须是数字',
                        range : $.validator.format('合格经销商业绩PV必须在{0}-{1}之间')
                    },
                    tichenggeshui : {
                        required : '请输入个税周提成',
                        digits: '个税周提成必须是数字',
                        range : $.validator.format('个税周提成必须在{0}-{1}之间')
                    },
                    geshuifee : {
                        required : '请输入个人所得税比例',
                        number: '个人所得税比例必须是数字',
                        range : $.validator.format('个人所得税比例必须在{0}-{1}之间')
                    },
                    tichenggouwu : {
                        required : '请输入消费储备周提成',
                        digits: '消费储备周提成必须是数字',
                        range : $.validator.format('消费储备周提成必须在{0}-{1}之间')
                    },
                    gouwufee : {
                        required : '请输入消费储备比例',
                        number: '消费储备比例必须是数字',
                        range : $.validator.format('消费储备比例必须在{0}-{1}之间')
                    },
                    gouwumax : {
                        required : '请输入周消费储备最大值',
                        digits: '周消费储备最大值必须是数字',
                        range : $.validator.format('周消费储备最大值必须在{0}-{1}之间')
                    },
                    tichengpeixun : {
                        required : '请输入培训基金周提成',
                        digits: '培训基金周提成必须是数字',
                        range : $.validator.format('培训基金周提成必须在{0}-{1}之间')
                    },
                    peixunfee : {
                        required : '请输入培训基金比例',
                        number: '培训基金比例必须是数字',
                        range : $.validator.format('培训基金比例必须在{0}-{1}之间')
                    },
                    maxdaili : {
                        required : '请输入代理分红的人数',
                        digits: '代理分红的人数必须是数字',
                        range : $.validator.format('代理分红的人数必须在{0}-{1}之间')
                    },
                    dailifee : {
                        required : '请输入代理分红的比例',
                        number: '代理分红的比例必须是数字',
                        range : $.validator.format('代理分红的比例必须在{0}-{1}之间')
                    },
                    maxtouzi : {
                        required : '请输入投资分红的人数',
                        digits: '投资分红的人数必须是数字',
                        range : $.validator.format('投资分红的人数必须在{0}-{1}之间')
                    },
                    touzifee : {
                        required : '请输入投资分红的比例',
                        number: '投资分红的比例必须是数字',
                        range : $.validator.format('投资分红的比例必须在{0}-{1}之间')
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
            system_action.elements_init();
            system_action.system_set();
        }
    };
    //运行
    system_action.init();
});