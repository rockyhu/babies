$(function () {
	//管理员管理
	var nav_action = {
		//元素初始化
		elements_init : function () {
			//管理员列表
			if($('#table-manage').length) var dataTable = baseTable.init($('#table-manage'), ThinkPHP['Managelist']);
	        //管理员登陆日志
			if($('#table-loginlog').length) baseTable.init($('#table-loginlog'), ThinkPHP['LoginLog']);
			
	        //Initialize Select2 Elements - add
			if($(".select2").length){
				$(".select2").length && $(".select2").select2({
		        	language: "zh-CN",
		        	ajax: {
		        		url : ThinkPHP['MODULE'] + '/AuthGroup/getListAll', 
		                delay: 250,
		                processResults: function (data) {
		                    return {results: data};
		                }
		            }
		        });
			}
			
			//删除操作
			$('#table-manage').on('click', '.del-btn', function () {
				var id = $(this).data('id');
				$.confirm({
				    title: '确认框',
				    content: '你确认要删除这条数据吗？',
				    confirmButton : '确认',
				    confirmButtonClass : 'btn-success',
				    cancelButton : '取消',
				    confirm: function(){
				        $.ajax({
							url : ThinkPHP['MODULE'] + '/Manage/remove',
							data : {id:id},
							type: 'POST',
							beforeSend: function(jqXHR, settings){
								$('#loading').addClass('show');
							},
							success: function(response, textStatus, jqXHR){
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('删除成功~');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-check-circle fa-2x success');
									$('#loading').find('p').html('正在处理中...');
									if(response != 0) dataTable.row('.active').remove().draw(true);
								}, 500);
							}
						});
				    },
				    cancel: function(){}
				});
			});
			
			if($('#manage-add').length || $('#manage-edit').length){
				//验证select2下拉菜单必须选择
				$.validator.addMethod("isSelect2", function(value, element) {
					return (value == '' || value == -1 || value == null) ? false : true;
				}, "请选择");
				$('.select2-hidden-accessible').on('change', function (e) {
					if($(this).val() != '') {
						$(this).parent().removeClass('has-error');
						$(this).nextAll('span.errorLabel').hide();
						$(this).parent().find('.select2-selection').css('borderColor', '#d2d6de');
					}
				});
			}
			
		},
		//新增管理员
		add_nav : function () {
			$('#manage-add').length && $('#manage-add').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Manage/addManage',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#manage-add-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['MANAGE'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#manage-add-btn').removeClass('disabled');
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
					if($(element).hasClass('select2-hidden-accessible')){
						$(element).parent().find('.select2-selection').css('borderColor', '#dd4b39');
					}
				},
				unhighlight : function (element,errorClass) {
					//取消错误样式
					$(element).parent().removeClass('has-error');
					if($(element).hasClass('select2-hidden-accessible')){
						$(element).parent().find('.select2-selection').css('borderColor', '#d2d6de');
					}
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
                    realname : {
                        required : true
                    },
					password : {
						required : true,
						rangelength : '[6,30]'
					},
					repassword : {
						required : true,
						equalTo : '#password'
					},
					role : {
						isSelect2 : true
					}
				},
				messages : {
					manager : {
						required : '请输入管理员账号',
						email : '管理员账号必须是邮件'
					},
                    realname : {
                        required : '请输入管理员真实姓名'
                    },
					password : {
						required : '请输入管理员登录密码',
						rangelength : $.validator.format('管理员登录密码长度必须在{0}-{1}位之间')
					},
					repassword : {
						required : '请输入确认密码',
						equalTo : '确认密码和登录密码不一致，请重新输入'
					},
					role : {
						isSelect2 : '请为管理员分配角色'
					}
				}
			});
		},
		//编辑管理员
		edit_nav : function () {
			$('#manage-edit').length && $('#manage-edit').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Manage/update',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#manage-edit-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['MANAGE'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#manage-edit-btn').removeClass('disabled');
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
					if($(element).hasClass('select2-hidden-accessible')){
						$(element).parent().find('.select2-selection').css('borderColor', '#dd4b39');
					}
				},
				unhighlight : function (element,errorClass) {
					//取消错误样式
					$(element).parent().removeClass('has-error');
					if($(element).hasClass('select2-hidden-accessible')){
						$(element).parent().find('.select2-selection').css('borderColor', '#d2d6de');
					}
				},
				//自定义错误显示的位置
				errorPlacement : function (error, element) {
					error.appendTo($(element).nextAll('span.errorLabel'));
				},
				rules : {
                    realname : {
                        required : true
                    },
					password : {
						rangelength : '[6,30]'
					},
					repassword : {
						equalTo : '#password'
					},
					role : {
						isSelect2 : true
					}
				},
				messages : {
                    realname : {
                        required : '请输入管理员真实姓名'
                    },
					password : {
						rangelength : $.validator.format('管理员登录密码长度必须在{0}-{1}位之间')
					},
					repassword : {
						equalTo : '确认密码和登录密码不一致，请重新输入'
					},
					role : {
						isSelect2 : '请为管理员分配角色'
					}
				}
			});
		},
		//页面初始化
		init:function(){
			nav_action.elements_init();
			nav_action.add_nav();
			nav_action.edit_nav();
		}
	};
	//运行
	nav_action.init();
});