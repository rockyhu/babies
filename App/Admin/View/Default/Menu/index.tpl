<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统 | 自定义菜单</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <!-- BEGIN PACE PLUGIN FILES -->
    <script src="__BASE__/pace/pace.min.js" type="text/javascript"></script>
    <link href="__BASE__/pace/themes/red/pace-theme-flash.css" rel="stylesheet" type="text/css"/>
    <!-- END PACE PLUGIN FILES -->
    <!-- Bootstrap 3.3.4 -->
    <link href="__BASE__/AdminLTE/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="__BASE__/font-awesome-4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <!-- Ionicons -->
    <link href="__BASE__/ionicons-2.0.1/css/ionicons.min.css" rel="stylesheet">
    <!-- DATA TABLES -->
    <link href="__BASE__/AdminLTE/plugins/jquery.datatables/css/dataTables.bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Theme style -->
    <link href="__BASE__/AdminLTE/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <AdminLTE.2.1.2nLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="__BASE__/AdminLTE/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- jquery-confirm.min.css -->
    <link href="__CSS__/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
    <!-- jquery-confirm.min.css -->
    <link href="__CSS__/admin.css" rel="stylesheet" type="text/css" />
    <link rel="shortcut icon" href="__ROOT__/favicon.ico"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .table>tbody>tr>td {
            text-align:left;
        }
    </style>
</head>
<body class="skin-blue fixed sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
    <!-- the header -->
    <include file="Public/header" />
    <!-- the sidebar -->
    <include file="Public/sidebar" />

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>自定义菜单</h1>
            <ol class="breadcrumb">
                <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href='javasript:void(0);'> 公众号管理</a></li>
                <li class="active">自定义菜单</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-body" id="wechatMenu">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href='{:U("Menu/index")}'><i class="ion-ios-paper"></i> 自定义菜单</a></li>
                            </ul>
                            <div class="panel panel-default no-panel">
                                <div class="panel-heading">菜单设计器 <small class="tips">编辑和设置公众号菜单, 必须拥有自定义菜单权限</small></div>
                                <div class="table-responsive panel-body">
                                    <table class="table table-hover">
                                        <tbody class="designer ui-sortable">
                                        <volist name="menusArr" id="menu">
                                            <tr class="hover ng-scope">
                                                <td style="border-top: none;">
                                                    <div class="parentmenu">
                                                        <input type="hidden" name="parent" data-role="parent">
                                                        <input type="text" class="form-control ng-pristine ng-untouched ng-valid" data-role="parent" value="{$menu.name}" placeholder="菜单名称" style="display: inline-block; width: 300px;">
                                                        <a href="javascript:void(0);" class="first-child" data-role="parent" title="拖动调整此菜单位置"><i class="fa fa-arrows"></i></a>
                                                        <a href="javascript:void(0);" class="setAction" data-role="parent" title="设置此菜单动作"<notempty name="menu.sub_button">style="display:none;"</notempty>><i class="fa fa-pencil"></i> 设置此菜单动作</a>
                                                        <a href="javascript:void(0);" class="deleteMenu" data-role="parent" title="删除此菜单"><i class="fa fa-remove"></i>删除此菜单</a>
                                                        <a href="javascript:void(0);" class="addSubMenu" data-role="parent" title="添加子菜单"><i class="fa fa-plus"></i> 添加子菜单</a>
                                                    </div>
                                                    <notempty name="menu.sub_button">
                                                        <div class="designer sonmenu ui-sortable">
                                                            <volist name="menu.sub_button" id="sub_button">
                                                                <div class="sonmenulist ng-scope">
                                                                    <input type="hidden" name="sub" data-role="sub">
                                                                    <input type="text" class="form-control ng-pristine ng-untouched ng-valid" data-role="sub" value="{$sub_button.name}" placeholder="子菜单名称" style="display: inline-block; width: 220px;">
                                                                    <a href="javascript:void(0);" class="first-child" data-role="sub" title="拖动调整此菜单位置"><i class="fa fa-arrows"></i></a>
                                                                    <a href="javascript:void(0);" class="setAction" data-role="sub" title="设置此菜单动作"><i class="fa fa-pencil"></i> 设置此菜单动作</a>
                                                                    <a href="javascript:void(0);" class="deleteMenu" data-role="sub" title="删除此菜单"><i class="fa fa-remove"></i> 删除此菜单</a>
                                                                </div>
                                                            </volist>
                                                        </div>
                                                    </notempty>
                                                </td>
                                            </tr>
                                        </volist>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="panel-footer">
                                    <a href="javascript:void(0);" class="addMenu">添加菜单 <i class="fa fa-plus-circle"></i></a>
                                    <span class="text-muted">可以使用<i class="fa fa-arrows"></i> 进行拖动排序</span>
                                </div>
                            </div>
                            <div class="panel panel-default no-panel">
                                <div class="panel-heading">操作 <small class="tips">设计好菜单后再进行保存操作</small></div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <button class="btn btn-primary" id="saveMenu">保存菜单结构</button>
                                        <small class="tips clearfix">菜单设计完成将在所有支持的公众号上生效.成功保存当前菜单结构至公众平台后, 由于缓存可能需要在24小时内生效</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <include file="Public/footer" />
