$(window).ready(function() {

    layui.use(['layedit','form'], function() {
        var form = layui.form();
        form.render('radio');
    });

    //订单接单
    $(document).on('click', '#receive-btn', function(e) {
        ajax_event('receive', {
            order_id: $(this).attr('data-val')
        }, '接单成功');
    });

    //订单配货
    $(document).on('click', '#packs-btn', function(e){
        ajax_event('packs', {
            order_id: $(this).attr('data-val')
        }, '配货成功');
    });

    //配货完成
    $(document).on('click', '#packs-finish-btn', function(e){
        ajax_event('complete_packs', {
            order_id: $(this).attr('data-val')
        }, '操作成功');
    });

    //订单完成
    $(document).on('click', '#finish-btn', function(e){
        ajax_event('complete', {
            order_id: $(this).attr('data-val')
        }, '操作成功');
    });

    //订单取消
    $(document).on('click', '#cancel-btn', function(e){

        var order_id = $(this).attr('data-val');
        layui.use(['layedit', 'form'], function() {
            var form = layui.form();
            form.render('radio', 'cancel-filter');
            form.on('radio(cancel-filter)', function(data) {
                if (data.value == 2007 || data.value == -1) {
                    $('#other-cancel-content').css('display','block');
                } else {
                    $('#other-cancel-content').css('display','none');
                }
            });
        });

        layer.open({
            title:'<p style="text-align:center;">取消原因</p>',
            type:1,
            area: ['430px','auto'],
            scrollbar:true,
            move:false,
            btn:['确定','取消'],
            btnAlign:'c',
            content: $('#cancel-layer'),
            yes:function(){

                var check = $('input[name="cancel-name"]:checked');
                var other_reason = $('#other-cancel-content').val();

                if ($.inArray(parseInt(check.val()), [-1, 2007]) >= 0 && other_reason == '') {
                    layer.alert('请填写自定义原因！', {icon: 2, offset: '70px'});
                    return false;
                }

                var reason = other_reason != '' ? other_reason : check.val();
                if(reason == undefined){
                    layer.alert('请选择取消原因！', {icon: 2, offset: '70px'});
                    return false;
                }

                ajax_event('cancel', {
                    order_id: order_id,
                    reason: reason,
                    reason_id: check.val()
                }, '取消成功');

            }
        });

    });

    //订单发货
    $(document).on('click', '#delivery-btn', function(e){

        layui.use(['layedit','form'], function(){
            var form = layui.form();
            form.render('radio', 'express-filter');
        });
        var order_id = $(this).attr('data-val');
        var is_zt = $(this).attr('data-zt');

        if (!is_zt) {

            ajax_event('delivery', {
                order_id: order_id
            }, '发货成功');

        } else {

            layer.open({
                title:'<p style="text-align:center;">选择配送平台</p>',
                type:1,
                area: ['360px','auto'],
                scrollbar:true,
                move:false,
                btn:['确定','取消'],
                btnAlign:'c',
                content: $('#express-layer'),
                yes:function() {
                    var express_id = $(' input[name="express"]:checked ').val();
                    if(E.empty(express_id)){
                        layer.alert('请选择配送平台！', {icon: 2, offset: '70px'});
                        return false;
                    }
                    ajax_event('delivery', {
                        order_id: order_id,
                        express_id: express_id
                    }, '发货成功');
                }
            });

        }

    });

    //拒绝退单/取消单申请
    $(document).on('click', '#disagree-refund-btn', function(e){
        layer.open({
            title:'<p style="text-align:center;">拒绝原因</p>',
            type:1,
            area: ['360px','auto'],
            scrollbar:true,
            shade: 0.6,
            move:false,
            btn:['确定','取消'],
            btnAlign: 'c',
            content: $('#refuse-layer'),
            yes:function(){
                var refuse_reason = $("#refuse-reason").val();
                if(refuse_reason == '') {
                    layer.alert('请填写拒绝原因！', {icon: 2, offset: '70px'});
                    return false;
                }
                ajax_event('disagree_refund', {
                    order_id: $('#disagree-refund-btn').attr('data-val'),
                    refuse_reason: refuse_reason
                }, '操作成功');

            }
        });
    });

    //同意退单/取消单申请
    $(document).on('click', '#agree-refund-btn', function(e){
        ajax_event('agree_refund', {
            order_id: $(this).attr('data-val')
        }, '操作成功');
    });

    //回复催单
    $(document).on('click', '#remind-reply-btn', function(e){

        var order_id = $(this).attr('data-val');

        layui.use(['layedit','form'], function() {
            var form = layui.form();
            form.render('radio', 'remind-filter');
            form.on('radio(remind-filter)', function(data){
                if (data.value == '其它原因') {
                    $('#other-reply-content').css('display','block');
                } else {
                    $('#other-reply-content').css('display','none');
                }
            });
        });

        layer.open({
            title:'<p style="text-align: center;">回复内容</p>',
            type:1,
            area: ['360px','auto'],
            scrollbar:true,
            move:false,
            btn:['确定','取消'],
            btnAlign:'c',
            content: $('#reply-layer'),
            yes:function(){

                var check = $('input[name="reply"]:checked');
                var other_reason = $('#other-reply-content').val();

                if (check.val() == '其它原因' && other_reason == '') {
                    layer.alert('请填写自定义内容！', {icon: 2, offset: '70px'});
                    return false;
                }

                var reason = other_reason != '' ? other_reason : check.val();
                if(reason == undefined){
                    layer.alert('请选择回复内容！', {icon: 2, offset: '70px'});
                    return false;
                }

                ajax_event('reply_remind', {
                    order_id: order_id,
                    reply_content: check.val()
                }, '回复成功');

            }
        });
    });

    //挂起
    $(document).on('click', '#hang-up-btn', function(e){
        ajax_event('hang_up', {
            order_id: $(this).attr('data-val')
        }, '挂起成功');
    });

    //取消挂起
    $(document).on('click', '#cancel-hang-up-btn', function(e){
        ajax_event('cancel_hang_up', {
            order_id: $(this).attr('data-val')
        }, '取消成功');
    });

    //打印
    $(document).on('click', '.print-btn' ,function (e) {

        var order_id = $(this).attr('data-val');
        var con = 0 ;
        var is_detail = $('.app-title li').hasClass('bill-detail') ? 1 : 0;
        var print_config = is_detail == 1
            ? parent.parent.client_print_config_get()
            : parent.client_print_config_get();

        $.each( print_config , function (k, v){
            if( v.connect == 1){
                con = 1;
            }
        });

        if( con == 0 ){
            is_detail == 1
                ? parent.parent.client_notice_audition(4)
                : parent.client_notice_audition(4);
            layer.open({
                title: '<p style="text-align:center;">提示</p>',
                content :'现在去设置打印机连接吗?',
                icon: 3,
                move: false,
                btn: ['确认', '取消'],
                btnAlign:'c',
                yes: function () {
                    if (is_detail) {
                        parent.location.href = '/lar/steward/mall/setting/1';
                    } else {
                        location.href = '/lar/steward/mall/setting/1';
                    }
                }
            });
            return false;
        }

        E.ajax({
            type:'get',
            url:'/lar/steward/order/order_fetch',
            data:{ order_id : order_id },
            dataType : 'json',
            success : function(o){
                if(o.code == 200){
                    if (is_detail){
                        parent.parent.client_print_auto_func(o.data);
                    }else{
                        parent.client_print_auto_func(o.data);
                    }
                }
            }
        });
    });

    $(document).on('click', '.order-detail', function(e){
        layer.open({
            title: false,
            type: 2,
            area: ['100%', '100%'],
            closeBtn: 0,
            content: '/admin/order/detail/' + $(this).attr('data-no')
        })
    });

});

