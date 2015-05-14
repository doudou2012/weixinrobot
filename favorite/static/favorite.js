/**
 * Created by user on 15/1/27.
 */
var baseurl = window.location.pathname;
var link_tpl = '' +
    '   <span class="categories-links">' +
    '       <a href="#" id="add_favorite">收藏</a>' +
    '   </span>';
var postId = 0;
$(document).ready(function(){
    var post_id = $('article:first').attr('id').replace(/[^\d]/g,'') || postId;
    $wraper = $('div.post-navigation');
    if ($wraper && (typeof $wraper != undefined)){
        $wraper.prepend(link_tpl);
        //$wraper.html(link_tpl);
        $elem = $('#add_favorite');
    }
    var disabledElem = function(){
        $elem.attr("disabled", "disabled");
        $elem.css({"background-color":"#666","opacity":.65,"pointer-events":"none","cursor":"not-allowed"});
        $elem.text('已收藏');
    };
    var showloginDialog = function(){
        var sign = new Sign({login_success:function(){window.location.reload()}});
        sign.login_dialog();
    };
    var addFav = function(){
        $elem.on('click',function(event){
            event.preventDefault();
            if (post_id){
                var url = baseurl+'?favorite&add';
                $.getJSON(url,{postid:post_id},function(data){
                    if (data.success){
                        disabledElem();
                    }else if (data.error.code == 'not_login'){
                        showloginDialog();
                    }else{
                        alertify.alert('收藏失败:'+data.error.msg).set('basic', true);
                        setTimeout(function(){alertify.close();},3000);
                    }
                });
            }
        });
    };
    if (post_id > 0 && (typeof $elem != undefined)){
        $.getJSON(baseurl+'?favorite&check_fav',{postid:post_id},function(data){
            if (data.success){
                disabledElem();
            }else{
                addFav();
            }
        });

    }
});
