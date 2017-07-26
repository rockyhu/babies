<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>爱尚缘运营系统 | 系统参数</title>
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
	<!-- Theme style -->
	<link href="__BASE__/AdminLTE/dist/css/AdminLTE.min.css" rel="stylesheet" type="text/css" />
	<AdminLTE.2.1.2nLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
	<link href="__BASE__/AdminLTE/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
	<!-- jquery-confirm.min.css -->
	<link href="__CSS__/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
	<!-- 自定义CSS -->
	<link rel="stylesheet" href="__CSS__/admin.css">
	<link rel="icon" href="__ROOT__/favicon.ico" type="image/x-icon" >
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
			<h1>系统参数</h1>
			<ol class="breadcrumb">
				<li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
				<li>系统管理</li>
				<li class="active">系统参数</li>
			</ol>
		</section>

		<!-- Main content -->
		<section class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="box box-primary">
						<div class="box-body">
							<ul class="nav nav-tabs">
								<li class="active"><a href='{:U("System/index")}'><i class="ion-ios-paper"></i> 系统参数</a></li>
							</ul>
							<div class="panel panel-default no-border no-panel">
								<div class="panel-body no-padding">
									<form method="post" id="system-set">
										<input type="hidden" name="id" value="{$oneSystem.id}" />
										<!--<div id="error-wrap"></div>-->
										<table class="table table-bordered">
                                            <tr>
                                                <th style="width:200px;text-align:center;vertical-align:middle;" rowspan="1">站点名称</th>
                                                <td colspan="3"><input type="text" name="webname" class="form-control" placeholder="站点名称" value="{$oneSystem.webname}" /></td>
                                            </tr>
                                            <tr>
                                                <th style="width:200px;text-align:center;vertical-align:middle;">站点关键字</th>
                                                <td colspan="4"><input type="text" name="keywords" class="form-control" placeholder="站点关键字" value="{$oneSystem.keywords}" /></td>
                                            </tr>
											<tr>
												<th style="width:200px;text-align:center;vertical-align:middle;">站点描述</th>
												<td colspan="4"><textarea rows="3" name="description" class="form-control" placeholder="站点描述">{$oneSystem.description}</textarea></td>
											</tr>
                                            <tr>
                                                <th style="width:200px;text-align:center;vertical-align:middle;">站点版权信息</th>
                                                <td colspan="4"><textarea rows="3" name="copyright" class="form-control" placeholder="站点版权信息">{$oneSystem.copyright}</textarea></td>
                                            </tr>
											<tr>
												<th style="width:200px;text-align:center;vertical-align:middle;">站点备案信息</th>
												<td colspan="4"><textarea rows="3" name="beian" class="form-control" placeholder="站点备案信息">{$oneSystem.beian}</textarea></td>
											</tr>
											<tr>
												<th style="width:200px;text-align:center;vertical-align:middle;" rowspan="3">系统维护</th>
												<td style="width:120px;vertical-align:middle;">系统停用</td>
												<td colspan="3"><input type="checkbox" name="shutdownstate" value="1" <eq name="oneSystem.shutdownstate" value="1">checked="checked"</eq>  /> <small class="tips">（选中即停用，取消选中即开启）</small></td>
											</tr>
											<tr>
												<td style="width:120px;vertical-align:middle;">维护页面标题</td>
												<td colspan="3"><input type="text" name="shutdowntitle" class="form-control input-sm" value="{$oneSystem.shutdowntitle}" /></td>
											</tr>
											<tr>
												<td style="width:120px;vertical-align:middle;">维护页面内容</td>
												<td colspan="3"><textarea rows="3" name="shutdowncontent" class="form-control">{$oneSystem.shutdowncontent}</textarea></td>
											</tr>
										</table>
										<div class="box-footer">
											<div class="pull-right"><button type="submit" class="btn btn-primary" id="system-set-btn"><i class="fa fa-save"></i> 保存</button></div>
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
<div class="ui-loading-block" id="loading">
	<div class="ui-loading-cnt">
		<i class="ui-loading-bright"></i>
		<p>正在加载中...</p>
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
<AdminLTE.2.1.2nLTE App -->
<script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.validate.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.form.js" type="text/javascript"></script>
<script src="__JS__/base/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript">
	var ThinkPHP = {
		'ROOT' : '__ROOT__',
		'MODULE' : '__MODULE__'
	};
</script>
<script src="__JS__/system.js" type="text/javascript"></script>
</body>
</html>