/**
 * ajax事件函数
 * @param url string 方法名
 * @param data object 数据
 * @param msg string 提示语
 */
function ajax_event(url, data, msg) {

    if (!msg) {
        msg = '操作成功';
    }

    E.ajax({
        type: 'get',
        url: '/ajax/order/' + url,
        data: data,
        success: function(obj) {
            if (obj.code == 200) {

                layer.closeAll('page');
                layer.msg(msg, {icon:1, time:1000});
                if ($('.app-title li').hasClass('bill-detail')) {
                    location.reload();
                } else {
                    refresh();
                }

            } else {
                layer.alert(obj.message,{icon:2, time:1000});
            }
        }
    });

}


/**
 * 初始化加载列表数据
 * @param type int 类型
 * @param page int 分页数
 */
function load(type, page) {

    var dt = {};
    var url;

    if (type == 0) {
        dt = E.getFormValues('search-form');
        url = '/ajax/order/search';
    } else {
        dt.list_type = type;
        url = '/ajax/order/index';
    }
    dt.page = page;

    E.ajax({
        type: 'get',
        url: url,
        data: dt,
        success: function(obj) {

            var data = obj.data;

            var order_box_id = '#order-list';
            var position_page = '.position-page';

            $(order_box_id).empty();
            $(position_page).empty();

            if (data.extend) {
                $('#new_orders').html(data.extend.new_orders);
                $('#wait_deliver_orders').html(data.extend.wait_deliver_orders);
                $('#send_orders').html(data.extend.send_orders);
                $('#abnormal_orders').html(data.extend.abnormal_orders);
                $('#remind_orders').html(data.extend.remind_orders);
                $('#refund_orders').html(data.extend.refund_orders);
                $('#finish_orders').html(data.extend.finish_orders);
                $('#cancel_orders').html(data.extend.cancel_orders);
            }

            var html = '';
            var express = data.express;

            $.each(data.list, function(k, v) {
                html += '<li class="order-li hover">';
                html += '<div class="order-status">';
                html += '<div class="order-top-left">';
                if (v.app_logo) {
                    html += '<span><img src="'+ v.app_logo +'"/></span>';
                }
                html += '<span><img src="'+ v.send_logo +'"/></span>';
                html += '<p class="text"><em class="order-no" data="'+ v.status_name +'" type="'+type+'">#'+ v.day_sequence +'</em></p>';
                if (!E.empty(v.send_time)) {
                    var s_name = v.send_type == 2 ? '提货' : '送货';
                    html += '<p class="text">期望'+ s_name +'时间：<span class="order-time">'+ v.send_time +'</span></p>';
                }
                html += '</div>';
                html += '<div class="order-top-right active" id="status">'+ v.status_name +'</div>';
                html += '</div>';
                html += '<div class="order-info order-detail" data-no="'+ v.order_id +'">';
                if (!E.empty(v.remark)) {
                    html += '<p class="notice-mark">用户备注：'+ v.remark +'</p>';
                }
                html += '<div class="left-info">';
                html += '<p>';
                html += '<span class="name">'+ v.deliver_name +'</span>';
                html += '<span class="phone">'+ v.deliver_mobile +'</span>';
                if (!E.empty(v.order_number)) {
                    html += '<span class="order-num">第'+ v.order_number +'次下单</span>';
                }
                html += '</p>';
                html += '<p class="address">'+ v.deliver_address +'</p>';
                if (!E.empty(v.mall_id)) {
                    html += '<p>';
                    html += '<span>所属门店  </span>';
                    html += '<span>'+ v.mall_name +'</span>';
                    html += '</p>';
                }

                html += '</div>';

                html += '<div class="right-info">';
                html += '<div class="order-fee">';
                html += '<span class="shop-number">商品件数：'+ v.total_goods_number +'件</span>';
                html += '<span>商品总金额：￥'+ v.total_fee +'</span>&nbsp;&nbsp;';
                html += '<span>包装费：￥'+ v.package_fee +'</span>&nbsp;&nbsp;';
                html += '<span>配送费：￥'+ v.send_fee +'</span>';
                html += '</div>';
                html += '<div class="order-fee">';
                html += '<span>客户实付：<em class="fnw">￥'+ v.user_fee +'</em></span>&nbsp;&nbsp;';
                html += '<span>预计收入：<em class="fnc">￥'+ v.mall_fee +'</em></span>&nbsp;&nbsp;';
                html += '</div>';
                html += '<div class="order-fee">';
                html += '<span>商家活动优惠：￥'+ v.mall_act_fee +'</span>';
                html += '</div>';
                html += '<div class="order-fee">';
                html += '<span>平台活动优惠：￥'+ v.app_act_fee +'</span>';
                html += '</div>';

                html += '</div>';
                html += '</div>';



                //订单取消原因------------------------------------开始
                html += '<div id="cancel-layer" style="display: none; margin-left:20px;text-align:left; margin-bottom: 20px;">';
                html += '<div class="layui-form">';
                $.each(v.cancel_reason, function(i, j){
                    html += '<input type="radio" lay-filter="cancel-filter" title="'+ j.name +'" name="cancel-name" value="'+ j.id +'">';
                });
                html += '</div>';
                html += '<textarea id="other-cancel-content" style="width: 230px;height:85px;margin-left:30px;margin-top:10px;display: none;" placeholder="请填写自定义原因"></textarea>';
                html += '</div>';
                //订单取消原因------------------------------------结束

                //拒绝订单退单------------------------------------开始
                html += '<div id="refuse-layer" style="display: none;text-align:center; margin-bottom: 20px;">';
                html += '<textarea id="refuse-reason" style="width: 311px;height:100px;margin-top:10px;" placeholder="请输入拒绝原因"></textarea>';
                html += '</div>';
                //拒绝订单退单------------------------------------结束

                //订单发货-----------------------------------------开始
                html += '<div id="express-layer" style="display: none; margin-left:20px; text-align:left; margin-bottom: 20px;">';
                html += '<div class="layui-form" style="text-align:left;">';
                $.each(express, function(i, j) {
                    html += '<input type="radio" lay-filter="express-filter" title="'+ j.name +'" name="express" value="'+ j.id +'">';
                });
                html += '</div>';
                html += '</div>';
                //订单发货-----------------------------------------结束


                html += '<div class="detail-bot">';
                html += '<p class="notice left-info">';
                html += '<span>下单时间：'+ v.created_at +'</span>';
                if (!E.empty(v.accept_at)) {
                    html += '&nbsp;&nbsp;&nbsp;&nbsp;<span>接单时间：'+ v.accept_at +'</span>';
                }
                html += '<br/><span>单号：'+ v.app_order_id +'</span>';
                html += '</p>';
                if(v.apply == 3){
                    html += '<p class="notice">第：'+ v.remind_number +'次催单</p>';
                }

                html += '<div class="submit-btn">';
                if (v.hang_up == 0) {

                    //接单/取消
                    if (v.status == 0) {
                        html += '<button class="btn btn-border-blue" id="receive-btn" data-val="'+ v.order_id +'">接单</button>';
                        html += '<button class="btn btn-default" id="cancel-btn" data-val="'+ v.order_id +'">取消</button>';
                    }

                    if (v.apply == 1 || v.apply == 2) {
                        html += '<button type="button" class="btn btn-default" id="disagree-refund-btn" data-val="'+ v.order_id +'">拒绝</button>';
                        if (v.apply == 1) {
                            html += '<button type="button" class="btn btn-border-blue" id="agree-refund-btn" data-val="'+ v.order_id +'">同意取消</button>';
                        } else if (v.apply == 2) {
                            html += '<button type="button" class="btn btn-border-blue" id="agree-refund-btn" data-val="'+ v.order_id +'">同意退单</button>';
                        }
                    } else {

                        //处理催单
                        if ($.inArray(v.status, [1, 2, 3, 7, 8]) >= 0 && v.apply == 3) {
                            html += '<div id="reply-layer" style="display: none; margin-left:20px;text-align:left; margin-bottom: 20px;">';
                            html += '<div class="layui-form">';
                            $.each(v.remind_reply, function(i, j) {
                                html += '<input type="radio" lay-filter="remind-filter" title="'+ j +'" name="reply" value="'+ j +'">';
                            });
                            html += '</div>';
                            html += '<textarea id="other-reply-content" style="width: 230px;height:85px;margin-left:30px;margin-top:10px;display: none;" placeholder="请填写自定义原因"></textarea>';
                            html += '</div>';
                            html += '<button class="btn btn-border-blue" id="remind-reply-btn" data-val="'+ v.order_id +'">回复用户</button>';
                        }

                        //配货
                        if (v.status == 1) {
                            html += '<button class="btn btn-border-blue" id="packs-btn" data-val="'+ v.order_id +'">配货</button>';
                        }

                        //发货
                        if (v.status == 8) {
                            html += '<button class="btn btn-border-blue" id="delivery-btn" data-val="'+ v.order_id +'" data-send-type="'+ v.send_type +'">发货</button>';
                        }

                        //配货完成
                        if (v.status == 7) {
                            html += '<button class="btn btn-border-blue" id="packs-finish-btn" data-val="'+ v.order_id +'">配货完成</button>';
                        }

                        //完成
                        if ($.inArray(v.status, [2, 3]) >= 0) {
                            html += '<button class="btn btn-border-blue" id="finish-btn" data-val="'+ v.order_id +'">完成</button>';
                        }

                        //挂起
                        if ($.inArray(v.status, [0, 1, 2, 3, 7, 8]) >= 0) {
                            html += '<button type="button" class="btn btn-default" id="hang-up-btn" data-val="'+ v.order_id +'">挂起</button>';
                        }

                    }

                    //打印
                    if (obj.data.app_client != 0) {
                        html += '<button type="button" class="btn btn-default print-btn"  data-val="'+ v.order_id +'">打印</button>';
                    }

                } else {
                    html += '<button type="button" class="btn btn-border-blue" id="cancel-hang-up-btn" data-val="'+ v.order_id +'">取消异常</button>';
                }
                html += '</div>';

                html +=  '</div>';

                html += '</li>';

            });

            $(order_box_id).append(html);
            if (data.total > 0) {
                $('#total').html(data.total).parents('.top-jilu').show();
            } else {
                $('#total').html(0).parents('.top-jilu').hide();
            }
            if(data.link){
                $(position_page).html(data.link);
            }

        }
    });

}


//订单详情页返回按钮
function back() {
    var index = parent.layer.getFrameIndex(window.name);
    parent.refresh();
    parent.layer.close(index);
}
