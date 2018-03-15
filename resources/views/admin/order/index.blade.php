@extends('admin.layoutEdit')

@section('css')
    <link href="/css/admin/order/order.css?v=20180124000" rel="stylesheet">
    <style>
        .col-extend-css {
            width:100%;
            text-align: left;
            padding-left: 32px;
        }
        /*ie7、ie8兼容性*/
        .form-inline button{
            *vertical-align: top;
            *margin-left:5px;
        }
        .form-inline .form-group{
            display: inline;
            zoom:1;
        }
        .form-inline .form-group label{
            display: inline;
            zoom:1;
        }
        .form-inline .form-group input{
            width:auto;
            display: inline;
            zoom:1;
            _line-height:35px;
        }
        .form-control{
            *padding:0;
        }
        .layui-form-radio {display:block !important; }
        .layui-form-radio i{
            vertical-align: top !important;
            padding-top:3px !important;
            zoom:1;
        }
        .layui-form-radio span{
            width:240px !important;
        }
        .pagination li {
            _float: left;
            _padding:10px 6px;
            _border:1px solid #ccc;
        }
        .pagination li.active a{
            _color:#fff;
        }
        .ebsig-page-nav .pagination{
            float: right
        }
        .top-jilu{
            margin-top: 40px;
            padding-left: 10px;
        }

    </style>
@endsection
@section('title')
    <li><span>订单助手</span></li>
@endsection

@section('content')
    <div class="order-tile">
        <ul class="order-titl-list">
            <li><span data-type="1" class="{{$type == '1' ? 'cur':''}} pointer">待接单<em id="new_orders">0</em></span></li>
            <li><span data-type="2" class="{{$type == '2' ? 'cur':''}} pointer">待发货<em id="wait_deliver_orders">0</em></span></li>
            <li><span data-type="3" class="pointer">配送中<em id="send_orders">0</em></span></li>
            <li><span data-type="5" class="{{$type == '5' ? 'cur':''}} pointer">异常单<em id="abnormal_orders">0</em></span></li>
            <li><span data-type="6" class="{{$type == '6' ? 'cur':''}} pointer">催单<em id="remind_orders">0</em></span></li>
            <li><span data-type="7" class="{{$type == '7' ? 'cur':''}} pointer">退单<em id="refund_orders">0</em></span></li>
            <li><span data-type="8" class="pointer">已完成<em id="finish_orders">0</em></span></li>
            <li><span data-type="9" class="pointer">已取消<em id="cancel_orders">0</em></span></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="order-content">
                <ul class="order-list order-list-detail" id="order-list"></ul>
                <div class="top-jilu navbar fl">共<span class="word-color" id="total">0</span>条记录</div>
                <div class="position-page"></div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="/js/admin/order.event.js?v=20180124000000"></script>
    <script>

        $(function () {

            $(document).on('click', '.pagination a', function() {
                var index = $('.order-tile .cur').attr('data-type');
                load(index, $(this).attr('data-paging'));
            }).on('click','.order-tile span',function() {
                $('.order-tile span').removeClass('cur');
                $(this).addClass('cur');
                load($(this).attr('data-type'), 1);
            });

            refresh();

        });


        function refresh() {
            var index = $('.order-tile .cur').attr('data-type');
            load(index, 1);
        }

    </script>

@endsection
