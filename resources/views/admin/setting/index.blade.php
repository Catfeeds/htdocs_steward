@extends('admin.layoutEdit')

@section('css')
    <link rel="stylesheet" href="/css/admin/setting/index.css?v=2018012315">
    <link href="/libs/layui-v2.1.7/css/layui.css" rel="stylesheet">
    <link rel="stylesheet" href="/libs/jquery-ui/jquery-ui.min.css" media="screen">
    <style>
        .set_a {
            margin-left: 63px;
            color: blue;
            font-size: 15px;
            display: block;
            width: 73px;
            height: 25px;
            text-align: center;
            border: 1px solid #2f3ead;
        }
        .logo-img img {
            width: 80px;
            height: 80px;
        }
    </style>
@endsection

@section('title')
    <li class="cur"><span>基础设置</span></li>
@endsection

@section('content')

    <form action="#" id="basic-set" class="form-horizontal" role="form">
        <!--企业LOGO-->
        <div class="form-group">
            <div class="col-sm-12">
                <label class="col-sm-2 control-label"><span class="orange">*</span>企业LOGO</label>
                <div class="col-sm-10">
                    <div class="logo-img fl">
                        <img src="{{ $images_data['company_logo'] or '' }}" alt="" id="company_img">
                        <input type="hidden" id="company_logo" name="company" value="{{ $images_data['company_logo'] or '' }}">
                    </div>
                    <div class="add-img fl">
                        <button type="button" class="btn btn-border-blue" id="company" style="margin-left: 52px">上传图片</button>
                    </div>
                </div>
            </div>
        </div>

        <!--门店LOGO-->
        <div class="form-group">
            <div class="col-sm-12">
                <label class="col-sm-2 control-label"><span class="orange">*</span>商品默认图片</label>
                <div class="col-sm-10">
                    <div class="logo-img fl">
                        <img src="{{ $images_data['goods_logo'] or '' }}" alt="" id="goods_img">
                        <input type="hidden" id="goods_logo" name="goods" value="{{ $images_data['goods_logo'] or '' }}">
                    </div>
                    <div class="add-img fl">
                        <button type="button" class="btn btn-border-blue" id="goods" style="margin-left: 52px">上传图片</button>
                    </div>
                </div>
            </div>
        </div>

        <!--线上平台-->
        <div class="form-group pr0">
            <div class="col-sm-12">
                <label class="col-sm-2 control-label"><span class="orange">*</span>线上平台</label>
                <div class="col-sm-10 pr0">
                    <ul class="platf-list">

                        @foreach($app_data as $apps)
                            <li>
                                <div class="check-box">
                                    <input type="checkbox" @if($apps['enable'] == 1) checked @endif class="square-radio platform" name="{{ $apps['alias'] }}" value="{{ $apps['id'] }}">
                                </div>
                                <div class="platform-infor">
                                    <div class="platf col-sm-3">
                                        <span class="platf-logo" style="background-image: url({{ $apps['logo'] }})"></span>
                                        <p class="platf-name">{{ $apps['name'] }}</p>
                                    </div>
                                    <div class="information" @if($apps['enable'] == 0) style="display: none " @endif>
                                        <div class="col-sm-4">
                                            <label for="" class="fl control-label">账号:</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" placeholder="合作平台账号" name="{{ $apps['alias'] }}[]" value="{{ $apps['app_key'] }}">
                                            </div>
                                        </div>
                                        <div class="col-sm-4">
                                            <label for="" class="fl control-label">密码:</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control" placeholder="合作平台密码" name="{{ $apps['alias'] }}[]" value="{{ $apps['app_secret'] }}">
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </li>
                        @endforeach

                    </ul>
                </div>
            </div>
        </div>
    </form>

    <div class="btn-box">
        <button class="btn btn-blue save">确定</button>
    </div>

@endsection

@section('js')

    <script src="/js/admin/photo.js"></script>
    <script src="/libs/layui-v2.1.7/layui.js"></script>
    <script src="/libs/jquery-ui/jquery-ui.min.js?v=20160927"></script>
    <script>
        //icheck插件
        $('.square-radio').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });


        $(function () {

            var csrf = '{{ csrf_token() }}';

            layui.use('upload', function(){
                var upload = layui.upload;

                //执行实例
                var uploadInst = upload.render({
                    elem: '#company' //绑定元素
                    ,url: '/upload' //上传接口
                    ,data : { action : 'company/photo', _token : csrf }
                    ,done: function(res){
                        console.log(res.data.url);
                        if( res.code == 200 ){

                            $('#company_img').attr('src',res.data.url);
                            $('#company_logo').val(res.data.url)

                        }

                    }
                    ,error: function(){
                        //请求异常回调
                    }
                });

                layui.use('upload', function(){
                    var upload = layui.upload;

                    //执行实例
                    var uploadInst = upload.render({
                        elem: '#goods' //绑定元素
                        ,url: '/upload' //上传接口
                        ,data : { action : 'goods/photo', _token : csrf }
                        ,done: function(res){
                            if( res.code == 200 ){
                                console.log(res.data.url);
                                $('#goods_img').attr('src',res.data.url);
                                $('#goods_logo').val(res.data.url)
                            }

                        }
                        ,error: function(){
                            //请求异常回调
                        }
                    });
                });
            });
        });

        //数据保存
        $(document).on('click','.save',function () {
            var dt = E.getFormValues('basic-set');
            console.log(dt);
            E.ajax({
                type: 'get',
                url: '/admin/setting/save',
                dataType: 'json',
                data: dt,
                success: function (obj) {
                    console.log(obj);
                    if (obj.code == 200) {
                        window.location.reload();
                        layer.msg('设置成功', {icon: 1, time: 4000});
                    } else {
                        layer.msg('操作失败', {icon: 2, time: 1000});
                    }
                }
            });
        }).on('ifChecked', '.platform', function () {
            $(this).parent().parent().siblings('.platform-infor').find('.information').show();
        }).on('ifUnchecked', '.platform', function () {
            $(this).parent().parent().siblings('.platform-infor').find('.information').hide();
        });
    </script>
@endsection