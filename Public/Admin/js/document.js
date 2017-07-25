//4.表格列表初始化
baseTable = {
    dataTable : {},
    init : function (jQueryElements, url) {
        if(jQueryElements.length && url.length){
            var lastIdx = null;
            baseTable.dataTable = jQueryElements.DataTable({
                "aLengthMenu" : [20, 40, 50, 60, 100, 500, 1000], //更改显示记录数选项
                "iDisplayLength" : 40, //默认显示的记录数
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": false,
                "info": true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: url,
                    pages: 5, // number of pages to cache
                    "data": function (d) {
                        //添加额外的参数传给服务器
                        d.status = $('select[name="status"]').val();
                        d.shuxing = $('select[name="shuxing"]').val();
                        d.pnid = $('select[name="pnid"]').val();
                        d.nid = $('select[name="nid"]').val();
                        d.marketprice = $('select[name="marketprice"]').val();
                    }
                },
                "bStateSave": true,//是否开启cookies保存当前状态信息
                "autoWidth":false,//关掉适配
                "dom":"<'row'<'col-sm-2'l><'searchbox-all col-sm-7'><'col-sm-3'f>r>"+
                "t"+
                "<'row'<'col-sm-6'i><'col-sm-6'p>>",
                'language': {//语言设置
                    'emptyTable': '没有数据',
                    'loadingRecords': '玩命加载中...',
                    'processing': '查询中...',
                    'search': '检索:',
                    'lengthMenu': '每页 _MENU_ 条',
                    'zeroRecords': '没有数据',
                    'paginate': {
                        'first':'第一页',
                        'last':'最后一页',
                        'next': '下一页',
                        'previous':'上一页'
                    },
                    'info': '第 _PAGE_ 页 / 共 _PAGES_ 页 , 共 _TOTAL_ 条数据',
                    'infoEmpty': '没有数据',
                    'infoFiltered': '(过滤总条数 _MAX_ 条)'
                }
            });

            //添加状态
            var status_html = '<select name="status" class="form-control input-sm">'+
                '<option value="-1" selected>按状态筛选</option>'+
                '<option value="1">已发布</option>'+
                '<option value="0">待发布</option>'+
                '</select> ';
            $('.searchbox-all').append(status_html);

            $('select[name="status"]').on('change', function () {
                baseTable.dataTable.ajax.reload();
            });

            //添加商品类型
            var shuxing_html = '<select name="shuxing" class="form-control input-sm" style="width:84px;">'+
                '<option value="" selected>按类型筛选</option>'+
                '<option value="isrecommand">推荐</option>'+
                '<option value="isnew">新品</option>'+
                '<option value="ishot">热卖</option>'+
                '<option value="istop">置顶</option>'+
                '</select> ';
            $('.searchbox-all').append(shuxing_html);

            $('select[name="shuxing"]').on('change', function () {
                baseTable.dataTable.ajax.reload();
            });

            //添加一级分类筛选
            var pnid_html = '<select name="nnid" class="form-control input-sm" style="width:120px;">';
            var pnidArr = ThinkPHP['documentnavlist'];
            pnid_html += '<option value="-1" selected>按一级分类筛选</option>';
            for(var i=0;i<pnidArr.length;i++) {
                //console.log(pnidArr[i]['child']);
                pnid_html += '<option value="'+pnidArr[i].id+'" data-child='+ JSON.stringify(pnidArr[i]['child']) +'>'+pnidArr[i].text+'</option>';
            }
            pnid_html += '</select> ';
            pnid_html += '<select name="nid" class="form-control input-sm" style="width:120px;">'+
                '<option value="-1" selected>按二级分类筛选</option>'+
                '</select>';
            $('.searchbox-all').append(pnid_html);

            $('select[name="nnid"]').on('change', function () {
                //调整二级分类栏目的选项
                var $child = $(this).find('option:selected').data('child');
                var $child_option = '<option value="-1" selected>按二级分类筛选</option>';
                for(var i=0;i<$child.length;i++) {
                    $child_option += '<option value="'+ $child[i].id +'">'+$child[i].text+'</option>';
                }
                if($child_option != '') $('select[name="nid"]').html($child_option);
                baseTable.dataTable.ajax.reload();
            });
            $('select[name="nid"]').on('change', function () {
                baseTable.dataTable.ajax.reload();
            });

            //排序
            var usertype_html = '<select name="order" class="form-control input-sm">'+
                '<option value="默认排序">默认排序</option>'+
                '<option value="create">按时间倒序排序</option>'+
                '<option value="readcount">按阅读数倒序排序</option>'+
                '</select>';
            $('.searchbox-all').append(usertype_html);

            $('select[name="marketprice"]').on('change', function () {
                baseTable.dataTable.ajax.reload();
            });

            jQueryElements.find('tbody').on('mouseover', 'td', function () {
                if(typeof baseTable.dataTable.cell(this).index() != 'undefined') {
                    var colIdx = baseTable.dataTable.cell(this).index().column;
                    if (colIdx !== lastIdx) {
                        $(baseTable.dataTable.cells().nodes()).removeClass('highlight');
                        $(baseTable.dataTable.column(colIdx).nodes()).addClass('highlight');
                    }
                }
            }).on('mouseleave', function () {
                $(baseTable.dataTable.cells().nodes()).removeClass('highlight');
            });

            return baseTable.dataTable;
        }
    }
};
$(function () {
	//商品列表
	var document_action = {
		//元素初始化
		elements_init : function () {
			//DataTable init - list
			if($('#table-document').length) var dataTable = baseTable.init($('#table-document'), ThinkPHP['ajaxDocumentList']);
			
			//初始化图片上传
			if($('#document-add').length || $('#document-edit').length){
                //缩略图上传
                Plupload.create({
                    buttonid : 'plupload-thumb-btn',
                    multi : false
                });
                //批量图片上传
                Plupload.create({
                    buttonid : 'plupload-select-files',
                    multi : true,
                    total : 6
                });
			}
			
			//Initialize Select2 Elements - add
			if($(".select2").length){
				//验证select2下拉菜单必须选择
				$.validator.addMethod("isSelect2", function(value, element) {
					return (value == '' || value == null) ? false : true;
				}, "请选择");
				$('.select2-hidden-accessible').on('change', function (e) {
					if($(this).val() != '') {
						$(this).parent().removeClass('has-error');
						$(this).nextAll('span.errorLabel').hide();
						$(this).parent().find('.select2-selection').css('borderColor', '#d2d6de');
					}
				});
			}
			
			//删除操作
			$('#table-document').on('click', '.del-btn', function () {
				var id = $(this).data('id');
				$.confirm({
				    title: '确认框',
				    content: '你确认要删除这条数据吗？',
				    confirmButton : '确认',
				    confirmButtonClass : 'btn-success',
				    cancelButton : '取消',
				    confirm: function(){
				        $.ajax({
							url : ThinkPHP['MODULE'] + '/Document/remove',
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
			
			if($('.textarea').length) document_action.kindeditor($('.textarea'));

            //选中一级分类,筛选出二级分类
            if($('#document-add').length || $('#document-edit').length) {
                $('select[name="nnid"]').on('change', function () {
                    var _selected_child = $(this).find('option:selected').data('child');
                    var _options = '<option value="-1">请选择二级分类</option>';
                    for(var i=0,len = _selected_child.length;i<len;i++) {
                        _options += '<option value="'+ _selected_child[i]['id'] +'">'+ _selected_child[i]['text'] +'</option>';
                    }
                    $('select[name="nid"]').html(_options);
                });
            }
			
		},
        //商品上架、下架
        document_status : function () {
            $('#table-document').on('click', '.product-status', function () {
                var _status = $(this).data('status'),
                    _id = $(this).data('id');
                $.ajax({
                    url: ThinkPHP['MODULE'] + '/Document/setDocumentStatus',
                    type: "POST",
                    data: {
                        id : _id,
                        status: _status,
                    },
                    success: function(response, textStatus, jqXHR) {
                        baseTable.dataTable.ajax.reload();
                    }
                });
            });
        },
		//商品属性
        document_property : function () {
            $('#table-document').on('click', '.property', function () {
                var _property = $(this).data('property'),
                    _id = $(this).data('id'),
                    _value = $(this).data('value');
                $('#loading').addClass('show');
                $.ajax({
                    url: ThinkPHP['MODULE'] + '/Document/setDocumentProperty',
                    type: "POST",
                    data: {
                        id : _id,
                        property: _property,
                        value : _value
                    },
                    success: function(response, textStatus, jqXHR) {
                        if(response>0) {
                            $('#loading').removeClass('show');
                            baseTable.dataTable.ajax.reload();
                        }
                    }
                });
            });
        },
		//新增商品
		add_document : function () {
            $('#document-add').length && $('#document-add').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Document/addDocument',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#document-add-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['DOCUMENT'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#document-add-btn').removeClass('disabled');
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
                    genlisid : {
                        isSelect2 : true
                    },
                    sort : {
                        number : true
                    },
                    name : {
                        required : true,
                        rangelength : '[1,50]'
                    },
					pnid : {
						isSelect2 : true
					},
                    marketprice : {
						number : true
					},
                    costprice : {
						number : true
					},
                    productprice : {
						number : true
					},
                    total : {
                        number : true
					},
                    maxbuy : {
                        number : true
					},
                    sales : {
                        number : true,
					}
				},
				messages : {
                    genlisid : {
                        isSelect2 : '请选择产品让利等级'
                    },
                    sort : {
                        number : '排序必须数字'
                    },
					pnid : {
						isSelect2 : '请输入商品分类'
					},
					name : {
						required : '请输入商品名称',
						rangelength : $.validator.format('商品名称必须在{0}-{1}位之间')
					},
                    marketprice : {
						number : '现价必须数字'
					},
                    productprice : {
						number : '原价必须数字'
					},
                    costprice : {
						number : '成本价必须是数字'
					},
                    total : {
                        number : '库存必须数字'
					},
                    maxbuy : {
                        number : '单次最多购买量必须是数字'
					},
                    sales : {
                        number : '已出售数必须是数字'
					}
				}
			});
		},
		//编辑商品
		edit_document : function () {
			//初始化
			if($('#document-edit').length){
				var thumb = $('#plupload-thumb-btn').next('.imglist').data('thumb'),
                    images = $('#plupload-select-files').next('.imglist').data('images');
				//缩略图
				if(thumb){
				    if(thumb.source.indexOf('http://') > -1 || thumb.source.indexOf('https://') > -1) {
                        var source = thumb.source;
                    }else {
                        var source = ThinkPHP['ROOT'] + thumb.source.substr(1);
                    }
                    $('#plupload-thumb-btn').next('.imglist').html('<div class="img_upload"><input type="hidden" name="thumb" value='+ JSON.stringify(thumb) +'><img class="img" src="'+ source +'"><span class="img-opacity"></span><i class="ion-android-close remove-btn"></i></div>');
				}
                //主图列表
                if(images){
                    var len = images.length,
                        html = '';
                    for(var i=0;i<len;i++){
                        if(thumb.source.indexOf('http://') > -1 || thumb.source.indexOf('https://') > -1) {
                            var _image = images[i].source;
                        }else {
                            var _image = ThinkPHP['ROOT'] + images[i].source.substr(1);
                        }
                        html += '<div class="img_upload"><input type="hidden" name="images[]" value=' + JSON.stringify(images[i]) + '><img class="img" src="'+ _image +'"><span class="img-opacity"></span><i class="ion-android-close remove-btn"></i></div>';
                    }
                    $('#plupload-select-files').next('.imglist').html(html);
                    Plupload.imgRemove('plupload-select-files');
                    Plupload.imgSort('plupload-select-files');
                }
			}
			$('#document-edit').length && $('#document-edit').validate({
				submitHandler : function (form) {
					$(form).ajaxSubmit({
						url: ThinkPHP['MODULE'] + '/Document/update',
						type: 'POST',
						beforeSubmit: function(formData, jqForm, options){
							$('#document-edit-btn').addClass('disabled');
							$('#loading').addClass('show');
						},
						success: function(responseText, statusText){
							if (responseText>0) {
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-check-circle fa-2x success');
								$('#loading').find('p').html('保存成功');
								setTimeout(function () {
									$('#loading').removeClass('show');
									location.href = ThinkPHP['DOCUMENT'];
								},1000);
							}else{
								$('#loading').find('.ui-loading-bright').addClass('no-image fa fa-times-circle fa-2x error');
								$('#loading').find('p').html('保存失败');
								setTimeout(function () {
									$('#loading').removeClass('show').removeClass('no-image fa fa-times-circle fa-2x error');
									$('#loading').find('p').html('正在处理中...');
									$('#document-edit-btn').removeClass('disabled');
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
                    genlisid : {
                        isSelect2 : true
                    },
                    sort : {
                        number : true
                    },
                    name : {
                        required : true,
                        rangelength : '[1,50]'
                    },
                    pnid : {
                        isSelect2 : true
                    },
                    marketprice : {
                        number : true
                    },
                    costprice : {
                        number : true
                    },
                    productprice : {
                        number : true
                    },
                    total : {
                        number : true
                    },
                    maxbuy : {
                        number : true
                    },
                    sales : {
                        number : true,
                    }
                },
                messages : {
                    genlisid : {
                        isSelect2 : '请选择产品让利等级'
                    },
                    sort : {
                        number : '排序必须数字'
                    },
                    pnid : {
                        isSelect2 : '请输入商品分类'
                    },
                    name : {
                        required : '请输入商品名称',
                        rangelength : $.validator.format('商品名称必须在{0}-{1}位之间')
                    },
                    marketprice : {
                        number : '现价必须数字'
                    },
                    productprice : {
                        number : '原价必须数字'
                    },
                    costprice : {
                        number : '成本价必须是数字'
                    },
                    total : {
                        number : '库存必须数字'
                    },
                    maxbuy : {
                        number : '单次最多购买量必须是数字'
                    },
                    sales : {
                        number : '已出售数必须是数字'
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
		//页面初始化
		init:function(){
			document_action.elements_init();
            document_action.document_status();
            document_action.document_property();
			document_action.add_document();
			document_action.edit_document();
		}
	};
	//运行
	document_action.init();
});