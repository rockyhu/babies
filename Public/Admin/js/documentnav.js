$(function () {
	//产品分类
	var documentnav_action = {
		//元素初始化
		elements_init : function () {
			//DataTable init - list
			if($('#table-documentnavs').length) var dataTable = baseTable.init($('#table-documentnavs'), ThinkPHP['ajaxDocumentNavList']);

            if($(".select3").length) $(".select3").select2();

            if($(".select4").length) {
                $(".select4").select2({
                    language: "zh-CN",
                    multiple: false,
                    closeOnSelect: false,//选择后不关闭下拉框，多选时非常方便
                    ajax: {
                        url: ThinkPHP['MODULE'] + '/DocumentNav/getMainNav',
                        delay: 250,
                        processResults: function (data) {
                            return {results: data};
                        },
                    }
                });
            }

            //初始化图片上传
            if($('#documentnav-add').length || $('#documentnav-edit').length){
                //缩略图上传
                Plupload.create({
                    buttonid : 'plupload-thumb-btn',
                    multi : false
                });
            }

			//删除操作
			$('#table-documentnavs').on('click', '.del-btn', function () {
				var id = $(this).data('id');
				$.confirm({
				    title: '确认框',
				    content: '你确认要删除这条数据吗？',
				    confirmButton : '确认',
				    confirmButtonClass : 'btn-success',
				    cancelButton : '取消',
				    confirm: function(){
				        $.ajax({
							url : ThinkPHP['MODULE'] + '/DocumentNav/remove',
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
			
			if($('#documentnav-add').length || $('#documentnav-edit').length) {
				//验证select2下拉产品分类必须选择
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
		add_documentnav : function () {
			$('#documentnav-add').length && $('#documentnav-add').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/DocumentNav/addDocumentNav',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#documentnav-add-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['DOCUMENTNAV'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#documentnav-add-btn').removeClass('disabled');
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
					text : {
						required : true,
						rangelength : '[1,20]'
					}
				},
				messages : {
					text : {
						required : '请输入产品分类名称',
						rangelength : $.validator.format('产品分类名称必须在{0}-{1}位之间')
					}
				}
			});
		},
		//编辑栏目
		edit_documentnav : function () {
            //初始化
            if($('#documentnav-edit').length){
                var thumb = $('#plupload-thumb-btn').next('.imglist').data('thumb');
                //缩略图
                if(thumb){
                    var source = ThinkPHP['ROOT'] + thumb.source.substr(1);
                    $('#plupload-thumb-btn').next('.imglist').html('<div class="img_upload"><input type="hidden" name="thumb" value='+ JSON.stringify(thumb) +'><img class="img" src="'+ source +'"><span class="img-opacity"></span><i class="ion-android-close remove-btn"></i></div>');
                }
            }
			$('#documentnav-edit').length && $('#documentnav-edit').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/DocumentNav/update',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#documentnav-edit-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['DOCUMENTNAV'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#documentnav-edit-btn').removeClass('disabled');
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
					text : {
						required : true,
						rangelength : '[1,20]'
					}
				},
				messages : {
					text : {
						required : '请输入产品分类名称',
						rangelength : $.validator.format('产品分类名称必须在{0}-{1}位之间')
					}
				}
			});
		},
		//页面初始化
		init:function(){
			documentnav_action.elements_init();
			documentnav_action.add_documentnav();
			documentnav_action.edit_documentnav();
		}
	};
	//运行
	documentnav_action.init();
});