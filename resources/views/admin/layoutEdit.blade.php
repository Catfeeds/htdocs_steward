<!DOCTYPE html>
<html>
<head lang="en">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>商管云</title>
    <link href="/libs/bootstrap-3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <link href="/libs/bootstrap-select/dist/css/bootstrap-select.min.css" rel="stylesheet">
    <link href="/libs/layui-v2.2.5/css/layui.css" rel="stylesheet">
    <link href="/libs/iCheck/skins/square/blue.css" rel="stylesheet">
    <link href="/css/admin/common.css?v=20180124111111" rel="stylesheet">
    <style>
        body {background: #fbfbfb;}
        a,a:hover,a:focus,a:active {text-decoration: none;color: #0066ff;}
        .app-title {height: 48px; overflow: hidden;padding: 0 20px;}
        .app-title ul { float: left;-webkit-box-sizing: border-box;-moz-box-sizing: border-box;box-sizing: border-box;overflow: hidden;}
        .app-title ul li {float: left;margin-right: 20px;line-height: 48px;}
        .app-title ul li span {padding-bottom: 13px; font-size:18px;}
        .app-title ul li.cur span { color: #313131;}
        .app-title .right-btn {float: right;padding-top: 7px;}
        .app-content { margin: 15px;background:#ffffff;min-height: 500px;border: 1px solid #eee;border-radius: 4px;overflow: hidden;padding: 20px 15px;}
        .form-horizontal .form-group {margin-right: 0;margin-left: 0;}
        .layui-laypage .layui-laypage-curr .layui-laypage-em{
            background: #00a0e9;
        }
    </style>
    @yield('css')
</head>

<body>
<div class="app-title">
    <ul>
        @yield('title')
    </ul>
    <div class="right-btn">
        @yield('title-btn')
        @yield('go-back-btn')
    </div>
</div>

<div class="app-content">
    @yield('content')
</div>

</body>

<script src="/libs/jquery/jquery-1.9.1.min.js"></script>
<script src="/libs/layer-v3.0.3/layer.js"></script>
<script src="/libs/layui/layui.js"></script>
<script src="/libs/bootstrap-3.3.5/js/bootstrap.min.js"></script>
<script src="/libs/iCheck/icheck.js"></script>
<script src="/js/admin/global.js"></script>

<script>

    $(document).on('click', '.layer-go-back', function () {
        E.layerClose();
    });

</script>

@yield('js')

</html>