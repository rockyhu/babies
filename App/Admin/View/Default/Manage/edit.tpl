<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统 | 管理员管理</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
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
    <!-- Select2 -->
    <link href="__BASE__/select2-4.0.3/css/select2.min.css" rel="stylesheet">
    <!-- Theme style -->
    <link href="__BASE__/AdminLTE/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <AdminLTE.2.1.2nLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="__BASE__/AdminLTE/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- jquery-confirm.min.css -->
    <link href="__CSS__/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
    <!-- 自定义CSS -->
    <link rel="stylesheet" href="__CSS__/admin.css">
    <link rel="shortcut icon" href="__ROOT__/favicon.ico"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
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
            <h1>管理员管理</h1>
            <ol class="breadcrumb">
                <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href='javscript:void(0);'> 系统管理</a></li>
                <li class="active">管理员管理</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-body">
                            <ul class="nav nav-tabs">
                                <li><a href='{:U("Manage/index")}'><i class="ion-ios-paper"></i> 管理员列表</a></li>
                                {$btn_html}
                                <li class="active"><a href='javascript:void(0);'><i class="ion-ios-compose"></i> 修改管理员</a></li>
                            </ul>
                            <div class="panel panel-default no-border no-panel">
                                <div class="panel-body no-padding">
                                    <form method="post" id="manage-edit">
                                        <input type="hidden" name="id" id="manage-edit-id" value="{$OneManage.id}">
                                        <label>管理员账号</label>
                                        <div class="form-group">
                                            <input class="form-control" name="manager" value="{$OneManage.manager}" disabled placeholder="管理员账号，必须是电子邮箱" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>真实姓名</label>
                                        <div class="form-group">
                                            <input class="form-control" name="realname" value="{$OneManage.realname}" placeholder="管理员真实姓名" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>登录密码</label>
                                        <div class="form-group">
                                            <input type="password" class="form-control" name="password" id="password" placeholder="登录密码，为空则不修改" />
                                        </div>
                                        <label class="info">确认密码</label>
                                        <div class="form-group">
                                            <input type="password" class="form-control" name="repassword" placeholder="确认密码，为空则不修改" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>角色分配</label>
                                        <div class="form-group">
                                            <select class="form-control select2" name="role" data-placeholder="请选择管理员角色" style="width:100%;">
                                                <option value="{$OneManage.role}" selected>{$OneManage.title}</option>
                                            </select>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <div class="box-footer">
                                            <div class="pull-right">
                                                <button type="submit" class="btn btn-primary" id="manage-edit-btn"><i class="fa fa-save"></i> 保存</button>
                                            </div>
                                        </div><!-- /.box-footer -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div><!-- /. box -->
                </div><!-- /.col -->
            </div><!-- /.row -->
        </section><!-- /.content -->
    </div><!-- /.content-wrapper -->

    <include file="Public/footer" />
</div><!-- ./wrapper -->

<!-- jQuery 1.11.3 -->
<script src="__JS__/base/jquery.min.js" type="text/javascript"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="__BASE__/AdminLTE/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- SlimScroll -->
<script src="__BASE__/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- FastClick -->
<script src="__BASE__/AdminLTE/plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
<!-- Select2 -->
<script src="__BASE__/select2-4.0.3/js/select2.full.min.js" type="text/javascript"></script>
<script src="__BASE__/select2-4.0.3/js/i18n/zh-CN.js" type="text/javascript"></script>
<AdminLTE.2.1.2nLTE App -->
<script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.validate.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.form.js" type="text/javascript"></script>
<script src="__JS__/base/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript">
    var ThinkPHP = {
        'ROOT' : '__ROOT__',
        'MODULE' : '__MODULE__',
        'MANAGE' : '{:U("Manage/index")}'
    };
</script>
<script src="__JS__/manage.js" type="text/javascript"></script>
</body>
</html>