function successMsg(msg){
    if(!msg){
        msg = '';
    }
    $.gritter.add({
        title: '操作成功',
        text: msg,
        class_name: 'gritter-success gritter-center',
        time:2000,
    });
}

function errorMsg(msg){
    if(!msg){
        msg = '';
    }
    $.gritter.add({
        title: '操作失败',
        text: msg,
        class_name: 'gritter-error gritter-center',
        time:2000,
    });
}

$(document).ajaxStart(function(){
    if(showAjaxLoading){
        $("#wait_mask").show();
    }    
    
});
$(document).ajaxComplete(function(){
    if(showAjaxLoading){
        $("#wait_mask").hide();
    }    
});

function isArray(o){
    return Object.prototype.toString.call(o)=='[object Array]';
}

function isDigit(s)
{
    var patrn=/^[0-9]{1,20}$/;
    if (!patrn.exec(s)) return false
    return true
}

function presenceValidate(fields,names){
    var msg = '';

    if(isArray(fields)){
   
        for(var i=0;  i<fields.length; i++){
            console.log(fields[i]);
            if(!$('form [name="'+fields[i]+'"]').val().length){
                msg += '没有填写'+names[i]+'<br>';
            }
        }
     
        if(msg.length){
            var content = $('#alert').html();
            msg = content.replace('{{msg}}',msg);
            bootbox.alert(msg);

            return false;
        }
    }
    console.log('true');
    return true;
}

function ajaxForm(){
    
    $('form.ajax-form').submit(function(){

        $.post($(this).attr('action'),$(this).serialize(),function(result){
            if(result.status==1){
                successMsg(result.msg);
                $('#container').dialog("close"); 

                location.reload();
            }
            else{
                errorMsg(result.msg);
                $('#container').dialog("close"); 
                
            }

        });
        return false;
    });
}

//为数组增加删除操作
Array.prototype.remove = function(from, to) {
    var rest = this.slice((to || from) + 1 || this.length);
    this.length = from < 0 ? this.length + from : from;
    return this.push.apply(this, rest);
};