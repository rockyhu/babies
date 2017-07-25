//1.KindEditor编辑器封装
kEditor = {
    create : function (selector) {//selector表示选择器，eg:.keditor OR #keditor等
        if(!$(selector).length) return;
        var editor;
        $.ajax({
            url: ThinkPHP['MODULE']+'/Kindeditor/getToken',
            async: false,
            type : 'POST',
            data : {type : 3},
            success: function(data){
                token1 = data.token1;
                token2 = data.token2;
                editor = KindEditor.create(selector, {
                    uploadJson : 'http://upload.qiniu.com/',
                    filePostName : 'file',
                    allowFileManager : true,
                    fileManagerJson : ThinkPHP['MODULE']+'/Kindeditor/fileManage',
                    extraFileUploadParams : {token : token2},
                    token : token1,
                    resizeType : 1,//只允许改变高度
                    autoHeightMode : true,//自动高度
                    afterCreate : function() {
                        this.loadPlugin('autoheight');
                        this.sync();
                    },
                    afterBlur: function(){this.sync();}
                });
            }
        });
        return editor;
    }
};

//2.plupload图片上传插件封装，options配置参数对象，包括buttonid,multi,total属性
Plupload = {
    //plupload插件配置
    create : function (options) {
        var options = options || {},
            _buttonid = options.buttonid,//按钮id
            upload_total = options.total || 1,//最多上传数量,默认上传一张
            _multi = options.multi,//是否允许多选
            _thisplupload = this;
        var uploader = new plupload.Uploader({
            runtimes : 'html5,flash,silverlight,html4',
            browse_button : _buttonid,
            url : ThinkPHP['IMAGEURL'],
            flash_swf_url : ThinkPHP['PLUPLOAD']+'/js/Moxie.swf',
            silverlight_xap_url : ThinkPHP['PLUPLOAD']+'/js/Moxie.xap',
            filters : {
                max_file_size : '5mb',
                mime_types: [
                    {title : "图片文件", extensions : "jpg,gif,png"}
                ],
                prevent_duplicates : true //不允许选取重复文件
            },
            multi_selection: _multi, //true:ctrl多文件上传, false 单文件上传
            init: {
                PostInit : function () {
                    _thisplupload.imgRemove(_buttonid);
                    _multi && _thisplupload.imgSort(_buttonid);
                },
                //文件上传前
                FilesAdded: function(up, files) {
                    var length_has_upload = $("#"+_buttonid).next().children(".img_upload").length;
                    if (files.length >= upload_total) {//超过上传总数量则隐藏
                        $('#'+_buttonid).addClass('disabled');
                    }
                    var imghtml = '';
                    plupload.each(files, function(file) {//遍历文件
                        if (length_has_upload < upload_total) {
                            imghtml += '<div class="img_upload" id="' + file['id'] + '"><div class="progress active"><div class="progress-bar progress-bar-yellow" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"><span class="sr-only">0% Complete</span></div></div></div>';
                        }
                        length_has_upload++;
                    });
                    if(imghtml != '') {
                        $("#"+_buttonid).next().prepend(imghtml);
                        uploader.start();
                    }
                },
                //上传中，显示进度条
                UploadProgress: function(up, file) {
                    var percent = file.percent;
                    $("#" + file.id).find('.progress-bar').css({"width": percent + "%"});
                    $("#" + file.id).find(".sr-only").text(percent + "% Complete");
                },
                //文件上传成功的时候触发
                FileUploaded: function(up, file, info) {
                    var uploaded_length = $("#"+_buttonid).find('.img_upload').length;
                    if (uploaded_length <= upload_total) {
                        var imageUrl = $.parseJSON(info.response);
                        if(!_multi) {
                            $("#" + file.id).html('<input type="hidden" name="thumb" value=' + info.response + '><img class="img" src=' + ThinkPHP["ROOT"] + imageUrl["source"].substr(1) + '><span class="img-opacity"></span><i class="ion-android-close remove-btn" data-fileid='+ file.id +'></i>');
                        }else {
                            $("#" + file.id).html('<input type="hidden" name="images[]" value=' + info.response + '><img class="img" src=' + ThinkPHP["ROOT"] + imageUrl["source"].substr(1) + '><span class="img-opacity"></span><i class="ion-android-close remove-btn" data-fileid='+ file.id +'></i>');
                        }
                    }
                    $('#'+_buttonid).removeClass('disabled');
                },
                //上传出错的时候触发
                Error: function(up, err) {
                    console.log(err.message);
                }
            }
        });
        uploader.init();
    },
    //图片删除
    imgRemove : function (_buttonid) {
        //删除图片
        $("#"+_buttonid).next().on('click', '.remove-btn', function () {
            $(this).parent().fadeOut(function () {
                $(this).remove();
            });
        });
    },
    //图片排序
    imgSort : function (_buttonid) {
        $("#"+_buttonid).next().sortable({
            connectWith : '.img_upload',
            placeholder : "sort-highlight",//设定占位符的样式容器，不需要加.
            handle : ".img-opacity",//设定拖动句柄
            forcePlaceholderSize : true,
            zIndex : 9999
        });
        $("#"+_buttonid).next().disableSelection();
    }
};

