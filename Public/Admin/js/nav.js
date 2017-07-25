$(function () {
	//栏目管理
	var nav_action = {
		//元素初始化
		elements_init : function () {
			//DataTable init - list
			if($('#table-navs').length) var dataTable = baseTable.init($('#table-navs'), ThinkPHP['Navlist']);
	        
	        //Initialize Select2 Elements - add
			if($(".select2").length){
				$(".select2").select2({
		        	language: "zh-CN",
		        	ajax: {
		                url: ThinkPHP['MODULE'] + '/Nav/getListMain/id/'+$('#nav-v2-edit-id').val(),
		                delay: 250,
		                processResults: function (data) {
		                    return {results: data};
		                },
		            }
		        });
			}
			
			if($(".select3").length) $(".select3").select2();
			
			//删除操作
			$('#table-navs').on('click', '.del-btn', function () {
				var id = $(this).data('id');
				$.confirm({
				    title: '确认框',
				    content: '你确认要删除这条数据吗？',
				    confirmButton : '确认',
				    confirmButtonClass : 'btn-success',
				    cancelButton : '取消',
				    confirm: function(){
				        $.ajax({
							url : ThinkPHP['MODULE'] + '/Nav/remove',
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
			
			if($('#nav-add').length || $('#nav-edit').length) {
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
		//新增栏目
		add_nav : function () {
			$('#nav-add').length && $('#nav-add').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Nav/addNav',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#nav-add-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['NAV'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#nav-add-btn').removeClass('disabled');
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
					nid : {
						//isSelect2 : true
					},
					text : {
						required : true,
						rangelength : '[1,20]'
					},
					iconCls : {
						required : true
					}
				},
				messages : {
					nid : {
						//isSelect2 : '请选择菜单'
					},
					text : {
						required : '请输入菜单名称',
						rangelength : $.validator.format('菜单名称必须在{0}-{1}位之间')
					},
					iconCls : {
						required : '请输入菜单图标'
					}
				}
			});
		},
		//编辑栏目
		edit_nav : function () {
			$('#nav-edit').length && $('#nav-edit').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Nav/update',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#nav-edit-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['NAV'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#nav-edit-btn').removeClass('disabled');
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
					nid : {
						//isSelect2 : true
					},
					text : {
						required : true,
						rangelength : '[1,20]'
					},
					iconCls : {
						required : true
					}
				},
				messages : {
					nid : {
						//isSelect2 : '请选择菜单'
					},
					text : {
						required : '请输入菜单名称',
						rangelength : $.validator.format('菜单名称必须在{0}-{1}位之间')
					},
					iconCls : {
						required : '请输入菜单图标'
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