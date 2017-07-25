<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="expires" content="1000">
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
    <!-- Theme style -->
    <link href="__BASE__/AdminLTE/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <AdminLTE.2.1.2nLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link href="__BASE__/AdminLTE/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
    <!-- 自定义样式 -->
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
          <h1>控制面板</h1>
          <ol class="breadcrumb">
            <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
            <li class="active">控制面板</li>
          </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            
            <div class="callout callout-danger">
          		<h4>系统须知！</h4>
          		<p>1、为了正常使用本系统，推荐您使用<a href="https://www.google.com/intl/zh-CN/chrome/browser/desktop/index.html?hl=zh-CN&brand=CHMI" target="_blank">谷歌浏览器</a>或<a href="http://www.firefox.com.cn/" target="_blank">火狐浏览器</a>。</p>
          		<p>2、为了系统安全，请不要在公共电脑操作后台系统，如网吧、陌生人电脑等。</p>
          		<p>3、后台操作完成后，如需离开电脑，一定要记得点退出按钮，以免被他人盗取、修改信息，造成不可估量的损失。</p>
          		<p>4、权限设置：如果多人操作后台系统，一定要设置权限分配，每个人操作的功能分工清楚，避免操作重叠，责任不清楚。操作流程：先设置角色管理，再添加管理员，选择相应的权限角色。</p>
          	</div>
            
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
    <AdminLTE.2.1.2nLTE App -->
    <script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
    <script type="text/javascript">
      var ThinkPHP = {
        'ROOT' : '__ROOT__',
        'MODULE' : '__MODULE__'
      };
    </script>
    <script src="__JS__/base/base.js" type="text/javascript"></script>
  </body>
</html>
