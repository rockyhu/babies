<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统 | 权限管理</title>
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
            <h1>权限管理</h1>
            <ol class="breadcrumb">
                <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href='javascript:void(0);'> 系统管理</a></li>
                <li class="active">权限管理</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-body">
                            <ul class="nav nav-tabs">
                                <li><a href='{:U("AuthGroup/index")}'><i class="ion-ios-paper"></i> 角色列表</a></li>
                                {$btn_html}
                            </ul>
                            <div class="panel panel-default no-border no-panel">
                                <div class="panel-body no-padding">
                                    <form method="post" id="authgroup-add">
                                        <label>角色名称</label>
                                        <div class="form-group">
                                            <input class="form-control" name="title" placeholder="角色名称" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>权限分配</label>
                                        <div class="form-group">
                                            <table id="table-rules" class="table table-bordered" width="100%">
                                                <thead>
                                                <tr class="info">
                                                    <th>菜单分类</th>
                                                    <th>菜单名称</th>
                                                    <th>操作名称</th>
                                                    <th>操作编码</th>
                                                    <th style="width:70px;">操作</th>
                                                    <th style="width:50px;text-align:center;"><input type="checkbox" name="checkall" id="checkall" /></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <volist name="allNavlist" id="list">
                                                    <tr class="first{$list.id}"><td rowspan="{$list.rowcount}">{$list.text}</td></tr>
                                                    <volist name="list.subNavlist" id="subNavlist1">
                                                        <tr class="second{$subNavlist1.id}">
                                                            <td rowspan="{$subNavlist1.rowcount}">{$subNavlist1.text}</td>
                                                            <td style="text-align:left;">{$subNavlist1.navdotext}</td>
                                                            <td style="text-align:left;">{$subNavlist1.url}</td>
                                                            <td></td>
                                                            <td align="center" class="checkboxtd"><input type="checkbox" name="rules[{$subNavlist1.id}][]" value="{$subNavlist1.text}||{$subNavlist1.url}" /></td>
                                                        </tr>
                                                        <volist name="subNavlist1.twoSubNavlist" id="twosubNavlist">
                                                            <tr class="second{$subNavlist1.id}-child">
                                                                <td style="text-align:left;">{$twosubNavlist.text}</td>
                                                                <td style="text-align:left;">{$twosubNavlist.url}</td>
                                                                <td>
                                                                    <a href="javascript:void(0);" title="编辑" data-id="{$twosubNavlist.id}" data-text="{$twosubNavlist.text}" data-url="{$twosubNavlist.url}" data-navtext="{$subNavlist1.text}" class="btn btn-primary btn-xs navdo-edit-btn"><i class="ion-ios-compose-outline"></i></a>
                                                                    <a href="javascript:void(0);" title="删除" data-id="{$twosubNavlist.id}" data-navid="{$subNavlist1.id}" data-firstnavid="{$list.id}" data-text="{$twosubNavlist.text}" data-navtext="{$subNavlist1.text}" class="btn btn-primary btn-xs navdo-del-btn"><i class="ion-ios-close-outline"></i></a>
                                                                </td>
                                                                <td align="center" class="checkboxtd"><input type="checkbox" name="rules[{$subNavlist1.id}][]" value="{$twosubNavlist.text}||{$twosubNavlist.url}" /></td>
                                                            </tr>
                                                        </volist>
                                                        <tr>
                                                            <td colspan="4" style="text-align:left;"><a href="javascript:void(0);" data-navid="{$subNavlist1.id}" data-navtext="{$subNavlist1.text}" data-firstnavid="{$list.id}" class="btn btn-primary btn-xs navdo-add-btn"><i class="ion-ios-plus-outline"></i> 添加</a></td>
                                                        </tr>
                                                    </volist>
                                                </volist>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="box-footer">
                                            <div class="pull-right">
                                                <button type="submit" class="btn btn-primary" id="authgroup-add-btn"><i class="fa fa-save"></i> 保存</button>
                                            </div>
                                        </div>
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

<!-- 添加菜单操作 -->
<div class="modal fade" id="navdo-add-dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="navdo-add-form" method="post">
                <input type="hidden" name="navid">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="ion-ios-cog"></i> 添加菜单操作</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="navtext" disabled="disabled" placeholder="菜单名称" />
                        <span class="glyphicon glyphicon-baby-formula form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="text" placeholder="菜单操作名称" />
                        <span class="glyphicon glyphicon-book form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="url" placeholder="菜单操作链接" />
                        <span class="glyphicon glyphicon-link form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" id="navdo-add-form-btn" class="btn btn-primary">保存</button></div>
            </form>
        </div>
    </div>
</div>

<!-- 编辑菜单操作 -->
<div class="modal fade" id="navdo-edit-dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="navdo-edit-form" method="post">
                <input type="hidden" name="id">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="ion-ios-cog"></i> 编辑菜单操作</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="navtext" disabled="disabled" placeholder="菜单名称" />
                        <span class="glyphicon glyphicon-baby-formula form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="text" placeholder="菜单操作名称" />
                        <span class="glyphicon glyphicon-book form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="url" placeholder="菜单操作链接" />
                        <span class="glyphicon glyphicon-link form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" id="navdo-edit-form-btn" class="btn btn-primary">保存</button></div>
            </form>
        </div>
    </div>
</div>

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
        'AuthGroup' : '{:U("AuthGroup/index")}'
    };
</script>
<script src="__JS__/authgroup.js" type="text/javascript"></script>
</body>
</html>