</div>
<!-- ./wrapper -->

<!-- 设置菜单执行动作模态窗 -->
<div class="modal fade" id="setMenuAction-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">选择菜单【未命名菜单】要执行的操作</h4>
            </div>
            <div class="modal-body">
                <form id="setMenuAction-form">
                    <input type="hidden" name="menu_index" id="meun_index">
                    <input type="hidden" name="submenu_index" id="submenu_index">
                    <span>
                        <label class="radio-inline"><input type="radio" name="ipt" value="view" checked="checked" class="ng-pristine ng-untouched ng-valid"> 链接</label>
                        <label class="radio-inline"><input type="radio" name="ipt" value="click" class="ng-pristine ng-untouched ng-valid"> 触发关键字</label>
                        <label class="radio-inline"><input type="radio" name="ipt" value="scancode_push" class="ng-pristine ng-untouched ng-valid"> 扫码</label>
                        <label class="radio-inline"><input type="radio" name="ipt" value="scancode_waitmsg" class="ng-pristine ng-untouched ng-valid"> 扫码（等待信息）</label>
                        <label class="radio-inline"><input type="radio" name="ipt" value="pic_sysphoto" class="ng-pristine ng-untouched ng-valid"> 系统拍照发图</label>
                        <label class="radio-inline"><input type="radio" name="ipt" value="pic_photo_or_album" class="ng-pristine ng-untouched ng-valid"> 拍照或者相册发图</label>
                        <label class="radio-inline"> <input type="radio" name="ipt" value="pic_weixin" class="ng-pristine ng-untouched ng-valid"> 微信相册发图</label>
                        <label class="radio-inline"> <input type="radio" name="ipt" value="location_select" class="ng-pristine ng-untouched ng-valid"> 地理位置</label>
                    </span>
                    <label class="radio-inline"><input type="radio" name="ipt" value="media_id" class="ng-valid ng-dirty ng-valid-parse ng-touched"> 回复素材</label>
                    <label class="radio-inline"><input type="radio" name="ipt" value="view_limited" class="ng-pristine ng-untouched ng-valid"> 跳转图文</label>
                    <div id="action-input-container">
                        <div class="action-input show">
                            <hr>
                            <div class="input-group" style="display: block;">
                                <input type="text" class="form-control ng-pristine ng-untouched ng-valid" value="http://" placeholder="链接地址">
                            </div>
                            <small class="tips">指定点击此菜单时要跳转的链接（注：链接需加http://）</small>
                        </div>
                        <div class="action-input">
                            <hr>
                            <div class="input-group" style="display: block;">
                                <input type="text" class="form-control ng-pristine ng-untouched ng-valid" placeholder="关键字">
                            </div>
                            <small class="tips">指定点击此菜单时要执行的操作, 你可以在这里输入关键字,那么点击这个菜单时就就相当于发送这个内容至公众号平台</small> <small class="tips"><strong>这个过程是程序模拟的,比如这里添加关键字: 优惠券, 那么点击这个菜单时, 公众号平台相当于接受了粉丝用户的消息, 内容为"优惠券"</strong></small>
                        </div>
                        <div class="action-input">
                            <hr>
                            <div class="input-group" style="display: block;">
                                <input type="text" class="form-control ng-pristine ng-untouched ng-valid" placeholder="素材id">
                            </div>
                            <small class="tips">公众平台的素材id</small>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-save-action">保存</button>
            </div>
        </div>
    </div>
</div>

<!-- jQuery 1.11.3 -->
<script src="__JS__/base/jquery.min.js" type="text/javascript"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="__BASE__/AdminLTE/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- DATA TABES SCRIPT -->
<script src="__BASE__/AdminLTE/plugins/datatables/jquery.dataTables.min.js" type="text/javascript"></script>
<script src="__BASE__/AdminLTE/plugins/datatables/dataTables.bootstrap.min.js" type="text/javascript"></script>
<!-- SlimScroll -->
<script src="__BASE__/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- FastClick -->
<script src="__BASE__/AdminLTE/plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
<AdminLTE.2.1.2nLTE App -->
<script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript">
    var ThinkPHP = {
        'ROOT' : '__ROOT__',
        'MODULE' : '__MODULE__',
        'MENU' : '{:U("MENU/index")}',
        'menus' : {$menus}//保存菜单数组对象，需要初始化，一般是从微信端获取的，然后经过处理
    };
</script>
<script src="__JS__/base/base.js" type="text/javascript"></script>
<script src="__JS__/menu.js" type="text/javascript"></script>
</body>
</html>