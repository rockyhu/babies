$(function () {
	//常见问题管理
	var question_action = {
		//元素初始化
		elements_init : function () {
			//DataTable init - list
			if($('#table-question').length) var noticeTable = baseTable.init($('#table-question'), ThinkPHP['AjaxQuestion']);
			
			//Initialize Select2 Elements - add
			if($(".select2").length){
				$(".select2").select2({
		        	language: "zh-CN"
		        });
			}

            if($('.textarea').length) question_action.kindeditor($('.textarea'));
			
			//删除操作
			$('#table-question').on('click', '.del-btn', function () {
				var id = $(this).data('id');
				$.confirm({
				    title: '确认框',
				    content: '你确认要删除这条数据吗？',
				    confirmButton : '确认',
				    confirmButtonClass : 'btn-success',
				    cancelButton : '取消',
				    confirm: function(){
				        $.ajax({
							url : ThinkPHP['MODULE'] + '/Question/remove',
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
									if(response != 0) noticeTable.row('.active').remove().draw(true);
								}, 500);
							}
						});
				    },
				    cancel: function(){}
				});
			});
			
			if($('#question-add').length || $('#question-edit').length) {
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

            if ($(".select4").length) {
                $(".select4").select2({
                    tags: true,
                    closeOnSelect: true, //选择后不关闭下拉框，多选时非常方便
                    language: "zh-CN",
                    ajax: {
                        url: ThinkPHP['MODULE'] + '/Question/getQuestionTags',
                        delay: 250,
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                    }
                });
            }
			
		},
		//新增常见问题
		add_question : function () {
			$('#question-add').length && $('#question-add').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Question/addQuestion',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#question-add-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['QUESTION'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#question-add-btn').removeClass('disabled');
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
					title : {
						required : true,
						rangelength : '[2,50]'
					},
					content : {
						required : true
					},
                    tags : {
						isSelect2 : true
					}
				},
				messages : {
					title : {
						required : '请输入常见问题标题',
						rangelength : $.validator.format('常见问题标题必须在{0}-{1}位之间')
					},
					content : {
						required : '请输入常见问题内容',
					},
					tags : {
						isSelect2 : '请选择常见问题类型'
					}
				}
			});
		},
		//编辑常见问题
		edit_question : function () {
			$('#question-edit').length && $('#question-edit').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Question/update',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#question-edit-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['QUESTION'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#question-edit-btn').removeClass('disabled');
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
					title : {
						required : true,
						rangelength : '[2,50]'
					},
					content : {
						required : true
					},
                    tags : {
						isSelect2 : true
					}
				},
				messages : {
					title : {
						required : '请输入常见问题标题',
						rangelength : $.validator.format('常见问题标题必须在{0}-{1}位之间')
					},
					content : {
						required : '请输入常见问题内容',
					},
                    tags : {
						isSelect2 : '请选择常见问题类型'
					}
				}
			});
		},
        //初始化文本编辑器
        kindeditor : function (textAreaName) {
            var editor = KindEditor.create(textAreaName,{
                uploadJson : ThinkPHP['KINDEDITOR']+'/php/upload_json.php',
                fileManagerJson : ThinkPHP['KINDEDITOR']+'/php/file_manager_json.php',
                resizeType:0,//设置不能拖动
                langType:'zh_CN',//设置编辑器的语言
                autoHeightMode : true,//自动高度
                afterCreate : function() {
                    this.loadPlugin('autoheight');
                },
                afterBlur:function () {this.sync();}//解决同步textarea数据的问题
            });
        },
        //相关操作
        action_do : function () {
            //3.修改真实姓名
            if($('#tags-dialog').length) {
                $('#tags-dialog').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: false
                });
            }
            $('#add-tags-btn').on('click', function () {
                $('#tags-dialog').modal('show');
            });
        },
        //添加新标签
        add_question_tags : function () {
            $('#tags-form').validate({
                submitHandler : function (form) {
                    $(form).ajaxSubmit({
                        url: ThinkPHP['MODULE'] + '/Question/addQuestionTags',
                        type: 'POST',
                        beforeSubmit: function(formData, jqForm, options){
                            $('#tags-form-btn').addClass('disabled');
                            $('#loading').addClass('show');
                        },
                        success: function(responseText, statusText){
                            if (responseText>0) {
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
                                $('#loading').find('p').html('标签添加成功~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-check-circle fa-2x success');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#tags-form-btn').removeClass('disabled');
                                    $('#tags-dialog').modal('hide');
                                    $('#tags-form').get(0).reset();//重置表单
                                },500);
                            }else{
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('标签添加失败~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#tags-form-btn').removeClass('disabled');
                                },1000);
                            }
                        },
                        error : function (responseText, statusText, errorThrown) {
                            $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                            $('#loading').find('p').html('出现错误~');
                            setTimeout(function () {
                                $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('正在处理中...');
                                $('#tags-form-btn').removeClass('disabled');
                            },1000);
                        }
                    });
                },
                showErrors : function(errorMap,errorList) {
                    var errors = this.numberOfInvalids();
                    this.defaultShowErrors();
                },
                highlight : function (element,errorClass) {
                    //错误样式
                    $(element).parents('.form-group').addClass('has-error');
                },
                unhighlight : function (element,errorClass) {
                    //取消错误样式
                    $(element).parents('.form-group').removeClass('has-error');
                },
                //自定义错误显示的位置
                errorPlacement : function (error, element) {
                    error.appendTo($(element).parents('.form-group').find('span.errorLabel'));
                },
                rules : {
                    name : {
                        required : true,
                        rangelength : [1,20],
                        remote : {
                            url : ThinkPHP['MODULE'] + '/Question/checkQuestionTag',
                            type : 'POST',
                            beforeSend : function () {},
                            complete : function (jqXHR) {}
                        }
                    }
                },
                messages : {
                    name : {
                        required : '请输入标签名称',
                        rangelength : $.validator.format('标签名称必须在{0}-{1}位之间'),
                        remote : '此标签已存在，请重新输入'
                    }
                }
            });
        },
		//页面初始化
		init:function(){
			question_action.elements_init();
			question_action.add_question();
			question_action.edit_question();
            question_action.action_do();
            question_action.add_question_tags();
		}
	};
	//运行
	question_action.init();
});