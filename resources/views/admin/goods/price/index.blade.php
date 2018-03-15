@extends('admin.layoutList')
@section('css')
    <link href="/libs/iCheck/skins/square/blue.css" rel="stylesheet">
    <style>
        .col-extend-css {
            width: 100%;
            text-align: left;
        }
        #table img {
            padding:2px;
        }
        #pop label {
            font-weight: 500;
        }
    </style>
@endsection
@section('title')
    <li class="cur">
        <a href="#"><span>价格列表</span></a>
    </li>
@endsection
@section('search')
    <div class="form-group">
        <label for="goods_name">商品名称：</label>
        <input type="text" placeholder="请输入商品名称" style="width: 130px;" class="form-control" name="name" id="name">&nbsp;
    </div>
    <div class="form-group">
        <label for="product_code">商品编码：</label>
        <input type="text" placeholder="请输入商品编码" style="width: 130px;"  class="form-control" name="sku" id="sku">&nbsp;
    </div>
    <div class="form-group">
        <label for="goods_name">商品条码：</label>
        <input type="text" placeholder="请输入商品条码" style="width: 130px;"  class="form-control" name="upc" id="upc">&nbsp;
    </div>
    <div class="form-group category-list">
        <label >商品分类：</label>

    </div>
@endsection
@section('button')
    <div class="" style="margin-top: 14px;padding:10px;background-color: #eee;">
        <button class="btn btn-border-blue pull-erp batch btn-batch" disabled="disabled">批量拉取ERP价格</button>
        <button class="btn btn-border-blue sync-app batch btn-batch" disabled="disabled">批量同步线上平台</button>
    </div>
