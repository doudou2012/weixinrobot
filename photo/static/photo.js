/**
 * Created by user on 15/1/12.
 */
var baseUrl = window.location.pathname;
var title_key = 'album_title';
var renderTpl = function(tpl,data){
    var render = template.compile(tpl);
    return render(data);
}

$(document).ready(function(){
    var takePhoto = function(){
        $('.container').html(home_html);
        //绑定拍照的事件
        $('#take-photo').on('click', function () {
            var the_title = $('#desc').val() || '';
            if (!the_title){
                storage.remove(title_key);
            }else{
                storage.set(title_key,the_title);
            }
            wx.chooseImage({
                success: function (res) {
                    var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                    renderList({desc: the_title, list: localIds});
                },
                fail: function (res) {
                    if (/permision/.test(res.errMsg)){
                        danger_toast('您的微信账号没有使用相册权限');
                    }
                    else {
                        danger_toast(res.errMsg);
                    }
                    return;
                }
            });
        });
    };
    var renderList = function(data){
        var htmlStr = renderTpl(preview_tpl_single_upload,data);
        $('.container').html(htmlStr);
        singleUpload();
        //mutiUpload();
    };

    /**
     * 多张同时上传
     */
    var mutiUpload = function(){
        $('button[name="uploadImage"]').on('click',function(){
            $images = $('img');
            var len = $images.length;
            if (len <=0){
                danger_toast('还没有照片');
                return false;
            }
            var isLast = false;
            $images.each(function(index){
                isLast = (index == len-1);
                var localId = $(this).attr('src');
                if (localId){
                    $(this).attr('disabled',true);
                    var args = {
                        localId:localId,
                        isShowProgressTips:1,
                        success:function(res){
                            var reqUrl = baseUrl+'?upload';
                            alert('serverid is: '+res.serverId);
                            $.post(reqUrl,{serverid:res.serverId,desc:desc},function(data){
                                if (data.success) {
                                    if (isLast) {
                                        completeUpload();
                                    }
                                }
                                else {
                                    $button.attr('disabled',false);
                                    $button.text('重试');
                                }
                            });
                        },
                        fail:function(res){
                            //danger_toast(res.errMsg);
                        }
                    };
                    wx.uploadImage(args);
                }
            });
        });
    };
    /**
     * 单张上传
     */
    var singleUpload = function(){
        var $lastBtn = $('button[name="uploadImage"]:last');
        $('button[name="uploadImage"]').on('click',function(){
            var $button = $(this);
            var localId = $button.prevAll('img:first').attr('src');
            var desc = storage.get(title_key) || '';
            if (localId){
                $button.attr('disabled',true);
                var args = {
                    localId:localId,
                    isShowProgressTips:1,
                    success:function(res){
                        var reqUrl = baseUrl+'?upload';
                        $.post(reqUrl,{serverid:res.serverId,desc:desc},function(data){
                            if (data.success) {
                                if ($button.is($lastBtn)) {
                                    completeUpload();
                                } else{
                                    $button.text('已经上传');
                                }
                            }else {
                                $button.attr('disabled',false);
                                $button.text('重试');
                            }
                        });
                    },
                    fail:function(res){
                        danger_toast(res.errMsg);
                        return;
                        //alert(res.errMsg);
                    }
                };
                wx.uploadImage(args);
            }
        });
    };

    var completeUpload = function(){
        $('.container').html(upload_complete);
        $('#photoListBtn').on('click', function () {
            viewPhoto();
        });
        $('#takePhotoBtn').on('click',function(){
            takePhoto();
        });
    }
    /*照片浏览*/
    var viewPhoto = function(){
        var reqUrl = baseUrl + '?list';
        $.getJSON(reqUrl,{},function(data){
            //if (data.success){
                var imgList = data.data || [],
                    title = storage.get(title_key);
                storage.set(photoSavedKey, imgList);
                var htmlStr = renderTpl(view_tpl,{list:imgList});
                $('.container').html(htmlStr);
                var urls = [];
                if (imgList.length > 0){
                    $.each(imgList,function(index,value){
                        urls.push(value.url);
                    });
                }
                $('img').on('click',function(){
                    var curImgUrl = $(this).attr('src');
                    wx.previewImage({
                        current: curImgUrl,
                        urls: urls
                    });
                });
            //}
            $('#takeMyPhotoBtn').on('click',function(){
                takePhoto();
            });
        });
    };
    if (getParameterByName('act') == 'list') {
        viewPhoto();
    }else{
        takePhoto();
    }
});
