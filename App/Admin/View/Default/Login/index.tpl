<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- BEGIN PACE PLUGIN FILES -->
    <script src="__BASE__/pace/pace.min.js" type="text/javascript"></script>
    <link href="__BASE__/pace/themes/red/pace-theme-flash.css" rel="stylesheet" type="text/css"/>
    <!-- END PACE PLUGIN FILES -->
    <!-- Bootstrap 3.3.4 -->
    <link href="__BASE__/AdminLTE/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <!-- Font Awesome Icons -->
    <link href="__BASE__/font-awesome-4.6.3/css/font-awesome.min.css" rel="stylesheet">
    <link href="//cdn.bootcss.com/animate.css/3.4.0/animate.min.css" rel="stylesheet">
    <!-- Theme style -->
    <link href="__BASE__/AdminLTE/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
    <!-- iCheck -->
    <link href="__BASE__/AdminLTE/plugins/iCheck/square/blue.css" rel="stylesheet" type="text/css" />
    <!-- 自定义CSS -->
    <link rel="stylesheet" href="__CSS__/admin.css">
	<link rel="shortcut icon" href="__ROOT__/favicon.ico"/>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
        <script src="//cdn.bootcss.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="//cdn.bootcss.com/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body class="login-page">
    <div class="login-box animated bounceInDown">
      <div class="login-logo">
        <b>第一宝贝·运营系统</b>
      </div><!-- /.login-logo -->
      <div class="box login-box-body" style="opacity: 0.8;">
        <form method="POST" id="signin">
          <input type="hidden" name="token" value="{:session('form-token')}">
          <br>
          <div class="form-group has-feedback">
            <input type="email" class="form-control" name="manager" placeholder="电子邮件" />
            <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
            <span class="errorLabel"></span>
          </div>
          <div class="form-group has-feedback">
            <input type="password" class="form-control" name="password" placeholder="密码" />
            <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            <span class="errorLabel"></span>
          </div>
          <div class="row">
            <div class="col-xs-8">
              <div class="checkbox icheck">
                <label>
                  <input type="checkbox" name="auto" id="auto"> 记住账号
                </label>
              </div>
            </div><!-- /.col -->
            <div class="col-xs-4">
              <button type="submit" class="btn btn-primary btn-block btn-flat" id="signbtn">登录</button>
            </div><!-- /.col -->
          </div>
        </form>
      </div><!-- /.login-box-body -->
    </div><!-- /.login-box -->
    <!-- jQuery 1.11.3 -->
    <div class="ui-loading-block" id="loading">
        <div class="ui-loading-cnt">
            <i class="ui-loading-bright"></i>
            <p>正在登录中...</p>
        </div>
    </div>
    <script src="__JS__/base/jquery.min.js" type="text/javascript"></script>
    <!-- Bootstrap 3.3.2 JS -->
    <script src="__BASE__/AdminLTE/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <!-- iCheck -->
    <script src="__BASE__/AdminLTE/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
    <script src="__JS__/base/jquery.validate.min.js" type="text/javascript"></script>
    <script src="__JS__/base/jquery.form.js" type="text/javascript"></script>
    <script src="__JS__/base/jquery.backstretch.min.js"></script>
    <script type="text/javascript">
        var ThinkPHP = {
        	'ROOT' : '__ROOT__',
        	'MODULE' : '__MODULE__',
            'IMG' : '__IMG__',
        	'INDEX' : '{:U("Index/index")}'
        };
    </script>
    <script src="__JS__/login.js" type="text/javascript"></script>
  </body>
</html>