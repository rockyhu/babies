$(function () {
	//管理员登录
	var form_signin = {
		//radio按钮初始化
		radio_init : function () {
			$('input').iCheck({
				checkboxClass: 'icheckbox_square-blue',
				radioClass: 'iradio_square-blue',
				increaseArea: '20%' // optional
			});
		},
		//动态背景图
		backstretch : function () {
			// Duration is the amount of time in between slides,
			// and fade is value that determines how quickly the next image will fade in
			$.backstretch([
				ThinkPHP['IMG']+"/bg/2.jpg",
				ThinkPHP['IMG']+"/bg/4.jpg",
				ThinkPHP['IMG']+"/bg/3.jpg",
				ThinkPHP['IMG']+"/bg/1.jpg"
			], {duration: 5000, fade: 750});
		},
		//管理员登录
		signin_login : function () {
			$('#signin').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Login/checkManager',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#signbtn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('登录成功');
								setTimeout(function () {
									$('#loading').find('.ui-loading-bright').removeClass('no-image fa fa-check-circle fa-2x success');
									$('#loading').find('p').html('正在跳转中...');
									setTimeout(function () {
										location.href = ThinkPHP['INDEX'];
									},200);
								},800);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('登录失败');
								setTimeout(function () {
									$('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#signbtn').removeClass('disabled');
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
					error.appendTo($(element).nextAll('span.errorLabel'));
				},
				rules : {
					manager : {
						required : true,
						email : true
					},
					password : {
						required : true,
						rangelength : [6,30]
					}
				},
				messages : {
					manager : {
						required : '请输入正确的电子邮件',
						email : 'Email邮件格式不合法'
					},
					password : {
						required : '请输入正确的密码',
						rangelength : $.validator.format('密码长度必须在{0}至{1}位之间！')
					}
				}
			});
		},
		init:function(){
			form_signin.radio_init();
			form_signin.signin_login();
			form_signin.backstretch();
		}
	};
	form_signin.init();
	
});