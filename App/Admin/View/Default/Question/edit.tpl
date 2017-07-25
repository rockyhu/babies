<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统 | 常见问题</title>
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
    <link rel="stylesheet" type="text/css" href="__KINDEDITOR__/themes/default/default.css" />
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
            <h1>常见问题</h1>
            <ol class="breadcrumb">
                <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
                <li class="active">常见问题</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-body">
                            <ul class="nav nav-tabs">
                                <li><a href='{:U("Question/index")}'><i class="ion-ios-paper"></i> 常见问题列表</a></li>
                                {$btn_html}
                                <li class="active"><a href='javascript:void(0);'><i class="ion-ios-compose"></i> 修改常见问题</a></li>
                            </ul>
                            <div class="panel panel-default no-border no-panel">
                                <div class="panel-body no-padding">
                                    <form method="post" id="question-edit">
                                        <input type="hidden" name="id" id="question-edit-id" value="{$OneQuestion.id}">
                                        <label>常见问题标题 <small class="tips">（常见问题标题长度不大于50个字符）</small></label>
                                        <div class="form-group">
                                            <input class="form-control" name="title" value="{$OneQuestion.title}" placeholder="常见问题标题" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>常见问题标签</label>
                                        <div class="form-group">
                                            <select class="select2 form-control select4" multiple="multiple" name="tags[]" data-placeholder="请选择常见问题标签" style="width:100%;">
                                                <volist name="OneQuestion.tags" id="tags">
                                                <option value="{$tags.name}" selected>{$tags.name} ({$tags.count})</option>
                                                </volist>
                                            </select>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <div><span class="btn btn-sm btn-info" id="add-tags-btn"><i class="ion-pricetags"></i> 添加新标签</span></div>
                                        <label>常见问题内容</label>
                                        <div class="form-group">
                                            <textarea name="content" class="textbox textarea" style="width:100%;height:300px">{$OneQuestion.content}</textarea>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <div class="box-footer">
                                            <div class="pull-right">
                                                <button type="submit" class="btn btn-primary" id="question-edit-btn"><i class="fa fa-save"></i> 保存</button>
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
<!-- 添加问题标签 -->
<div class="modal fade" id="tags-dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="tags-form" method="post">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="ion-ios-cog"></i> 添加新的问题标签</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="name" placeholder="标签名称" />
                        <span class="glyphicon glyphicon-tags form-control-feedback"></span>
                        <span class="errorLabel"></span>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" id="tags-form-btn" class="btn btn-primary">添加</button></div>
            </form>
        </div>
    </div>
</div>
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
<!-- Select2 -->
<script src="__BASE__/select2-4.0.3/js/select2.full.min.js" type="text/javascript"></script>
<script src="__BASE__/select2-4.0.3/js/i18n/zh-CN.js" type="text/javascript"></script>
<AdminLTE.2.1.2nLTE App -->
<script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.validate.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.form.js" type="text/javascript"></script>
<script src="__JS__/base/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript" src="__KINDEDITOR__/kindeditor.js"></script>
<script type="text/javascript" src="__KINDEDITOR__/lang/zh_CN.js"></script>
<script type="text/javascript">
    var ThinkPHP = {
        'ROOT' : '__ROOT__',
        'MODULE' : '__MODULE__',
        'KINDEDITOR' : '__KINDEDITOR__',
        'IMAGEURL' : '{:U("File/image")}',
        'IMG' : '__IMG__',
        'QUESTION' : '{:U("Question/index")}'
    };
</script>
<script src="__JS__/base/base.js" type="text/javascript"></script>
<script src="__JS__/question.js" type="text/javascript"></script>
</body>
</html>