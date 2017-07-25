<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>第一宝贝运营系统 | 文档分类</title>
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
            <h1>文档分类</h1>
            <ol class="breadcrumb">
                <li><a href='{:U("Index/index")}'><i class="fa fa-dashboard"></i> 首页</a></li>
                <li><a href='javascript:void(0);'> 文档管理</a></li>
                <li class="active">文档分类</li>
            </ol>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-body">
                            <ul class="nav nav-tabs">
                                <li><a href='{:U("DocumentNav/index")}'><i class="ion-ios-paper"></i> 分类列表</a></li>
                                {$btn_html}
                                <li class="active"><a href='javascript:void(0);'><i class="ion-ios-compose"></i> 修改分类</a></li>
                            </ul>
                            <div class="panel panel-default no-border no-panel">
                                <div class="panel-body no-padding">
                                    <form method="post" id="documentnav-edit">
                                        <input type="hidden" name="id" id="documentnav-edit-id" value="{$OneProductNav.id}">
                                        <label>上级分类</label>
                                        <div class="form-group">
                                            <select class="form-control select4" name="pnid" data-placeholder="请选择上级分类" style="width:100%;">
                                                <notempty name="OneProductNav.pnid">
                                                    <option value="{$OneProductNav.pnid}" selected>{$OneProductNav.ntext}</option>
                                                    <else />
                                                    <option value="-1">主分类</option>
                                                </notempty>
                                            </select>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>分类名称</label>
                                        <div class="form-group">
                                            <input class="form-control" name="text" value="{$OneProductNav.text}" placeholder="分类名称" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label class="info">分类排序</label>
                                        <div class="form-group">
                                            <input class="form-control" name="sort" value="{$OneProductNav.sort}" placeholder="分类排序" />
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>分类图片 <small class="tips">（图片尺寸要求：100px * 100px，大小要求：不超过200KB）</small></label>
                                        <div class="form-group fileupload">
                                            <a href="javascript:void(0);" id="plupload-thumb-btn" class="btn btn-default plupload-select-files"><i class="ion-image"></i> 上传缩略图</a>
                                            <div class="imglist clearfix" data-thumb='{$OneProductNav.thumb}'></div>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label>分类描述</label>
                                        <div class="form-group">
                                            <textarea name="info" rows="3" class="form-control" placeholder="分类描述">{$OneProductNav.info}</textarea>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label class="info">是否推荐</label>
                                        <div class="form-group">
                                            <select class="select3 form-control" name="ishome" style="width:100%;">
                                                <option value="1" <eq name="OneProductNav.ishome" value="1">selected</eq>>是</option>
                                                <option value="0" <eq name="OneProductNav.ishome" value="0">selected</eq>>否</option>
                                            </select>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <label class="info">是否显示</label>
                                        <div class="form-group">
                                            <select class="select3 form-control" name="isshow" style="width:100%;">
                                                <option value="1" <eq name="OneProductNav.isshow" value="1">selected</eq>>是</option>
                                                <option value="0" <eq name="OneProductNav.isshow" value="0">selected</eq>>否</option>
                                            </select>
                                            <span class="errorLabel"></span>
                                        </div>
                                        <div class="box-footer">
                                            <div class="pull-right">
                                                <button type="submit" class="btn btn-primary" id="documentnav-edit-btn"><i class="fa fa-save"></i> 保存</button>
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
<!-- uploadify -->
<script type="text/javascript" src="__BASE__/plupload-2.3.1/js/plupload.full.min.js"></script>
<AdminLTE.2.1.2nLTE App -->
<script src="__BASE__/AdminLTE/dist/js/app.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.validate.min.js" type="text/javascript"></script>
<script src="__JS__/base/jquery.form.js" type="text/javascript"></script>
<script src="__JS__/base/jquery-confirm.min.js" type="text/javascript"></script>
<script type="text/javascript">
    var ThinkPHP = {
        'ROOT' : '__ROOT__',
        'MODULE' : '__MODULE__',
        'IMAGEURL' : '{:U("File/plupload")}',
        'IMG' : '__IMG__',
        'PLUPLOAD' : '__BASE__/plupload-2.3.1',
        'DOCUMENTNAV' : '{:U("DocumentNav/index")}'
    };
</script>
<script src="__JS__/base/base.js" type="text/javascript"></script>
<script src="__JS__/documentnav.js" type="text/javascript"></script>
</body>
</html>