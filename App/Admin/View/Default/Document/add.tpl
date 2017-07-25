<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>第一宝贝运营系统 | 文档列表</title>
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
	<link href="__BASE__/AdminLTE/dist/css/skins/_all-skins.min.css" rel="stylesheet" type="text/css" />
	<!-- uploadify -->
	<link rel="stylesheet" type="text/css" href="__KINDEDITOR__/themes/default/default.css" />
	<!-- jquery-confirm.min.css -->
	<link href="__CSS__/jquery-confirm.min.css" rel="stylesheet" type="text/css" />
	<!-- 自定义CSS -->
	<link rel="stylesheet" href="__CSS__/admin.css">
	<link rel="shortcut icon" href="__ROOT__/favicon.ico" />
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
			<h1>文档列表</h1>
			<ol class="breadcrumb">
				<li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i>首页</a></li>
				<li>文档管理</li>
                <li class="active">文档列表</li>
			</ol>
		</section>

		<!-- Main content -->
		<section class="content">
			<div class="row">
				<div class="col-md-12">
					<div class="box box-primary">
						<div class="box-body">
							<ul class="nav nav-tabs">
								<li><a href='{:U("Document/index")}'><i class="ion-ios-paper"></i> 文档列表</a></li>
								{$btn_html}
							</ul>
							<div class="panel panel-default no-border no-panel">
								<div class="panel-body no-padding">
                                    <p class="tips red">备注:带*的为必填项</p>
									<form method="post" id="document-add" name="product_add">
                                        <label>排序 <small class="tips">（数字大的排名在前,默认排序方式为创建时间）</small></label>
                                        <div class="form-group">
                                            <input class="form-control" name="sort" placeholder="排序" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label><span class="red">*</span>文档名称</label>
                                        <div class="form-group">
                                            <input class="form-control" name="name" placeholder="文档名称" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label><span class="red">*</span>文档分类</label>
										<div class="form-group">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <select class="form-control select2" name="nnid">
                                                        <option value="-1">请选择一级分类</option>
                                                        <volist name="documentnavlist.one" id="list">
                                                        <option value="{$list.id}" {$list.selected} data-child='{$list.child}'>{$list.text}</option>
                                                        </volist>
                                                    </select>
                                                    <span class="errorLabel"></span>
                                                </div>
                                                <div class="col-md-6">
                                                    <select class="form-control select2" name="nid">
                                                        <option value="-1">请选择二级分类</option>
                                                        <volist name="documentnavlist.two" id="two">
                                                            <option value="{$two.id}" {$two.selected}>{$two.text}</option>
                                                        </volist>
                                                    </select>
                                                    <span class="errorLabel"></span>
                                                </div>
                                            </div>
										</div>
                                        <label>文档属性</label>
                                        <div class="form-group">
                                            <label style="color: #666;font-weight: 400;padding-right:10px;"><input type="checkbox" name="istop" value="1"> 置顶</label>
                                            <label style="color: #666;font-weight: 400;padding-right:10px;"><input type="checkbox" name="isrecommand" value="1"> 推荐</label>
                                            <label style="color: #666;font-weight: 400;padding-right:10px;"><input type="checkbox" name="isgun" value="1"> 滚动</label>
                                            <label style="color: #666;font-weight: 400;padding-right:10px;"><input type="checkbox" name="ishuan" value="1"> 幻灯</label>
                                        </div>
                                        <label>缩略图 <small class="tips">（建议尺寸: 640 * 640 ，或正方型图片）</small></label>
                                        <div class="form-group fileupload">
                                            <a href="javascript:void(0);" id="plupload-thumb-btn" class="btn btn-default plupload-select-files"><i class="ion-image"></i> 上传缩略图</a>
                                            <div class="imglist clearfix"></div>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label><span class="red">*</span>文档关键字</label>
                                        <div class="form-group">
                                            <input class="form-control" name="keyword" placeholder="文档关键字，关键字之间用,隔开" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>文档关键字描述</label>
                                        <div class="form-group">
                                            <textarea name="description" class="textbox form-control" placeholder="文档关键字描述" style="width:100%;height:80px"></textarea>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>文档简介</label>
                                        <div class="form-group">
                                            <textarea name="info" class="textbox form-control" placeholder="文档简介" style="width:100%;height:80px"></textarea>
                                            <span class="errorLabel"></span>
                                        </div>
										<label>文档内容</label>
										<div class="form-group">
											<textarea name="content" class="textbox textarea" style="width:100%;height:300px"></textarea>
											<span class="errorLabel"></span>
										</div>
                                        <label>阅读数</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <input class="form-control" name="readcount" placeholder="阅读数" />
                                                <span class="input-group-addon">次</span>
                                            </div>
                                            <span class="errorLabel"></span>
                                        </div>
										<label>文档状态</label>
										<div class="form-group">
											<select class="form-control" name="status" data-placeholder="选择文档状态" style="width: 100%;">
												<option value="0">待发布</option>
												<option value="1">已发布</option>
											</select>
											<span class="errorLabel"></span>
										</div>
										<div class="box-footer">
											<div class="pull-right">
												<button type="submit" class="btn btn-primary" id="document-add-btn"> <i class="fa fa-save"></i> 保存</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
					<!-- /. box -->
				</div>
				<!-- /.col -->
			</div>
			<!-- /.row -->
		</section>
		<!-- /.content -->
	</div>
	<!-- /.content-wrapper -->

	<include file="Public/footer" />
</div>
<!-- ./wrapper -->

<!-- jQuery 1.11.3 -->
<script src="__JS__/base/jquery.min.js" type="text/javascript"></script>
<!-- jQuery UI 1.11.4 -->
<script src="__BASE__/AdminLTE/plugins/jQueryUI/jquery-ui.min.js" type="text/javascript"></script>
<!-- Bootstrap 3.3.2 JS -->
<script src="__BASE__/AdminLTE/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
<!-- SlimScroll -->
<script src="__BASE__/AdminLTE/plugins/slimScroll/jquery.slimscroll.min.js" type="text/javascript"></script>
<!-- FastClick -->
<script src="__BASE__/AdminLTE/plugins/fastclick/fastclick.min.js" type="text/javascript"></script>
<!-- Select2 -->
<script src="__BASE__/select2-4.0.3/js/select2.full.min.js" type="text/javascript"></script>
<script src="__BASE__/select2-4.0.3/js/i18n/zh-CN.js" type="text/javascript"></script>
<script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
<!-- uploadify -->
<script type="text/javascript" src="__BASE__/plupload-2.3.1/js/plupload.full.min.js"></script>
<script type="text/javascript" src="__KINDEDITOR__/kindeditor.js"></script>
<script type="text/javascript" src="__KINDEDITOR__/lang/zh_CN.js"></script>
<script src="__JS__/base/jquery.validate.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.form.js" type="text/javascript"></script>
<script src="__JS__/base/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript">
	var ThinkPHP = {
		'ROOT' : '__ROOT__',
		'MODULE' : '__MODULE__',
		'KINDEDITOR' : '__KINDEDITOR__',
        'IMAGEURL' : '{:U("File/plupload")}',
        'IMG' : '__IMG__',
        'PLUPLOAD' : '__BASE__/plupload-2.3.1',
		'DOCUMENT' : '{:U("Document/index")}'
	};
</script>
<script src="__JS__/base/base.js" type="text/javascript"></script>
<script src="__JS__/document.js" type="text/javascript"></script>
</body>
</html>