/**3. * 对Date的扩展，将 Date 转化为指定格式的String * 月(M)、日(d)、12小时(h)、24小时(H)、分(m)、秒(s)、周(E)、季度(q)
 可以用 1-2 个占位符 * 年(y)可以用 1-4 个占位符，毫秒(S)只能用 1 个占位符(是 1-3 位的数字) * eg:
 * (new Date()).pattern("yyyy-MM-dd hh:mm:ss.S")==> 2006-07-02 08:09:04.423
 * (new Date()).pattern("yyyy-MM-dd E HH:mm:ss") ==> 2009-03-10 二 20:09:04
 * (new Date()).pattern("yyyy-MM-dd EE hh:mm:ss") ==> 2009-03-10 周二 08:09:04
 * (new Date()).pattern("yyyy-MM-dd EEE hh:mm:ss") ==> 2009-03-10 星期二 08:09:04
 * (new Date()).pattern("yyyy-M-d h:m:s.S") ==> 2006-7-2 8:9:4.18
 */
Date.prototype.pattern=function(fmt) {
    var o = {
        "M+" : this.getMonth()+1, //月份
        "d+" : this.getDate(), //日
        "h+" : this.getHours()%12 == 0 ? 12 : this.getHours()%12, //小时
        "H+" : this.getHours(), //小时
        "m+" : this.getMinutes(), //分
        "s+" : this.getSeconds(), //秒
        "q+" : Math.floor((this.getMonth()+3)/3), //季度
        "S" : this.getMilliseconds() //毫秒
    };
    var week = {
        "0" : "/u65e5",
        "1" : "/u4e00",
        "2" : "/u4e8c",
        "3" : "/u4e09",
        "4" : "/u56db",
        "5" : "/u4e94",
        "6" : "/u516d"
    };
    if(/(y+)/.test(fmt)){
        fmt=fmt.replace(RegExp.$1, (this.getFullYear()+"").substr(4 - RegExp.$1.length));
    }
    if(/(E+)/.test(fmt)){
        fmt=fmt.replace(RegExp.$1, ((RegExp.$1.length>1) ? (RegExp.$1.length>2 ? "/u661f/u671f" : "/u5468") : "")+week[this.getDay()+""]);
    }
    for(var k in o){
        if(new RegExp("("+ k +")").test(fmt)){
            fmt = fmt.replace(RegExp.$1, (RegExp.$1.length==1) ? (o[k]) : (("00"+ o[k]).substr((""+ o[k]).length)));
        }
    }
    return fmt;
};

