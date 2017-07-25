<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>{$shutdowninfo.shutdowntitle}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <meta name="format-detection" content="telephone=no">
    <link href="__BASE__/ionicons-2.0.1/css/ionicons.min.css" rel="stylesheet">
    <link rel="icon" href="__ROOT__/favicon.ico" type="image/x-icon" >
    <style>
        .error {
            margin:0 auto;
            width:85%;
            text-align: center;
            margin-top:70px;
        }
        .error i {
            font-size:85px;
            color: rgba(236, 15, 34, 0.81);
        }
        .error p {
            padding:0 10px;
            font-size:17px;
            line-height:1.6;
            color: #333;
            text-align: left;
            text-indent: 32px;
        }
        .error .bottom img{
            position: absolute;
            bottom:20px;
            left:calc(50% - 50px);
        }
    </style>
</head>
<body>
<div class="error">
    <i class="ion-ios-cog"></i>
    <p>{$shutdowninfo.shutdowncontent}</p>
    <div class="bottom"><img src="__IMG__/logo-error.png" style="width:100px;opacity: 0.2;" alt=""></div>
</div>
</body>
</html>