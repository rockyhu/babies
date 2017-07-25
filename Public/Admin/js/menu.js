$(function () {
	//自定义菜单管理
	/**
	 * 菜单添加的规则如下：
	 *
	 * 添加主菜单的时候，会有一个input表单字段标注该菜单是主菜单，主菜单可以设置菜单动作（子菜单亦是）
	 *
	 * 当在主菜单下添加了子菜单的时候，主菜单设置动作的按钮将隐藏，设置的动作对主菜单无效。
	 *
	 * 子菜单一直都可以设置菜单动作
	 *
	 * 主菜单可以添加1~3个，子菜单可以添加1~5个
	 *
	 * 菜单标题：子菜单不超过16个字节，子菜单不超过40个字节
	 * 
	 */
	var menu_action = {
		//数据初始化
		elements_init : function () {
			//菜单数据结构初始化
			//...
			//ThinkPHP.menus - 菜单数组
			//模态窗初始化
			$('#setMenuAction-modal').modal({
				backdrop: 'static',
				keyboard: false,
				show: false
			});
		},
		//添加主菜单
		addMenu : function () {
			$('#wechatMenu').on('click', '.addMenu', function () {
				var menu_html = '<tr class="hover ng-scope">'+
									'<td style="border-top:none;">'+
										'<div class="parentmenu">'+
											'<input type="hidden" name="parent" data-role="parent">'+
											'<input type="text" class="form-control ng-pristine ng-untouched ng-valid" data-role="parent" placeholder="菜单名称" style="display:inline-block;width:300px;">'+
											'<a href="javascript:void(0);" class="first-child" data-role="parent" title="拖动调整此菜单位置"><i class="fa fa-arrows"></i></a>'+
											'<a href="javascript:void(0);" class="setAction" data-role="parent" title="设置此菜单动作"><i class="fa fa-pencil"></i> 设置此菜单动作</a>'+
											'<a href="javascript:void(0);" class="deleteMenu" data-role="parent" title="删除此菜单"><i class="fa fa-remove"></i> 删除此菜单</a>'+
											'<a href="javascript:void(0);" class="addSubMenu" data-role="parent" title="添加子菜单"><i class="fa fa-plus"></i> 添加子菜单</a>'+
										'</div>'+
									'</td>'+
								'</tr>';
				if($('#wechatMenu tr.hover').length<3) {
					//更新菜单数据
					ThinkPHP.menus.push({
						name: '',
						type: '',
						url: '',
						key: '',
						media_id: '',
						sub_button: []
					});
					$('#wechatMenu tbody.designer').append(menu_html);
				}
			});
		},
		//添加子菜单
		addSubMenu : function () {
			$('#wechatMenu').on('click', '.addSubMenu', function () {
				var sonmenu = $(this).parents('td').find('.sonmenu');
				if(sonmenu.length) {
					var submenu_html =  '<div class="sonmenulist ng-scope">'+
											'<input type="hidden" name="sub" data-role="sub">'+
											'<input type="text" class="form-control ng-pristine ng-untouched ng-valid" data-role="sub" placeholder="子菜单名称" style="display:inline-block;width:220px;">'+
											'<a href="javascript:void(0);" class="first-child" data-role="sub" title="拖动调整此菜单位置"><i class="fa fa-arrows"></i></a>'+
											'<a href="javascript:void(0);" class="setAction" data-role="sub" title="设置此菜单动作"><i class="fa fa-pencil"></i> 设置此菜单动作</a>'+
											'<a href="javascript:void(0);" class="deleteMenu" data-role="sub" title="删除此菜单"><i class="fa fa-remove"></i> 删除此菜单</a>'+
										'</div>';
				}else{
					var submenu_html =  '<div class="designer sonmenu ui-sortable">'+
											'<div class="sonmenulist ng-scope">'+
												'<input type="hidden" name="sub" data-role="sub">'+
												'<input type="text" class="form-control ng-pristine ng-untouched ng-valid" data-role="sub" placeholder="子菜单名称" style="display:inline-block;width:220px;">'+
												'<a href="javascript:void(0);" class="first-child" data-role="sub" title="拖动调整此菜单位置"><i class="fa fa-arrows"></i></a>'+
												'<a href="javascript:void(0);" class="setAction" data-role="sub" title="设置此菜单动作"><i class="fa fa-pencil"></i> 设置此菜单动作</a>'+
												'<a href="javascript:void(0);" class="deleteMenu" data-role="sub" title="删除此菜单"><i class="fa fa-remove"></i> 删除此菜单</a>'+
											'</div>'+
										'</div>';
					sonmenu = $(this).parents('td');
				}
				if(sonmenu.find('.sonmenulist').length<5) {
					//更新菜单数据
					/**
					 * 1.先在菜单数据中找到当前操作的主菜单
					 * 2.然后在主菜单下的子菜单列表数组中添加子菜单
					 */
					//当前主菜单的索引编号
					var menu_index = $(this).parents('tr').index();
					ThinkPHP.menus[menu_index].sub_button.push({
						name: '',
						type: '',
						url: '',
						media_id: '',
						key: ''
					});
					sonmenu.append(submenu_html);
				}
				//存在子菜单的时候，隐藏当前主菜单的设置菜单动作按钮
				$(this).parent().find('.setAction').hide();
			});
		},
		//删除菜单
		deleteMenu : function () {
			$('#wechatMenu').on('click', '.deleteMenu', function () {
				var data_role = $(this).data('role');
				if(data_role === 'parent') {
					//主菜单
					/**
					 * 当主菜单下存在子菜单的时候，当主菜单删除的时候应该提示：将同时删除所有子菜单, 是否继续? 
					 * 如果不存在子菜单，则直接删除即可
					 */
					var this_tr = $(this).parents('tr.hover');
					if(this_tr.find('.sonmenulist').length) {
						if(confirm('将同时删除所有子菜单, 是否继续? ')) {
							ThinkPHP.menus.splice($(this).parents('tr').index(), 1);
							this_tr.remove();
						}
					}else{
						ThinkPHP.menus.splice($(this).parents('tr').index(), 1);
						this_tr.remove();
					}
				}else if(data_role === 'sub') {
					//子菜单
					if($(this).parent().find('input[type="text"]').val() != '') {
						if(confirm('将删除该菜单, 是否继续?')) {
							if(ThinkPHP.menus[$(this).parents('tr').index()].sub_button.length == 1) {
								$(this).parents('td').find('.setAction').show();
							}
							//更新菜单数据
							ThinkPHP.menus[$(this).parents('tr').index()].sub_button.splice($(this).parent('.sonmenulist').index(), 1);
							$(this).parents('.sonmenulist').remove();
						}
					}else{
						if(ThinkPHP.menus[$(this).parents('tr').index()].sub_button.length == 1) {
							$(this).parents('td').find('.setAction').show();
						}
						//更新菜单数据
						ThinkPHP.menus[$(this).parents('tr').index()].sub_button.splice($(this).parent('.sonmenulist').index(), 1);
						$(this).parents('.sonmenulist').remove();
					}
				}
			});
		},
		//更新菜单标题
		setMenuTitle : function () {
			//文本框失去焦点时
			$('#wechatMenu').on('focusout', 'input[type="text"]', function () {
				if($.trim($(this).val()) == '') return;
				var menu_index = $(this).parents('tr').index(),
					role = $(this).data('role');
				if(role === 'parent') {
					ThinkPHP.menus[menu_index].name = $.trim($(this).val());
				}else if(role === 'sub') {
					ThinkPHP.menus[menu_index].sub_button[$(this).parent('.sonmenulist').index()].name = $.trim($(this).val());
				}
			});
		},
		//设置菜单动作
		setMenuAction : function () {
			//弹窗
			$('#wechatMenu').on('click', '.setAction', function () {
				//1.获取当前点击菜单的名称
				var _title = $.trim($(this).parent().find('input[type="text"]').val());
				if(_title != '') {
					$('#setMenuAction-modal').find('.modal-title').text('选择菜单【'+ _title +'】要执行的操作');
				}
				//2.保存当前需要设置的菜单索引编号
				var menu_index = $(this).parents('tr').index(),
					role = $(this).data('role'),
					submenu_index = '';
				if(role === 'sub') {
					submenu_index = $(this).parent('.sonmenulist').index();
				}
				$('#meun_index').val(menu_index);
				$('#submenu_index').val(submenu_index);
				//3.初始化
				$('#setMenuAction-modal').find('input[name="ipt"]').get(0).checked = 'checked';
				if(role === 'sub') {
					if(ThinkPHP.menus[menu_index].sub_button[submenu_index].type != '') {
						$('#setMenuAction-modal').find('input[name="ipt"]').each(function () {
							if($(this).val() == ThinkPHP.menus[menu_index].sub_button[submenu_index].type) $(this).get(0).checked = 'checked';
						});
						//遍历类型
						switch(ThinkPHP.menus[menu_index].sub_button[submenu_index].type) {
							case 'view':
								$('#action-input-container').find('.action-input').eq(0).addClass('show').siblings().removeClass('show');
								$('#action-input-container').find('.action-input').eq(0).find('input').val(ThinkPHP.menus[menu_index].sub_button[submenu_index].url);
								break;
							case 'media_id':
							case 'view_limited':
								$('#action-input-container').find('.action-input').eq(2).addClass('show').siblings().removeClass('show');
								$('#action-input-container').find('.action-input').eq(2).find('input').val(ThinkPHP.menus[menu_index].sub_button[submenu_index].media_id);
								break;
							case 'click':
							case 'scancode_push':
							case 'scancode_waitmsg':
							case 'pic_sysphoto':
							case 'pic_photo_or_album':
							case 'pic_weixin':
							case 'location_select':
								$('#action-input-container').find('.action-input').eq(1).addClass('show').siblings().removeClass('show');
								$('#action-input-container').find('.action-input').eq(1).find('input').val(ThinkPHP.menus[menu_index].sub_button[submenu_index].key);
								break;
						}
					}
				}
				//4.显示模态窗
				$('#setMenuAction-modal').modal('show');
			});
			//保存菜单动作
			$('#setMenuAction-modal').on('click', '.btn-save-action', function () {
				var ipt_val = $('#setMenuAction-modal').find('input[name="ipt"]:checked').val(),
					text_val = $('#action-input-container .show').find('input[type="text"]').val();
				//菜单索引
				var menu_index = $('#meun_index').val(),
					submenu_index = $('#submenu_index').val();
				//菜单验证
				switch(ipt_val) {
					case 'view':
						if(text_val == 'http://') {
							alert('请指定点击此菜单时要跳转的链接');
							return;
						}
						//菜单动作赋值
						if(submenu_index !== '' && !isNaN(submenu_index)) {
							ThinkPHP.menus[menu_index].sub_button[submenu_index].url = text_val;
							ThinkPHP.menus[menu_index].sub_button[submenu_index].type = ipt_val;
						}else{
							ThinkPHP.menus[menu_index].url = text_val;
							ThinkPHP.menus[menu_index].type = ipt_val;
						}
						break;
					case 'media_id':
					case 'view_limited':
						if(text_val == '') {
							alert('请指定公众平台的素材id');
							return;
						}
						//菜单动作赋值
						if(submenu_index !== '' && !isNaN(submenu_index)) {
							ThinkPHP.menus[menu_index].sub_button[submenu_index].media_id = text_val;
							ThinkPHP.menus[menu_index].sub_button[submenu_index].type = ipt_val;
						}else {
							ThinkPHP.menus[menu_index].media_id = text_val;
							ThinkPHP.menus[menu_index].type = ipt_val;
						}
						break;
					case 'click':
					case 'scancode_push':
					case 'scancode_waitmsg':
					case 'pic_sysphoto':
					case 'pic_photo_or_album':
					case 'pic_weixin':
					case 'location_select':
						if(text_val == '') {
							alert('请指定点击此菜单时要执行操作的关键字');
							return;
						}
						//菜单动作赋值
						if(submenu_index !== '' && !isNaN(submenu_index)) {
							ThinkPHP.menus[menu_index].sub_button[submenu_index].key = text_val;
							ThinkPHP.menus[menu_index].sub_button[submenu_index].type = ipt_val;
						}else {
							ThinkPHP.menus[menu_index].key = text_val;
							ThinkPHP.menus[menu_index].type = ipt_val;
						}
						break;
				}
				//重置表单
				$('#setMenuAction-form').get(0).reset();
				$('#action-input-container .action-input').eq(0).addClass('show').siblings().removeClass('show');
				//关闭弹窗
				$('#setMenuAction-modal').modal('hide');
			});
		},
		//菜单类型动作切换
		setMenuAction_change : function () {
			$('#setMenuAction-modal').on('click', 'input[name="ipt"]', function () {
				var _val = $(this).val();
				if(_val == 'view') {
					$('#action-input-container').find('.action-input').eq(0).addClass('show').siblings().removeClass('show');
				}else if(_val == 'media_id' || _val == 'view_limited') {
					$('#action-input-container').find('.action-input').eq(2).addClass('show').siblings().removeClass('show');
				}else if(_val != 'view' && _val != 'media_id' && _val != 'view_limited') {
					$('#action-input-container').find('.action-input').eq(1).addClass('show').siblings().removeClass('show');
				}
			});
		},
		//保存菜单结构
		saveMenu : function () {
			$('#saveMenu').on('click', function () {
				//这里必须，判断一下是否有没有输入的值
				//...
				$('#loading').addClass('show');
				//ajax提交数据到服务器进行处理，更新微信菜单
				var _this = this;
				$.ajax({
                    url: ThinkPHP['MODULE'] + '/Menu/saveMenu',
                    data: {
                        menu: JSON.stringify(ThinkPHP.menus)
                    },
                    type: 'POST',
                    beforeSend: function(jqXHR, settings) {
                    	$('#loading').find('p').html('更新中...');
                        $(_this).addClass('disabled');
                    },
                    success: function(response, textStatus, jqXHR) {
                    	if(response>0) {
	                        $('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
	                        $('#loading').find('p').html('菜单更新成功');
	                        setTimeout(function () {
	                        	$(_this).removeClass('disabled');
	                        	$('#loading').removeClass('show');
	                        	location.reload();
	                        }, 500);
                    	}else{
                    		$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
							$('#loading').find('p').html('请检查设置');
							setTimeout(function () {
								$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('正在处理中...');
								$(_this).removeClass('disabled');
							},1000);
                    	}
                    },
                    error : function (responseText, statusText, errorThrown) {
						$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
						$('#loading').find('p').html('连接失败~');
						setTimeout(function () {
							$('#loading').removeClass('show').find('.ui-loading-bright').removeClass('no-image fa fa-times-circle fa-2x error');
							$('#loading').find('p').html('正在处理中...');
							$(_this).removeClass('disabled');
						},1000);
					}
                });
			});
		},
		//页面初始化
		init:function(){
			menu_action.elements_init();
			menu_action.addMenu();
			menu_action.addSubMenu();
			menu_action.deleteMenu();
			menu_action.setMenuTitle();
			menu_action.setMenuAction();
			menu_action.setMenuAction_change();
			menu_action.saveMenu();
		}
	};
	//运行
	menu_action.init();
});