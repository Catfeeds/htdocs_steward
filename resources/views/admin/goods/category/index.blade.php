@extends('admin.layoutEdit')

@section('css')
    <link href="/css/admin/category/category.css" rel="stylesheet">
    <style>
        .category-list {
            width:75% ;
        }
        .category-list .title span {
            font-weight: 700;
        }
        .edit-list li {
            cursor: move;
        }
    </style>
@endsection

@section('title')
    <li><span>商品分类</span></li>
@endsection

@section('title-btn')
    <button class="btn btn-border-blue sync-category">同步分类至平台</button>
@endsection

@section('content')

    <div class="manager-content">
        <button class="btn btn-blue add-category" data-level="1">添加一级分类</button>
        &nbsp;&nbsp;&nbsp;&nbsp;<span>可以拖拽分类调整排序</span>
        <div class="manage-body ">
            <div calss="category-list" style="float: left;width:100%;">
                <div class="title" style="background-color: rgba(229, 229, 229, 0.20)">
                    <span>操作</span>
                    <span>分类编号</span>
                    <span>分类名称</span>
                    <span>商品数量</span>
                    <span>状态</span>
                </div>
                <div class="manage-cont" id="management">
                    <ul class="edit-list" id="edit-list"></ul>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="/libs/sortable/Sortable.js"></script>
    <script>

        E.ajax({
            type : 'get' ,
            url : 'category/search',
            dataType : 'json',
            data : {} ,
            success :function (obj){

                if( obj.code == 200 ){

                    if( obj.data != ''){

                        var html = '';
                        var edit_list_id = $('#edit-list');

                        var big_category_ids = [];

                        $.each(obj.data , function ( k ,v ){

                            html +='<li class="big-category-list" data-sort="'+ v.sort +'" data-cid="'+ v.id+'" style="border-bottom: 1px solid #ccc;">';
                            html +=' <div class="over">';
                            if(v.children == '' ){
                                html +='<div class="icon-place prevent"></div>';
                            }else{
                                html +='<div class="icon-place prevent shadeholder"></div>';
                            }
                            html += '<span style="line-height: 33px;;">';
                            html += '<a href="#" data-id="'+ v.id +'" class="edit-category">编辑</a>&nbsp;&nbsp;&nbsp;';
                            html += '<a href="#" class="add-category" data-level="2" data-id="'+ v.id +'">新增二级分类</a>';
                            html += '</span>';

                            html += '<div class="icon">';
                            if(v.status == 1 ){
                                html += '<a href="#" class="change-status" data-status="0" data-id="'+ v.id +'">已启用</a>';
                            }else{
                                html += '<a href="#" class="change-status" data-status="1" data-id="'+ v.id +'">已禁用</a>';
                            }
                            html += '</div>';

                            html += '<div class="icon">';
                            html += '<span>'+ v.goods_num + '</span>';
                            html += '</div>';

                            html += '<div class="icon">';
                            html += '<span>'+ v.name + '</span>';
                            html += '</div>';

                            html += '<div class="icon">';
                            html += '<span>'+ v.id + '</span>';
                            html += '</div>';


                            html +='</div>';
                            html +='<ul class="submitnav" id="sort_category_' + v.id +'">';

                            if(v.children != ''){

                                big_category_ids.push(v.id);

                                $.each(v.children ,function( km , vm ){

                                    html += '<li class="mid-category-list"  data-sort="'+ vm.sort +'">';
                                    html += '<div class="over">';

                                    if(vm.children == ''){
                                        html += '<div class="icon-place prevent "></div>';
                                    }else{
                                        html += '<div class="icon-place prevent shadeholder"></div>';
                                    }

                                    html += '<span style="line-height: 33px;;">';
                                    html += '<a href="#" data-id="'+ vm.id +'" class="edit-category">编辑</a>';
                                    html += '</span>';


                                    html += '<div class="icon">';
                                    if(vm.status == 1 ){
                                        html += '<a href="#" class="change-status"  data-status="0" data-id="'+ vm.id +'">已启用</a>';
                                    }else{
                                        html += '<a href="#" class="change-status"  data-status="1" data-id="'+ vm.id +'">已禁用</a>';
                                    }
                                    html += '</div>';

                                    html += '<div class="icon">';
                                    html += '<span>'+ vm.goods_num + '</span>';
                                    html += '</div>';

                                    html += '<div class="icon">';
                                    html += '<span>'+ vm.name + '</span>';
                                    html += '</div>';

                                    html += '<div class="icon">';
                                    html += '<span>'+ vm.id + '</span>';
                                    html += '</div>';

                                    html += '<ul class="thirdnav">';

                                    if(vm.children != ''){
                                        $.each(vm.children ,function( ks , vs ){

                                            html += '<li class="mid-category-list"  data-sort="'+ vs.sort +'">';
                                            html += '<div class="over">';

                                            if(vs.children == ''){
                                                html += '<div class="icon-place prevent "></div>';
                                            }else{
                                                html += '<div class="icon-place prevent shadeholder"></div>';
                                            }

                                            html += '<span style="line-height: 33px;;">';
                                            html += '<a href="#" class="edit-category">编辑</a>';
                                            html += '</span>';


                                            html += '<div class="icon">';
                                            if(vs.status == 1 ){
                                                html += '<a href="#" class="change-status"  data-status="0" data-id="'+ vs.id +'">已启用</a>';
                                            }else{
                                                html += '<a href="#" class="change-status"  data-status="1" data-id="'+ vs.id +'">已禁用</a>';
                                            }
                                            html += '</div>';

                                            html += '<div class="icon">';
                                            html += '<span>'+ vs.goods_num + '</span>';
                                            html += '</div>';

                                            html += '<div class="icon">';
                                            html += '<span>'+ vs.name + '</span>';
                                            html += '</div>';

                                            html += '<div class="icon">';
                                            html += '<span>'+ vs.id + '</span>';
                                            html += '</div>';

                                            html += '</li>';
                                        });
                                    }

                                    html += '</ul>';
                                    html += '</li>';
                                });
                            }
                            html +='</ul>';
                            html +='</li>';

                        });

                        edit_list_id.append(html);

                        Sortable.create(edit_list_id[0], {
                            group: {
                                name:"words",
                                pull: false,
                                put: false
                            },
                            handle: 'li',
                            animation: 150,
                            onUpdate:function() {

                                var dt = {
                                    category_ids : []
                                };

                                $('.big-category-list').each(function(){

                                    var category_id = $(this).attr('data-cid');
                                    dt.category_ids.push(category_id);
                                });
                                dt.level = 1 ;
                                E.ajax({
                                    type : 'get',
                                    url :'/admin/category/sort',
                                    dataType : 'json',
                                    data : dt,
                                    success : function( obj){
                                        if( obj.code == 200 ){
                                            layer.msg('排序设置成功',{ icon : 1 ,shade: [0.15, 'black'], time : 1000 });
                                        }else{
                                            layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                                        }
                                    }
                                })
                            }
                        });

                        $.each(big_category_ids ,function ( k ,v ){

                            Sortable.create(document.getElementById('sort_category_'+ v ), {
                                group: 'words',
                                handle: 'li',
                                animation: 150,
                                onUpdate:function() {

                                    var dt = {
                                        category_ids : []
                                    };

                                    $('#sort_category_'+ v + ' li').each(function(){

                                        var category_id = $(this).find('.change-status').attr('data-id');
                                        dt.category_ids.push(category_id);
                                    });
                                    dt.level = 2 ;

                                    E.ajax({
                                        type : 'get',
                                        url :'/admin/category/sort',
                                        dataType : 'json',
                                        data : dt,
                                        success : function( obj){
                                            if( obj.code == 200 ){
                                                layer.msg('排序设置成功',{ icon : 1 ,shade: [0.15, 'black'], time : 1000 });
                                            }else{
                                                layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                                            }
                                        }
                                    })
                                }
                            });
                        });

                    }
                }
            }
        });

        $(function() {
            $(document).on("click", '.over .icon-place', function() {
                var len = $(this).parent().next().find('li').length ;
                if( len != 0 ){

                    $(this).parent().next().slideToggle(150);
                    $(this).toggleClass('shadeholder');
                }
            }).on('click','.add-category',function(){

                var level = $(this).attr('data-level');
                var sort = 0;
                var p_id = 0 ;     //父类id

                if( level == 1 ){

                    var num = $('.big-category-list').length;
                    if( num != 0 ){
                        sort = $('.big-category-list:last').attr('data-sort');
                    }
                }else if(level == 2){

                    p_id = $(this).attr('data-id');
                    var num = $(this).parent().parent().next().find('.mid-category-list').length;

                    if( num != 0){
                        sort = $(this).parent().parent().next().find('.mid-category-list:last').attr('data-sort') ;
                    }

                    var p_name = $(this).parent().next().next().next().find('span').text();
                }else{

                }

                var addcategoryHtml = '';

                addcategoryHtml += '<div id="pop" style="margin-top:10px;" style="width: 100%">';
                addcategoryHtml += '<div style="background: #ffffff">';
                addcategoryHtml += '<form action="" id="pop_form" class="form-horizontal" role="form" style="margin-top: 30px;">';
                addcategoryHtml += '<input type="hidden" name="p_id" value="'+ p_id +'">';
                addcategoryHtml += '<input type="hidden" name="sort" value="'+ sort +'">';
                addcategoryHtml += '<div class="form-group">';
                addcategoryHtml += '<label class="col-sm-4 control-label" for="stock_edit" style="text-align: right;line-height: 21px;;">';
                addcategoryHtml += '<span style="color:red">* </span>分类名称：</label>';
                addcategoryHtml += '<div class="col-sm-8">';
                addcategoryHtml += '<input type="text" name="category_name" class="form-control" style="width:160px;">';
                addcategoryHtml += '</div>';
                addcategoryHtml += '</div>';

                if( level == 2 ){

                    addcategoryHtml += '<div class="form-group">';
                    addcategoryHtml += '<label class="col-sm-4 control-label" for="" style="text-align: right;line-height: 21px;;">';
                    addcategoryHtml += '<span style="color:red">* </span>上级分类：</label>';
                    addcategoryHtml += '<div class="col-sm-8">';
                    addcategoryHtml += '<select class="form-control" style="width:160px;" disabled>';
                    addcategoryHtml += '<option value="'+ p_id +'">'+ p_name +'</option>';
                    addcategoryHtml += '</select>';
                    addcategoryHtml += '</div>';
                    addcategoryHtml += '</div>';
                }

                addcategoryHtml += '<div class="form-group">';
                addcategoryHtml += '<label class="col-sm-4 control-label" for="stock_edit" style="text-align: right;line-height: 32px;;">';
                addcategoryHtml += '<span style="color:red">* </span>状态：</label>';
                addcategoryHtml += '<div class="col-sm-8" style="line-height: 50px;">';
                addcategoryHtml += '<input type="radio" class="square-radio" name="status" value="1" checked> 启用&nbsp;&nbsp;';
                addcategoryHtml += '<input type="radio" class="square-radio" name="status" value="0"> 禁用';
                addcategoryHtml += '</div>';
                addcategoryHtml += '</div>';
                addcategoryHtml += '</form>';
                addcategoryHtml += '</div></div>';

                layer.open({
                    title : '添加分类',
                    type : 1,
                    move :false,
                    btnAlign:'c',
                    area : ['550px','300px'],
                    closeBtn : false,
                    content : addcategoryHtml,
                    btn : ['保存' ,'取消'],
                    yes :function(){

                        var dt = E.getFormValues('pop_form');
                        if( dt.category_name == ''){
                            layer.msg('请输入分类名称',{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                            return false;
                        }

                        dt.level = level ;

                        E.ajax({
                            type: 'get',
                            url : '/admin/category/add' ,
                            dataType : 'json' ,
                            data : dt,
                            success :function (obj){

                                if( obj.code == 200 ){

                                    layer.msg('添加成功',{ icon : 1 ,shade: [0.15, 'black'], time : 1000 });
                                    window.location.reload();
                                }else{
                                    layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                                }
                            }
                        })
                    }
                });

                addIcheck();

            }).on('click','.change-status',function(){

                var status = $(this).attr('data-status');
                var id = $(this).attr('data-id');
                var _this = $(this);

                if( status == 0 ){
                    var title = "确定禁用分类吗?" ;
                }else{
                    var title = "确定启用分类吗?" ;
                }

                layer.confirm( title ,{icon: 3, title:'提示'}, function(index){

                    E.ajax({
                        type : 'get',
                        url : '/admin/category/status',
                        dataType : 'json',
                        data : { status : status ,id : id },
                        success : function (obj){
                            if ( obj.code == 200 ){
                                if( status == 0 ){
                                    layer.msg('禁用成功', { icon : 1 ,shade: [0.15, 'black'], time : 1500 });
                                    _this.text('已禁用').attr('data-status' , 1 );
                                    _this.parent().parent().next().find('.change-status').text('已禁用').attr('data-status' , 1 );
                                }else{
                                    layer.msg('启用成功', { icon : 1 ,shade: [0.15, 'black'], time : 1500 });
                                    _this.text('已启用').attr('data-status' ,0 );
                                    _this.parent().parent().next().find('.change-status').text('已启用').attr('data-status' ,0 );
                                }
                            }else{
                                layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                            }
                        }
                    });

                    layer.close(index);
                });
            }).on('click','.edit-category',function(){

                var id = $(this).attr('data-id');
                var category_name = $(this).parent().next().next().next().text();

                var editcategoryHtml = '';

                editcategoryHtml += '<div id="pop" style="margin-top:10px;" style="width: 100%">';
                editcategoryHtml += '<div style="background: #ffffff">';
                editcategoryHtml += '<form action="" id="pop_form" class="form-horizontal" role="form" style="margin-top: 30px;">';
                editcategoryHtml += '<input type="hidden" name="id" value="'+ id +'">';
                editcategoryHtml += '<div class="form-group">';
                editcategoryHtml += '<label class="col-sm-4 control-label" for="stock_edit" style="text-align: right;line-height: 21px;;">';
                editcategoryHtml += '<span style="color:red">* </span>分类名称：</label>';
                editcategoryHtml += '<div class="col-sm-8">';
                editcategoryHtml += '<input type="text" name="category_name" class="form-control" style="width:160px;" value="'+ category_name +'">';
                editcategoryHtml += '</div>';
                editcategoryHtml += '</div>';
                editcategoryHtml += '</form>';
                editcategoryHtml += '</div></div>';

                layer.open({
                    title : '编辑分类',
                    type : 1,
                    move :false,
                    btnAlign:'c',
                    area : ['400px','200px'],
                    closeBtn : false,
                    content : editcategoryHtml,
                    btn : ['保存' ,'取消'],
                    yes :function(){

                        var dt = E.getFormValues('pop_form');
                        if( dt.category_name == ''){
                            layer.msg('请输入分类名称',{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                            return false;
                        }

                        E.ajax({
                            type: 'get',
                            url : '/admin/category/edit' ,
                            dataType : 'json' ,
                            data : dt,
                            success :function (obj){

                                if( obj.code == 200 ){

                                    layer.msg('编辑成功',{ icon : 1 ,shade: [0.15, 'black'], time : 1000 });
                                    window.location.reload();
                                }else{
                                    layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                                }
                            }
                        })
                    }
                });

                addIcheck();

            }).on('click','.sync-category',function(){

                E.ajax({
                    type : 'get',
                    url :'/admin/category/sync',
                    dataType : 'json',
                    data : {} ,
                    success : function (obj){
                        if( obj.code == 200 ){
                            layer.msg('同步成功',{ icon : 1 ,shade: [0.15, 'black'], time : 1000 });
                        }else{
                            layer.msg( obj.message ,{ icon : 2 ,shade: [0.15, 'black'], time : 2000 });
                        }
                    }
                })
            });
        });

        function addIcheck(){   //icheck 初始化
            $('.square-radio').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        }


    </script>
@endsection
