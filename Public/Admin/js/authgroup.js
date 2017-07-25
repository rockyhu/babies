$(function () {
	//权限管理
	var authgroup_action = {
		//元素初始化
		elements_init : function () {
			//DataTable init - list
			if($('#table-authgroup').length) var dataTable = baseTable.init($('#table-authgroup'), ThinkPHP['AuthGrouplist']);
			
			//删除操作
			$('#table-authgroup').on('click', '.del-btn', function () {
				var id = $(this).data('id');
				$.confirm({
				    title: '确认框',
				    content: '你确认要删除这条数据吗？',
				    confirmButton : '确认',
				    confirmButtonClass : 'btn-success',
				    cancelButton : '取消',
				    confirm: function(){
				        $.ajax({
							url : ThinkPHP['MODULE'] + '/AuthGroup/remove',
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
		},
        //添加菜单操作
        add_navdo : function () {
            //弹窗初始化
            if($('#navdo-add-dialog').length) {
                $('#navdo-add-dialog').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: false
                });
            }
            //添加按钮点击事件
            var _navid,_navtext,_firstnavid,_thisparent;//全局变量
            $('#authgroup-add,#authgroup-edit').on('click', '.navdo-add-btn', function () {
                _navid = $(this).data('navid');
                _navtext = $(this).data('navtext');
                _firstnavid = $(this).data('firstnavid');
                _thisparent = $(this).parent().parent();//当前的父元素的父元素即tr
                $('#navdo-add-form input[name="navid"]').val(_navid);
                $('#navdo-add-form input[name="navtext"]').val(_navtext);
                //显示
                $('#navdo-add-dialog').modal('show');
            });
            //表单提交
            $('#navdo-add-form').length && $('#navdo-add-form').validate({
                submitHandler : function (form) {
                    $(form).ajaxSubmit({
                        url: ThinkPHP['MODULE'] + '/AuthGroup/addNavDo',
                        type: 'POST',
                        beforeSubmit: function(formData, jqForm, options){
                            $('#navdo-add-form-btn').addClass('disabled');
                            $('#loading').addClass('show');
                        },
                        success: function(responseText, statusText){
                            if (responseText>0) {
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
                                $('#loading').find('p').html('菜单操作添加成功~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-check-circle fa-2x success');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#navdo-add-form-btn').removeClass('disabled');
                                    //动态修改rowspan属性
                                    $('#table-rules').find('.first'+_firstnavid).find('td').eq(0).attr('rowspan', parseInt($('#table-rules').find('.first'+_firstnavid).find('td').eq(0).attr('rowspan')) +1);
                                    $('#table-rules').find('.second'+_navid).find('td').eq(0).attr('rowspan', parseInt($('#table-rules').find('.second'+_navid).find('td').eq(0).attr('rowspan')) +1);
                                    var _text = $('#navdo-add-form input[name="text"]').val();
                                    var _url = $('#navdo-add-form input[name="url"]').val();
                                    //动态插入当前一行
                                    _thisparent.before(
                                        '<tr class="second'+ _navid +'-child">'+
                                            '<td style="text-align: left;">'+_text+'</td>'+
                                            '<td style="text-align: left;">'+_url+'</td>'+
                                            '<td>'+
                                                '<a href="javascript:void(0);" title="编辑" data-id="'+ responseText +'" data-text="'+ _text +'" data-url="'+ _url +'" data-navtext="'+ _navtext +'" class="btn btn-primary btn-xs navdo-edit-btn"><i class="ion-ios-compose-outline"></i></a>'+
                                                ' <a href="javascript:void(0);" title="删除" data-id="'+ responseText +'" data-navid="'+ _navid +'" data-firstnavid="'+ _firstnavid +'" data-text="'+ _text +'" data-navtext="'+ _navtext +'" class="btn btn-primary btn-xs navdo-del-btn"><i class="ion-ios-close-outline"></i></a>'+
                                            '</td>'+
                                            '<td align="center" class="checkboxtd"><input type="checkbox" name="rules['+ _navid +'][]" value="' + _text + '||' + _url +'" /></td>'+
                                        '</tr>'
                                    );
                                    $('#navdo-add-dialog').modal('hide');
                                    $('#navdo-add-form').get(0).reset();//重置表单
                                },500);
                            }else{
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('菜单操作添加失败~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#navdo-add-form-btn').removeClass('disabled');
                                },1000);
                            }
                        },
                        error : function (responseText, statusText, errorThrown) {
                            $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                            $('#loading').find('p').html('出现错误~');
                            setTimeout(function () {
                                $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('正在处理中...');
                                $('#navdo-add-form-btn').removeClass('disabled');
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
                    text : {
                        required : true,
                        rangelength : [2,20]
                    },
                    url : {
                        required : true
                    }
                },
                messages : {
                    text : {
                        required : '请输入菜单操作名称',
                        rangelength : $.validator.format('菜单操作名称必须在{0}-{1}位之间')
                    },
                    url : {
                        required : '请输入菜单操作链接'
                    }
                }
            });
        },
        //编辑菜单操作
        edit_navdo : function () {
            //弹窗初始化
            if($('#navdo-edit-dialog').length) {
                $('#navdo-edit-dialog').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: false
                });
            }
            //添加按钮点击事件
            var _id,_navtext,_text,_url,_thisparent,_this;//全局变量
            $('#authgroup-add,#authgroup-edit').on('click', '.navdo-edit-btn', function () {
                _this = this;
                _id = $(this).data('id');
                _navtext = $(this).data('navtext');
                _text = $(this).attr('data-text');
                _url = $(this).attr('data-url');
                _thisparent = $(this).parent().parent();//当前的父元素的父元素即tr
                $('#navdo-edit-form input[name="id"]').val(_id);
                $('#navdo-edit-form input[name="navtext"]').val(_navtext);
                $('#navdo-edit-form input[name="text"]').val(_text);
                $('#navdo-edit-form input[name="url"]').val(_url);
                //显示
                $('#navdo-edit-dialog').modal('show');
            });
            //表单提交
            $('#navdo-edit-form').length && $('#navdo-edit-form').validate({
                submitHandler : function (form) {
                    $(form).ajaxSubmit({
                        url: ThinkPHP['MODULE'] + '/AuthGroup/editNavDo',
                        type: 'POST',
                        beforeSubmit: function(formData, jqForm, options){
                            $('#navdo-edit-form-btn').addClass('disabled');
                            $('#loading').addClass('show');
                        },
                        success: function(responseText, statusText){
                            if (responseText>0) {
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
                                $('#loading').find('p').html('菜单操作编辑成功~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-check-circle fa-2x success');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#navdo-edit-form-btn').removeClass('disabled');
                                    //动态设置修改的属性
                                    var _newtext = $('#navdo-edit-form input[name="text"]').val(),
                                        _newurl = $('#navdo-edit-form input[name="url"]').val();
                                    $(_this).attr('data-text', _newtext);
                                    $(_this).attr('data-url', _newurl);
                                    $(_this).parent().next().find('input[type="checkbox"]').val(_newtext +'||'+ _newurl);
                                    _thisparent.find('td').eq(0).text(_newtext);
                                    _thisparent.find('td').eq(1).text(_newurl);
                                    $('#navdo-edit-dialog').modal('hide');
                                    $('#navdo-edit-form').get(0).reset();//重置表单
                                },500);
                            }else{
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('菜单操作编辑失败~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
                                    $('#loading').find('p').html('正在处理中...');
                                    $('#navdo-edit-form-btn').removeClass('disabled');
                                },1000);
                            }
                        },
                        error : function (responseText, statusText, errorThrown) {
                            $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
                            $('#loading').find('p').html('出现错误~');
                            setTimeout(function () {
                                $('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
                                $('#loading').find('p').html('正在处理中...');
                                $('#navdo-add-form-btn').removeClass('disabled');
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
                    text : {
                        required : true,
                        rangelength : [2,20]
                    },
                    url : {
                        required : true
                    }
                },
                messages : {
                    text : {
                        required : '请输入菜单操作名称',
                        rangelength : $.validator.format('菜单操作名称必须在{0}-{1}位之间')
                    },
                    url : {
                        required : '请输入菜单操作链接'
                    }
                }
            });
        },
        //删除菜单操作
        del_navdo : function () {
            //删除事件
            $('#authgroup-add,#authgroup-edit').on('click', '.navdo-del-btn', function () {
                var _id = $(this).data('id'),
                    _text = $(this).data('text'),
                    _navtext = $(this).data('navtext'),
                    _thisparent = $(this).parent().parent(),
                    _navid = $(this).data('navid'),
                    _firstnavid = $(this).data('firstnavid');
                $.confirm({
                    title: '确认框',
                    content: '你确认要删除“'+ _navtext +'”菜单下“'+ _text +'”的操作吗？',
                    confirmButton : '确认',
                    confirmButtonClass : 'btn-success',
                    cancelButton : '取消',
                    confirm: function(){
                        $.ajax({
                            url : ThinkPHP['MODULE'] + '/AuthGroup/removeNavDo',
                            data : {id:_id},
                            type: 'POST',
                            beforeSend: function(jqXHR, settings){
                                $('#loading').addClass('show');
                            },
                            success: function(response, textStatus, jqXHR){
                                $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
                                $('#loading').find('p').html('操作删除成功~');
                                setTimeout(function () {
                                    $('#loading').removeClass('show').removeClass('no-image fa fa-check-circle fa-2x success');
                                    $('#loading').find('p').html('正在处理中...');
                                    //动态修改rowspan属性
                                    $('#table-rules').find('.first'+_firstnavid).find('td').eq(0).attr('rowspan', parseInt($('#table-rules').find('.first'+_firstnavid).find('td').eq(0).attr('rowspan')) -1);
                                    $('#table-rules').find('.second'+_navid).find('td').eq(0).attr('rowspan', parseInt($('#table-rules').find('.second'+_navid).find('td').eq(0).attr('rowspan')) -1);
                                    //删除当前菜单操作所在的tr
                                    _thisparent.remove();
                                }, 500);
                            }
                        });
                    },
                    cancel: function(){}
                });
            });
        },
        //全选
        check_all : function () {
            //编辑时，初始化操作
            if($('#authgroup-edit').length) {
                //判断是否已经全部选中，全部选择，则设置$('#checkall')为选择的状态
                var $flag = true;
                $('#table-rules').find('td.checkboxtd input[type="checkbox"]').each(function () {
                    if(!$(this).get(0).checked) $flag = false;
                });
                if($flag) $('#checkall').prop('checked', true);
            }
            //全选操作
            $('#checkall').on('click', function () {
                if($(this).get(0).checked) {//全选
                    $('td.checkboxtd input[type="checkbox"]').each(function () {
                        $(this).prop('checked', true);
                    });
                }else {//取消全选
                    $('td.checkboxtd input[type="checkbox"]').each(function () {
                        $(this).prop('checked', false);
                    });
                }
            });
            //单选操作
            $('#table-rules').on('click', 'td.checkboxtd input[type="checkbox"]', function () {
                var _childClass = $(this).parents('tr').attr('class'),
                    _index = _childClass.indexOf('-');
                if($(this).get(0).checked) {
                    //判断是否已经全部选中，全部选择，则设置$('#checkall')为选择的状态
                    var $flag = true;
                    $('#table-rules').find('td.checkboxtd input[type="checkbox"]').each(function () {
                        if(!$(this).get(0).checked) $flag = false;
                    });
                    if($flag) $('#checkall').prop('checked', true);

                    //判断点击的是否是二级菜单下的操作
                    /**
                     * 思路逻辑：
                     * 点击操作如添加、修改、删除必须要开通查看的功能才行，页面结构决定
                     * 所以，当点击菜单操作时，必须自动选中查看功能
                     */
                    if(_index > -1) {///菜单操作点击
                        //console.log(_childClass.substr(0, _index));//second2
                        var _class = _childClass.substr(0, _index);
                        if(!$('.'+_class).find('input[type="checkbox"]').get(0).checked) {
                            $('.'+_class).find('input[type="checkbox"]').prop('checked', true);
                        }
                    }
                }else {
                    $('#checkall').prop('checked', false);
                    //当二级菜单栏目取消选中时，取消所有的菜单操作选中
                    if(_index == -1) {
                        $('.'+_childClass+'-child').find('input[type="checkbox"]:checked').each(function () {
                            $(this).prop('checked', false);
                        });
                    }
                }
            });
        },
		//新增栏目
		add_authgroup : function () {
			$('#authgroup-add').length && $('#authgroup-add').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/AuthGroup/addRole',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#authgroup-add-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['AuthGroup'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#authgroup-add-btn').removeClass('disabled');
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
						rangelength : '[2,20]'
					}
				},
				messages : {
					title : {
						required : '请输入角色名称',
						rangelength : $.validator.format('角色名称必须在{0}-{1}位之间')
					}
				}
			});
		},
		//编辑栏目
		edit_authgroup : function () {
			$('#authgroup-edit').length && $('#authgroup-edit').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/AuthGroup/editRole',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#authgroup-edit-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['AuthGroup'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#authgroup-edit-btn').removeClass('disabled');
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
						rangelength : '[2,20]'
					}
				},
				messages : {
					title : {
						required : '请输入角色名称',
						rangelength : $.validator.format('角色名称必须在{0}-{1}位之间')
					}
				}
			});
		},
		//页面初始化
		init:function(){
			authgroup_action.elements_init();
            authgroup_action.add_navdo();
            authgroup_action.edit_navdo();
            authgroup_action.del_navdo();
            authgroup_action.check_all();
			authgroup_action.add_authgroup();
			authgroup_action.edit_authgroup();
		}
	};
	//运行
	authgroup_action.init();
});