//4.表格列表初始化
baseTable = {
    //配置dataTable缓存加载，减少ajax的请求次数（管道分页）
    config : function () {
        if(typeof $.fn.dataTable == 'function') {
            //
            // Pipelining function for DataTables. To be used to the `ajax` option of DataTables
            //
            $.fn.dataTable.pipeline = function ( opts ) {
                // Configuration options
                var conf = $.extend( {
                    pages: 5,     // number of pages to cache
                    url: '',      // script url
                    data: null,   // function or object with parameters to send to the server
                                  // matching how `ajax.data` works in DataTables
                    method: 'GET' // Ajax HTTP method
                }, opts );

                // Private variables for storing the cache
                var cacheLower = -1;
                var cacheUpper = null;
                var cacheLastRequest = null;
                var cacheLastJson = null;

                return function ( request, drawCallback, settings ) {
                    var ajax          = false;
                    var requestStart  = request.start;
                    var drawStart     = request.start;
                    var requestLength = request.length;
                    var requestEnd    = requestStart + requestLength;

                    if ( settings.clearCache ) {
                        // API requested that the cache be cleared
                        ajax = true;
                        settings.clearCache = false;
                    }
                    else if ( cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper ) {
                        // outside cached data - need to make a request
                        ajax = true;
                    }
                    else if ( JSON.stringify( request.order )   !== JSON.stringify( cacheLastRequest.order ) ||
                        JSON.stringify( request.columns ) !== JSON.stringify( cacheLastRequest.columns ) ||
                        JSON.stringify( request.search )  !== JSON.stringify( cacheLastRequest.search )
                    ) {
                        // properties changed (ordering, columns, searching)
                        ajax = true;
                    }

                    // Store the request for checking next time around
                    cacheLastRequest = $.extend( true, {}, request );

                    if ( ajax ) {
                        // Need data from the server
                        if ( requestStart < cacheLower ) {
                            requestStart = requestStart - (requestLength*(conf.pages-1));

                            if ( requestStart < 0 ) {
                                requestStart = 0;
                            }
                        }

                        cacheLower = requestStart;
                        cacheUpper = requestStart + (requestLength * conf.pages);

                        request.start = requestStart;
                        request.length = requestLength*conf.pages;

                        // Provide the same `data` options as DataTables.
                        if ( $.isFunction ( conf.data ) ) {
                            // As a function it is executed with the data object as an arg
                            // for manipulation. If an object is returned, it is used as the
                            // data object to submit
                            var d = conf.data( request );
                            if ( d ) {
                                $.extend( request, d );
                            }
                        }
                        else if ( $.isPlainObject( conf.data ) ) {
                            // As an object, the data given extends the default
                            $.extend( request, conf.data );
                        }

                        settings.jqXHR = $.ajax( {
                            "type":     conf.method,
                            "url":      conf.url,
                            "data":     request,
                            "dataType": "json",
                            "cache":    false,
                            "success":  function ( json ) {
                                cacheLastJson = $.extend(true, {}, json);

                                if ( cacheLower != drawStart ) {
                                    json.data.splice( 0, drawStart-cacheLower );
                                }
                                if ( requestLength >= -1 ) {
                                    json.data.splice( requestLength, json.data.length );
                                }

                                drawCallback( json );
                            }
                        } );
                    }
                    else {
                        json = $.extend( true, {}, cacheLastJson );
                        json.draw = request.draw; // Update the echo for each response
                        json.data.splice( 0, requestStart-cacheLower );
                        json.data.splice( requestLength, json.data.length );

                        drawCallback(json);
                    }
                }
            };

            // Register an API method that will empty the pipelined data, forcing an Ajax
            // fetch on the next draw (i.e. `table.clearPipeline().draw()`)
            $.fn.dataTable.Api.register( 'clearPipeline()', function () {
                return this.iterator( 'table', function ( settings ) {
                    settings.clearCache = true;
                } );
            } );
        }
    },
    init : function (jQueryElements, url) {
        if(jQueryElements.length && url.length){
            var lastIdx = null;
            var dataTable = jQueryElements.DataTable({
                "aLengthMenu" : [20, 40, 50, 60, 100, 500, 1000], //更改显示记录数选项
                "iDisplayLength" : 40, //默认显示的记录数
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": false,
                "info": true,
                "processing": true,
                "serverSide": true,
                "ajax": url,
                "bStateSave": true,//是否开启cookies保存当前状态信息
                "autoWidth":false,//关掉适配
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

            jQueryElements.find('tbody').on('mouseover', 'td', function () {
                if(typeof dataTable.cell(this).index() != 'undefined') {
                    var colIdx = dataTable.cell(this).index().column;
                    if (colIdx !== lastIdx) {
                        $(dataTable.cells().nodes()).removeClass('highlight');
                        $(dataTable.column(colIdx).nodes()).addClass('highlight');
                    }
                }
            }).on('mouseleave', function () {
                $(dataTable.cells().nodes()).removeClass('highlight');
            });

            return dataTable;
        }
    }
};

//5.textarea高度自适应
$(function () {
    $(document).on('input propertychange', 'textarea.autoHeight', function (e) {
        var target = e.target;
        //保存初始高度
        var defaultHeight = $(target).attr('defaultHeight') || 0;
        if(!defaultHeight){
            defaultHeight = target.clientHeight;
            $(target).attr('defaultHeight', defaultHeight);
        }
        //设置高度
        target.style.height = defaultHeight + 'px';
        var clientHeight = target.clientHeight,
            scrollHeight = target.scrollHeight;
        if(clientHeight !== scrollHeight){
            target.style.height = scrollHeight + 5 + 'px';
        }
    });
    //初始化
    $('.autoHeight').each(function () {
        $(this).height($(this).get(0).scrollHeight+ 5 +'px');
    });
});

//3.选择微信角色
$(function () {
    $('.checkwxuser').on('click', function () {
        //模态窗初始化
        if(!$('#checkwxuser-dialog').length) {
            //生成二级密码弹窗代码
            var _dialog_html = '<div class="modal fade" id="checkwxuser-dialog">'+
                '<div class="modal-dialog" style="width:750px;">'+
                '<div class="modal-content">'+
                '<div class="modal-header">'+
                '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
                '<h4 class="modal-title"><i class="ion-ios-people"></i> 选择角色</h4>'+
                '</div>'+
                '<div class="modal-body">'+
                    '<div class="form-group">'+
                        '<div class="input-group">'+
                        '<input type="text" class="form-control" name="keyword" id="search_keyword" placeholder="请输入昵称/姓名/手机号" />'+
                        '<span class="input-group-btn"><button type="button" class="btn btn-default search_members">搜索</button></span>'+
                        '</div>'+
                    '</div>'+
                    '<div class="module-menus-notice" id="module-menus-notice" style="padding-bottom: 5px;"></div>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '</div>';
            $('body').append(_dialog_html);
        }
        $('#checkwxuser-dialog').modal({
            backdrop: 'static',
            keyboard: false,
            show: true
        });
        //搜索
        $('.search_members').on('click', function () {
            var $keyword = $('#search_keyword').val();
            if($keyword == '') return false;
            //模拟数据,后期需要通过ajax从数据库进行搜索
            $.ajax({
                url: ThinkPHP['MODULE'] + '/User/searchMembers',
                type: "GET",
                data: {
                    keyword: $('#search_keyword').val(),
                },
                beforeSend : function (jqXHR, settings) {
                    //加载中
                    $('#module-menus-notice').html('<div>正在搜索...</div>');
                },
                success: function(response, textStatus, jqXHR) {
                    $('#module-menus-notice').html(response);
                    //选择
                    $('.select_member').on('click', function () {
                        var _userinfo = $(this).data('userinfo');
                        $('#userid').val(_userinfo.id);
                        $('#saler').val(_userinfo.nickname+'/'+_userinfo.realname+'/'+_userinfo.phone);
                        $('#saleravatar').show().find('img').attr('src', _userinfo.avatar);
                        $('#checkwxuser-dialog').modal('hide');
                        //初始化
                        $('#module-menus-notice').html('');
                        $('#search_keyword').val('');
                    });
                }
            });
        });
    });
    //清除选择
    $('.clearwxuser').on('click', function () {
        $('#userid').val('');
        $('#saler').val('');
        $('#saleravatar').hide();
    });
});