@endsection
@section('js')

    <script src="/libs/iCheck/icheck.js"></script>
    <script>
        var layui_table_ajax_url = '/admin/price/search';

        layui_table({
            sort_name : 'updated_at',
            sort_order : 'desc',
            cols: [[ //字段
                { type: 'checkbox',event : 'checkbox'},
                { title: '操作', field: 'operation', align: 'center' , width:230 },
                { title: '商品编码/条码', field: 'product_code', align: 'center' , width:190 },
                { title: '商品名称',  field: 'goods_info', align: 'left',width : 200 },
                { title: '商品分类', field: 'category', align: 'left', width:90 },
                { title: '线下ERP价格', field: 'erp_price', align: 'center', width:115 },
                { title: '售价', field: 'price', align: 'left', width:90 },
                { title: '更新于', field: 'updated_at', align: 'left',width : 170}
            ]]
        });

        var category = $.parseJSON( '{!! $category !!}' );
        var category_html = '';
        category_html += '<select class="form-control bigCategoryID" name="bigCategoryID" id="bigCategoryID"  data-wm="0">';
        category_html += '<option value="0">请选择</option>';
        if( category != ''){

            $.each(category , function( k ,v ){

                category_html += '<option value="'+ v.id +'">'+ v.name+'</option>';
            });
        }
        category_html += '</select>&nbsp;';
        category_html += '<select class="form-control midCategoryID" name="midCategoryID" id="midCategoryID" data-wm="0" style="display: none" >';
        category_html += '</select>&nbsp;';
        category_html += '<select class="form-control smallCategoryID" name="smallCategoryID" id="smallCategoryID" data-wm="0" style="display: none;">';
        category_html += '</select>&nbsp;';

        $('.category-list').append(category_html);

        $(function(){

            $(document).on('change','.bigCategoryID',function(){

                var _this = $(this);
                //隐藏三级分类
                _this.next().next().html('').hide();
                //隐藏二级
                _this.next().hide().html('');

                var bigCategoryID = _this.val();

                if( bigCategoryID != 0  ) {

                    $.each(category  ,function ( k ,v ){

                        if (v.id == bigCategoryID ){
                            if(v.children != ''){

                                var midHtml = '<option value="0">请选择</option>' ;
                                $.each(v.children ,function ( km, vm ){
                                    midHtml +='<option value="' + vm.id + '" >' + vm.name + '</option>' ;
                                });

                                _this.next().show().html(midHtml).css('display','inline-block');
                            }else{
                                //隐藏三级分类
                                _this.next().next().html('').hide();
                                //隐藏二级
                                _this.next().hide().html('');
                            }
                        }
                    })
                }
            }).on('click','.price',function(){   //修改价格

                var id = $(this).attr('data-id');

                var price = $(this).prev().text();
                var goods_name = $(this).attr('data-name');

                var htmlPrice = '';

                htmlPrice += '<div id="pop" style="margin-top:10px;" style="width: 100%">';
                htmlPrice += '<div style="background: #ffffff">';
                htmlPrice += '<form id="pop_form" onsubmit="return false;" class="form-horizontal" role="form">';
                htmlPrice += '<input type="hidden" value="'+ id +'" name="spec_id">';
                htmlPrice += '<div class="form-group" style="margin-right: 1px; margin-left: 28px;text-align: left">';
                htmlPrice += '<label class="col-sm-12 " for="price_edit" >';
                htmlPrice += '<span>'+ goods_name +'</span></label>';
                htmlPrice += '</div>';
                htmlPrice += '<div class="form-group" style="margin-right: 1px; margin-left: 1px;">';
                htmlPrice += '<label class="col-sm-3 control-label" for="price_edit">';
                htmlPrice += '价格：</label>';
                htmlPrice += '<div class="col-sm-8">';
                htmlPrice += '<input class="form-control"  style="width: 150px;" type="text" id="price_edit" name="price" maxlength="100" value="'+price+'" />';
                htmlPrice += '</div></div>';
                htmlPrice += '</form>';
                htmlPrice += '</div>';
                htmlPrice += '</div>';

                layer.open({
                    title:'修改价格',
                    type : 1,
                    area : ['40%','auto'],
                    move:false,
                    btnAlign:'c',
                    content : htmlPrice,
                    btn : ['确认' ,'取消'],
                    yes : function(index){

                        var dt = E.getFormValues('pop_form');
                        var error = '' ;

                        if( dt.price == ''){
                            error = '请输入商品价格' ;
                        }

                        if(!E.isMoney(dt.price)){
                            error = '请设置正确的商品价格' ;
                        }

                        if( error != '' ){
                            layer.msg( error ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                            return false;
                        }

                        layer.close(index);

                        E.ajax({
                            type : 'get',
                            url : '/admin/price/edit',
                            dataType : 'json',
                            data : dt ,
                            success : function(obj){

                                if( obj.code == 200 ){
                                    layer.msg('操作成功',{ icon : 1 ,shade: [0.15, 'black'], time : 2000 });
                                    layui_table_reload();
                                }else{
                                    layer.msg('操作失败',{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                                }
                            }
                        })
                    }
                });
            }).on('click','.reset',function(){

                $(this).prev().val(0);
            }).on('click','.infinite',function(){

                $(this).prev().prev().val(9999);
            }).on('click','.sync-app',function(){

                var  dt = { spec_ids : [] };
                if( $(this).hasClass('batch')){

                    var spec_num = 0;

                    $('.layui-table-body').find('.layui-form-checked').each(function(){

                        spec_num = 1;
                        var spec_id = $(this).parents('tr').find('.pull-erp').attr('data-id');
                        dt.spec_ids.push( spec_id );

                    });

                    if( !spec_num ){
                        layer.msg('请至少选择一个商品',{ icon : 2 , time : 2000 ,shade: [0.15, 'black'] });
                        return false;
                    }
                }else{

                    dt.spec_ids.push($(this).prev().attr('data-id'));
                }

                E.ajax({
                    type : 'get',
                    url :'/admin/price/sync',
                    dataType : 'json',
                    data : dt,
                    success :function ( obj ){

                        if( obj.code == 200 ){
                            layer.msg('同步成功',{ icon : 1 ,shade: [0.15, 'black'], time : 2000 });
                        }else{
                            layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                        }
                    }
                })
            }).on('click','.pull-erp',function(){

                var  dt = { skus : [] };

                if( $(this).hasClass('batch')){

                    var spec_num = 0;

                    $('.layui-table-body').find('.layui-form-checked').each(function(){

                        spec_num = 1 ;
                        var sku = $(this).parents('tr').find('.pull-erp').attr('data-sku');
                        dt.skus.push( sku );
                    });

                    if( !spec_num ){
                        layer.msg('请至少选择一个商品',{ icon : 2 , time : 2000 ,shade: [0.15, 'black'] });
                        return false;
                    }
                }else{

                    dt.skus.push($(this).attr('data-sku'));
                }

                E.ajax({
                    type : 'get',
                    url :'/admin/price/pull_erp',
                    dataType : 'json',
                    data : dt,
                    success :function ( obj ){

                        if( obj.code == 200 ){
                            layer.msg('拉取成功',{ icon : 1 ,shade: [0.15, 'black'], time : 2000 });
                        }else{
                            layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                        }
                    }
                })
            });

        });

        function addcheck(){

            $('.square-radio').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        }

    </script>
@endsection
