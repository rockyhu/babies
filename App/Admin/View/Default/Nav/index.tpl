<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统 | 栏目管理</title>
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
          <h1>栏目管理</h1>
          <ol class="breadcrumb">
            <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
            <li><a href='javasript:void(0);'> 系统管理</a></li>
            <li class="active">栏目管理</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
          <div class="row">
            <div class="col-md-12">
              <div class="box box-primary">
                <div class="box-body">
                    <ul class="nav nav-tabs">
                      <li class="active"><a href='{:U("Nav/index")}'><i class="ion-ios-paper"></i> 栏目列表</a></li>
                      {$btn_html}
                    </ul>
                    <div class="panel panel-default no-border no-panel">
                      <div class="panel-body no-padding">
                      <table id="table-navs" class="table table-bordered table-hover" width="100%">
                          <thead>
                          <tr>
                            <th></th>
                            <th>菜单名称</th>
                            <th>菜单状态</th>
                            <th>菜单链接</th>
                            <th>菜单图标</th>
                            <th>菜单状态</th>
                            <th>菜单排序</th>
                            <th>所属主菜单</th>
                            <th width="45px">操作</th>
                          </tr>
                          </thead>
                      </table>
                      </div>
                    </div>
                </div><!-- /.box-body -->

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
        	'NAV' : '{:U("Nav/index")}',
          'Navlist':'{:U("Nav/ajaxlistNav")}'
        };
    </script>
    <script src="__JS__/base/base.js" type="text/javascript"></script>
    <script src="__JS__/nav.js" type="text/javascript"></script>
  </body>
</html>