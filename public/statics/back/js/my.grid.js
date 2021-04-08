$(function(){
    $(document).on('click', 'th input:checkbox' , function(){
        var that = this;
        $(this).closest('table').find('tr > td:first-child input:checkbox')
        .each(function(){
            this.checked = that.checked;
            $(this).closest('tr').toggleClass('selected');
        });
    });

    var formType = '{$formType}';

    $('.viewBtn[ajax]').on('click',function(){
      
        $.get($(this).attr('ajax'),function(result){
            if(result.status==1){
                $('#container').html(result.data);
                var dialog = $('#container').dialog({
                        modal: true,
                        title: "<div class='widget-header widget-header-small'><h4 class='smaller'><i class='ace-icon fa fa-check'></i> 查看</h4></div>",
                        title_html: true,
                        width:600,
                        height:'auto'                        
                    });
            }
            else{
                errorMsg(result.msg);
            }
            
        });
    });


    $( ".formBtn[ajax]" ).on('click', function(e) {
        e.preventDefault(); 
        $.get($(this).attr('ajax'),function(result){

            if(result.status=="1"){

                $('#container').html(result.data);

                if(typeof afterFormBtnAct!=='undefined'){
                    afterFormBtnAct();
                }

                var dialog = $('#container').dialog({
                    modal: true,
                    title: "<div class='widget-header widget-header-small'><h4 class='smaller'><i class='ace-icon fa fa-check'></i> 表单</h4></div>",
                    title_html: true,
                    width:600,
                    height:'auto',
                    buttons: [ 
                        {
                            text: "取消",
                            "class" : "btn btn-xs",
                            click: function() {
                                $( this ).dialog( "close" ); 
                            } 
                        },
                        {
                            text: "提交",
                            "class" : "btn btn-primary btn-xs",
                            "id":"editSubmit",
                            click: function() {

                                var _this = this;

                                if(formType=='ajax'){
                                    $.post($('#container form').attr('action'),$('#container form').serialize(),function(result){
                                        if(result.status==1){
                                            successMsg(result.msg);
                                            $(_this).dialog("close"); 

                                            location.reload();
                                        }
                                        else{
                                            errorMsg(result.msg);
                                            // $(_this).dialog("close"); 
                                            
                                        }
                                    });
                                }
                                else{
                                    $('#container form').submit();
                                    //$( this ).dialog( "close" );
                                }

                            } 
                        }
                    ]
                });
            }
            
        })
           

    });

    $( ".addBtn" ).on('click', function(e) {

        $.get($(this).attr('ajax'),function(result){
            $('#container').html(result.data);
            if(typeof afterAddBtnAct!=='undefined'){
                afterAddBtnAct();
            }
        
            var dialog = $('#container').dialog({
                modal: true,
                title: "<div class='widget-header widget-header-small'><h4 class='smaller'><i class='ace-icon fa fa-check'></i> 表单</h4></div>",
                title_html: true,
                width:750,
                height:'auto',
                open:function(){
                    if(typeof(dialog_open)!='undefined'){
                        dialog_open();
                    }
                },
                buttons: [ 
                    {
                        text: "取消",
                        "class" : "btn btn-xs",
                        click: function() {
                            $( this ).dialog( "close" ); 
                        } 
                    },
                    {
                        text: "提交",
                        "class" : "btn btn-primary btn-xs",
                        "id":"editSubmit",
                        click: function() {
                            var _this = this;

                            if(formType=='ajax'){
                                $.post($('#container form').attr('action'),$('#container form').serialize(),function(result){
                                    if(result.status==1){
                                        successMsg(result.msg);
                                        $(_this).dialog("close"); 

                                        location.reload();
                                    }
                                    else{
                                        errorMsg(result.msg);
                                        $(_this).dialog("close"); 
                                        
                                    }
                                });
                            }else{
                                $('#container form').submit();
                                //$( this ).dialog( "close" );
                            }
                            
                            
                        } 
                    }
                ]
            });
        });

        
        
    });

    $( ".deleteBtn[ajax]" ).on('click', function(e) {
        if(confirm('确定要删除此条数据吗？')){
            $.get($(this).attr('ajax'),{id:$(this).attr('data-id')},function(result){
                if(result.status==1){
                    successMsg(result.msg);
                    location.reload();
                }
                else{
                    errorMsg(result.msg);
                }
            })
        }
    });

    $('#bottomBtns button').on('click',function(e){
        //alert($("input[name='ids[]']").serialize());
        if(confirm('确定要'+$(this).text()+'这些信息吗？')){
            $.post($(this).attr('ajax'),$("input[name='ids[]']").serialize(),function(result){
                if(result.status==1){
                    successMsg(result.msg);
                    location.reload();
                }
                else{
                    errorMsg(result.msg);
                }
            })
        }
    });
});