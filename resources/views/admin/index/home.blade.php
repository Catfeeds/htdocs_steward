@extends('admin.layout')
@section('css')
    <link href="/libs/bootstrap-3.3.5/css/bootstrap.min.css" rel="stylesheet">
    <link  rel="stylesheet" href="/css/admin/index/home.css?v=2018011615">
    <style>
        a { text-decoration: none !important; color: #000; }
        a:hover{ color: #000000; }
    </style>
@endsection
@section('content')
    <div class="main">
        <!--紧急通知-->
        <!--<div class="main-notice">
            <a href="javascript:;"><span id="urgent_message">紧急通知：艺术家合作卡发布疯狂星期五，清洁用品2件5折！414乐视超级电视最高直降980家装节钜惠，3件8.8折！</span></a>
            <span class="icon-cancel"><img src="/images/admin/icon/icon-cancel.png"> </span>
        </div>-->

        <!--各数据统计-->
        <div class="main-info-box">
            <div style="padding: 0 15px;">
                <div class="info-choose">
                    <ul>
                        <li class="active day" data-id="1"><a href="javascript:;">今日</a></li>
                        <li class="yes_day" data-id="0"><a href="javascript:;" >昨日</a></li>
                        <li class="seven_day" data-id="0"><a href="javascript:;" >近7日</a></li>
                        <li class="thirty_day" data-id="0"><a href="javascript:;">近30日</a></li>
                    </ul>
                </div>
            </div>

            <div class="main-show">
                <div class="left-cont">
                    <ul class="shop-info-list">
                        <li>
                            <a href="javascript:;">
                                <div class="title-info">总营业额（元）</div>
                                <div class="today-number total_sales">0.00</div>
                                <div class="tom-number yes_total_sales"><div class="mom" style="display: inline-block">昨日</div>  <span>0.00</span>元<span style="padding-left: 4px;"></span></div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                                <div class="title-info">包装收入（元）</div>
                                <div class="today-number package_sales">0.00</div>
                                <div class="tom-number yes_package_sales"><div class="mom" style="display: inline-block">昨日</div>  <span>0.00</span>元<span style="padding-left: 4px;"></span></div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                                <div class="title-info">预计收入（元）</div>
                                <div class="today-number expect_sales">0.00</div>
                                <div class="tom-number yes_expect_sales"><div class="mom" style="display: inline-block">昨日</div>  <span>0.00</span>元<span style="padding-left: 4px;"></span></div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                                <div class="title-info">有效订单数</div>
                                <div class="today-number valid_orders">0.00</div>
                                <div class="tom-number yes_valid_orders"><div class="mom" style="display: inline-block">昨日</div>  <span>0.00</span>单<span style="padding-left: 4px;"></span></div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                                <div class="title-info">总订单数</div>
                                <div class="today-number total_orders">0.00</div>
                                <div class="tom-number yes_total_orders"><div class="mom" style="display: inline-block">昨日</div>  <span>0.00</span>单<span style="padding-left: 4px;"></span></div>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                                <div class="title-info">客单价</div>
                                <div class="today-number avg_price">0.00</div>
                                <div class="tom-number yes_avg_price"><div class="mom" style="display: inline-block">昨日</div>  <span>0.00</span>元<span style="padding-left: 4px;"></span></div>
                            </a>
                        </li>
                    </ul>
                    <div class="count-box">
                        <ul>
                            <li class="count-1">
                                <img src="/images/admin/index/total-1.png" alt="">
                                <div class="count">
                                    <p>总商品数</p>
                                    <h4>0</h4>
                                </div>
                            </li>
                            <li class="count-2">
                                <img src="/images/admin/index/total-2.png" alt="">
                                <div class="count">
                                    <p>上架商品数</p>
                                    <h4>0</h4>
                                </div>
                            </li>
                            <li class="count-3">
                                <img src="/images/admin/index/total-3.png" alt="">
                                <div class="count">
                                    <p>动销商品数</p>
                                    <h4>0</h4>
                                </div>
                            </li>
                            <li class="count-4">
                                <img src="/images/admin/index/total-4.png" alt="">
                                <div class="count">
                                    <p>总门店数</p>
                                    <h4>0</h4>
                                </div>
                            </li>
                            <li class="count-5">
                                <img src="/images/admin/index/total-5.png" alt="">
                                <div class="count">
                                    <p>上架门店数</p>
                                    <h4>0</h4>
                                </div>
                            </li>
                            <li class="count-6">
                                <img src="/images/admin/index/total-6.png" alt="">
                                <div class="count">
                                    <p>动销门店数</p>
                                    <h4>0</h4>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="right-cont">
                    <div class="change-type">
                        <span class="cur-type sale">销售额</span>
                        <span class="bill">订单数</span>
                    </div>
                    <div id="pro" class="proportion" style="height: 300px;">

                    </div>
                </div>
            </div>

        </div>

        <!--门店排名\效率排行-->
        <div class="chart-z-list chart-list">
            <div class="chart-z chart-list-1">
                <div class="chart-title">
                    <span><img src="/images/admin/index/chart-1.png" alt=""></span>
                    <span class="chart-info">门店营收排名</span>
                </div>
                <div class="chart-table mall_table">
                    <table>
                        <thead>
                        <tr>
                            <td>排行</td>
                            <td class="td-name">门店</td>
                            <td>总营业额</td>
                            <td>包装收入</td>
                            <td>预计收入</td>
                            <td>有效订单数</td>
                            <td>总订单</td>
                            <td>数客单价</td>
                        </tr>
                        </thead>
                        <tbody id="mall-revenue">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="chart-z chart-list-2">
                <div class="chart-title">
                    <span><img src="/images/admin/index/chart-2.png" alt=""></span>
                    <span class="chart-info">订单效率排行</span>
                </div>
                <div class="chart-table bill_table">
                    <table>
                        <thead>
                        <tr>
                            <td>排行</td>
                            <td class="td-name">门店</td>
                            <td>效率</td>
                        </tr>
                        </thead>
                        <tbody id="order-efficiency">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--热销排行-->
        <div class="chart-z-list chart-list">
            <div class="chart-z chart-list-3">
                <div class="chart-title">
                    <span><img src="/images/admin/index/hot-1.png" alt=""></span>
                    <span class="chart-info">热销商品排名</span>
                </div>
                <div class="chart-table selling_table">
                    <table>
                        <thead>
                        <tr>
                            <td>排行</td>
                            <td class="td-name">商品名称</td>
                            <td>总金额</td>
                            <td>总数量</td>
                            <td>均单价</td>
                        </tr>
                        </thead>
                        <tbody id="selling-goods">

                        </tbody>
                    </table>
                </div>
            </div>
            <div class="chart-z chart-list-4">
                <div class="chart-title">
                    <span><img src="/images/admin/index/hot-2.png" alt=""></span>
                    <span class="chart-info">热销分类排名</span>
                </div>
                <div class="chart-table category_table">
                    <table>
                        <thead>
                        <tr>
                            <td>排行</td>
                            <td class="td-name">分类名称</td>
                            <td>总金额</td>
                            <td>总数量</td>
                            <td>均单价</td>
                        </tr>
                        </thead>
                        <tbody id="selling-mall">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!--新手上路\产品动态-->
        <div class="chart-z-list">
            <div class="chart-z">
                <div class="chart-title border-b">
                    <span class="chart-info">新手上路</span>
                    <div class="sel-day-btn">
                        <a href="/admin/cms/article?category_id=35"  class="more">更多</a>
                    </div>
                </div>
                <div class="chart-box">
                    <ul class="news-list guide-list">

                    </ul>
                </div>
            </div>
            <div class="chart-z">
                <div class="chart-title border-b">
                    <span class="chart-info">产品动态</span>
                    <div class="sel-day-btn">
                        <a href="/admin/cms/article?category_id=36" class="more">更多</a>
                    </div>
                </div>
                <div class="chart-box">
                    <ul class="news-list movement-list">

                    </ul>
                </div>
            </div>
        </div>
    </div>

@endsection
<script src="/libs/echarts/echarts.js"> </script>
@section('js')
    <script>

        $(function () {

            index.index(0,1);

            index.channel(0,1,1);

            index.mall_revenue(0,1);

            index.order_efficiency(0,1);

            index.selling_goods(0,1);

            index.selling_category(0,1);

            E.ajax({
                type : 'get',
                url : '/admin/cms/article/search',
                data : { category_id : 35 , page : "0" ,page_size: 5 },
                dataType :'json',
                success :function (obj) {
                    var html = '';
                    if ( obj.code == 200 ){

                        $.each( obj.data.data ,function( k , v ){
                            if( k > 4 ){
                                return false;
                            }
                            html += '<li><a href="http://www.ebsig.com/steward/article/detail/'+ v.article_id+'">'+ v.article_title+'</a><span>'+  v.createTime+'</span></li>';
                        } );

                        $('.guide-list').append(html).parent().parent().show();
                    }
                }
            });

            E.ajax({
                type : 'get',
                url : '/admin/cms/article/search',
                data : { category_id : 36 ,page : "0" ,page_size : 5},
                dataType :'json',
                success :function (obj) {
                    var html = '';
                    if ( obj.code == 200 ){

                        $.each( obj.data.data ,function( k , v ){
                            if( k > 4 ){
                                return false;
                            }
                            html += '<li><a href="http://www.ebsig.com/steward/article/detail/'+ v.article_id+'">'+ v.article_title+'</a><span>'+ v.createTime+'</span></li>';
                        } );

                        $('.movement-list').append(html).parent().parent().show();
                    }
                }
            });

        });

        $(document).on('click','.yes_day',function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).attr('data-id',2);
            $(this).siblings().attr('data-id',0);
            $('.mom').text('前日');
            index.selection(0,2);
            $('.sale').addClass('cur-type');
            $('.bill').removeClass('cur-type');
            index.channel(0,2,1);
        }).on('click','.day',function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).attr('data-id',2);
            $(this).siblings().attr('data-id',0);
            $('.mom').text('昨日');
            index.selection(0,1);
            $('.sale').addClass('cur-type');
            $('.bill').removeClass('cur-type');
            index.channel(0,1,1);
        }).on('click','.seven_day',function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).attr('data-id',3);
            $(this).siblings().attr('data-id',0);
            $('.mom').text('前7日');
            index.selection(0,3);
            $('.sale').addClass('cur-type');
            $('.bill').removeClass('cur-type');
            index.channel(0,3,1);
        }).on('click','.thirty_day',function(){
            $(this).addClass('active');
            $(this).siblings().removeClass('active');
            $(this).attr('data-id',4);
            $(this).siblings().attr('data-id',0);
            $('.mom').text('前30日');
            index.selection(0,4);
            $('.sale').addClass('cur-type');
            $('.bill').removeClass('cur-type');
            index.channel(0,4,1);
        }).on('click','.sale',function(){
            $(this).addClass('cur-type');
            $('.bill').removeClass('cur-type');
            var day_id = 0;
            $('.info-choose ul li').each(function(k,v){
                if ($(this).attr('data-id') != 0) {
                    day_id = $(this).attr('data-id');
                }
            });
            index.channel(0,day_id,1);
        }).on('click','.bill',function(){
            $(this).addClass('cur-type');
            $('.sale').removeClass('cur-type');
            var day_id = 0;
            $('.info-choose ul li').each(function(k,v){
                if ($(this).attr('data-id') != 0) {
                    day_id = $(this).attr('data-id');
                }
            });
            index.channel(0,day_id,2);
        });

        var index = {

            app: [],

            app_data: {},

            selection:function(mall_id,date_type) {

                index.index(mall_id,date_type);

                index.mall_revenue(mall_id,date_type);

                index.order_efficiency(mall_id,date_type);

                index.selling_goods(mall_id,date_type);

                index.selling_category(mall_id,date_type);
            },

            //销售概况
            index:function(mall_id,data_type){

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/sales_profile',
                    dataType : 'json' ,
                    data : {
                        'mall_id':mall_id,
                        'date_type':data_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            $('.total_sales').html(obj.data.total_sales.current);
                            $('.yes_total_sales').find('span').eq(0).html(obj.data.total_sales.mom);
                            if (obj.data.total_sales == 1) {
                                $('.yes_total_sales').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }else if(obj.data.total_sales == 2){
                                $('.yes_total_sales').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }
                            $('.package_sales').html(obj.data.package_sales.current);
                            $('.yes_package_sales').find('span').eq(0).html(obj.data.package_sales.mom);
                            if (obj.data.package_sales == 1) {
                                $('.yes_package_sales').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }else if(obj.data.package_sales == 2){
                                $('.yes_package_sales').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }
                            $('.expect_sales').html(obj.data.expect_sales.current);
                            $('.yes_expect_sales').find('span').eq(0).html(obj.data.expect_sales.mom);
                            if (obj.data.expect_sales == 1) {
                                $('.yes_expect_sales').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }else if(obj.data.expect_sales == 2){
                                $('.yes_expect_sales').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }
                            $('.valid_orders').html(obj.data.valid_orders.current);
                            $('.yes_valid_orders').find('span').eq(0).html(obj.data.valid_orders.mom);
                            if (obj.data.valid_orders == 1) {
                                $('.yes_valid_orders').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }else if(obj.data.valid_orders == 2){
                                $('.yes_valid_orders').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }
                            $('.total_orders').html(obj.data.total_orders.current);
                            $('.yes_total_orders').find('span').eq(0).html(obj.data.total_orders.mom);
                            if (obj.data.total_orders == 1) {
                                $('.yes_total_orders').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }else if(obj.data.total_orders == 2){
                                $('.yes_total_orders').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }
                            $('.avg_price').html(obj.data.avg_price.current);
                            $('.yes_avg_price').find('span').eq(0).html(obj.data.avg_price.mom);
                            if (obj.data.avg_price == 1) {
                                $('.yes_avg_price').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }else if(obj.data.avg_price == 2){
                                $('.yes_avg_price').find('span').eq(1).addClass('glyphicon glyphicon-arrow-up');
                            }
                        }
                    }
                });

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/goods_mall_act_sales',
                    dataType : 'json' ,
                    data : {
                        'mall_id':mall_id,
                        'date_type':data_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            var num = 0;
                            $.each(obj.data,function(k,v){
                                num ++;
                                $('.count-'+num).find('h4').html(v);
                            });
                        }
                    }
                });
            },

            channel:function(mallID,date_type,type) {

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/app_orders_sales',
                    dataType : 'json' ,
                    data : {
                        mall_id:mallID,
                        date_type:date_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            if (type == 1) {
                                $.each(obj.data.sales,function(k,v){
                                    index.app.push(v.name);
                                });
                                index.app_data = JSON.stringify(obj.data.sales);
                                index.my_chart(index.app,index.app_data);
                            }else{
                                $.each(obj.data.orders,function(k,v){
                                    index.app.push(v.name);
                                });
                                index.app_data = JSON.stringify(obj.data.orders);
                                index.my_chart(index.app,index.app_data);
                            }
                        }
                    }
                });
            },

            my_chart:function(app,app_data){

                app_data = JSON.parse(app_data);

                //渠道占比饼图
                var piechart = echarts.init(document.getElementById('pro'));
                option = {
                    title : {
                        text: '',
                        subtext: '',
                        x:'center'
                    },
                    tooltip : {
                        trigger: 'item',
                        formatter: "{a} <br/>{b} : {c} ({d}%)"
                    },
                    legend: {
                        orient: 'vertical',
                        left: 'left',
                        data: app
                    },
                    color:['rgba(249, 207, 140, 1)', 'rgba(140, 183, 0, 1)','rgba(253, 96, 0, 1)','rgba(73, 132, 177, 1)','red'],
                    series : [
                        {
                            name: '',
                            type: 'pie',
                            radius : '60%',
                            center: ['50%', '67%'],
                            label: {
                                normal: {
                                    show: false
                                }
                            },
                            data:app_data,
                            itemStyle: {
                                emphasis: {
                                    shadowBlur: 10,
                                    shadowOffsetX: 0,
                                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                                }
                            }
                        }
                    ]
                };
                piechart.setOption(option);
                index.app.length = 0;
                index.app_data.length = 0;
            },

            //门店营收排名
            mall_revenue:function(mall_id,date_type){

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/mall_revenue_rank',
                    dataType : 'json' ,
                    data : {
                        mall_id:mall_id,
                        date_type:date_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            if ($.isEmptyObject(obj.data)) {
                                $('#mall-revenue').html('<div style="position: relative;left:357px;">暂无数据</div>');
                            }else{
                                var html = '';
                                $.each(obj.data,function(k,v){
                                    html += '<tr>';
                                    if (k == 0) {
                                        html += '<td><span class="first">1</span></td>';
                                    }else if(k == 1){
                                        html += '<td><span class="second">2</span></td>';
                                    }else if(k == 2) {
                                        html += '<td><span class="third">3</span></td>';
                                    }else{
                                        html += '<td><span>'+Number(k+1)+'</span></td>';
                                    }
                                    html += '<td class="td-name"><p>'+v.mall_name+'</p></td>';
                                    html += '<td>'+v.total_sales+'</td>';
                                    html += '<td>'+v.package_sales+'</td>';
                                    html += '<td>'+v.expect_sales+'</td>';
                                    html += '<td>'+v.valid_orders+'</td>';
                                    html += '<td>'+v.total_orders+'</td>';
                                    html += '<td>'+v.avg_price+'</td>';
                                    html += '</tr>';
                                });
                                $('#mall-revenue').html(html);
                            }
                        }
                    }
                });
            },

            //订单效率排名
            order_efficiency:function(mall_id,date_type){

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/mall_order_efficiency_rank',
                    dataType : 'json' ,
                    data : {
                        mall_id:mall_id,
                        date_type:date_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            if ($.isEmptyObject(obj.data)) {
                                $('#order-efficiency').html('<div style="position: relative;left:125px;">暂无数据</div>');
                            }else{
                                var html = '';
                                $.each(obj.data,function(k,v){
                                    html += '<tr>';
                                    if (k == 0) {
                                        html += '<td><span class="first">1</span></td>';
                                    }else if(k == 1){
                                        html += '<td><span class="second">2</span></td>';
                                    }else if(k == 2) {
                                        html += '<td><span class="third">3</span></td>';
                                    }else{
                                        html += '<td><span>'+Number(k+1)+'</span></td>';
                                    }
                                    html += '<td class="td-name"><p>'+v.mall_name+'</p></td>';
                                    html += '<td>'+v.efficiency+'</td>';
                                    html += '</tr>';
                                });
                                $('#order-efficiency').html(html);
                            }
                        }
                    }
                });
            },

            //热销商品
            selling_goods:function(mall_id,date_type){

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/hot_sell_goods_rank',
                    dataType : 'json' ,
                    data : {
                        mall_id:mall_id,
                        date_type:date_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            if ($.isEmptyObject(obj.data)) {
                                $('#selling-goods').html('<div style="position: relative;left:227px;">暂无数据</div>');
                            }else{
                                var html = '';
                                $.each(obj.data,function(k,v){
                                    html += '<tr>';
                                    if (k == 0) {
                                        html += '<td><span class="first">1</span></td>';
                                    }else if(k == 1){
                                        html += '<td><span class="second">2</span></td>';
                                    }else if(k == 2) {
                                        html += '<td><span class="third">3</span></td>';
                                    }else{
                                        html += '<td><span>'+Number(k+1)+'</span></td>';
                                    }
                                    html += '<td class="td-name"><p>'+v.goods_name+'</p></td>';
                                    html += '<td>'+v.total_money+'</td>';
                                    html += '<td>'+v.total_number+'</td>';
                                    html += '<td>'+v.avg_price+'</td>';
                                    html += '</tr>';
                                });
                                $('#selling-goods').html(html);
                            }
                        }
                    }
                });
            },

            //热销分类排行
            selling_category:function(mall_id,date_type){

                E.ajax({
                    type : 'get',
                    url : '/ajax/index/hot_sale_category_rank',
                    dataType : 'json' ,
                    data : {
                        mall_id:mall_id,
                        date_type:date_type
                    } ,
                    success : function (obj){
                        if (obj.code == 200) {
                            if ($.isEmptyObject(obj.data)) {
                                $('#selling-mall').html('<div style="position: relative;left:234px;">暂无数据</div>');
                            }else{
                                var html = '';
                                $.each(obj.data,function(k,v){
                                    html += '<tr>';
                                    if (k == 0) {
                                        html += '<td><span class="first">1</span></td>';
                                    }else if(k == 1){
                                        html += '<td><span class="second">2</span></td>';
                                    }else if(k == 2) {
                                        html += '<td><span class="third">3</span></td>';
                                    }else{
                                        html += '<td><span>'+Number(k+1)+'</span></td>';
                                    }
                                    html += '<td class="td-name"><p>'+v.category_name+'</p></td>';
                                    html += '<td>'+v.total_money+'</td>';
                                    html += '<td>'+v.total_number+'</td>';
                                    html += '<td>'+v.avg_price+'</td>';
                                    html += '</tr>';
                                });
                                $('#selling-mall').html(html);
                            }
                        }
                    }
                });
            }
        }

    </script>

@endsection
