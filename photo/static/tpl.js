/**
 * Created by user on 15/1/14.
 */
/**
 * 图片列表页面模板
 * @type {string}
 */
/**
 * 单行模板
  * @type {string}
 */
var list_line = '  <li><img src="{{value}}" class="img-rounded img-responsive" /></li> ';
/**
 * 浏览图片模板循环
 * @type {string}
 */
var list_tpl = ' {{each list as value}} '
    + ' <li> '
    + ' <img src="{{value.url}}" class="img-rounded img-responsive" /> '
    + ' <p>{{value.title}}</p>'
    + ' </li>'
    + '{{/each}}';
/**
 * 预览图片模板循环
 * @type {string}
 */
var preview_list_tpl = ' {{each list as value}}'
        + '<li>'
        + '<img src="{{value}}" class="img-rounded img-responsive" />'
        + '<p>{{desc}}</p>'
        + '<button type="button" name="uploadImage" class="btn btn-default btn-lg btn-block">上传</button>'
        + '</li>'
        + '{{/each}}';
//图片浏览页面模板
var preview_tpl_single_upload = ' <ul class="photo-list list-unstyled">'
    + preview_list_tpl
    + ' </ul>' ;

var preview_tpl_muti_upload = '<ul class="photo-list list-unstyled">' +
    '{{each list as value}}' +
    '<li>' +
    '<img src="{{value}}" class="img-rounded img-responsive" />' +
    '<p>{{desc}}</p>' +
    '<button type="button" name="uploadImage" class="btn btn-default btn-lg btn-block">上传</button>' +
    '</li>' +
    '{{/each}}' +
    '</ul>';

var view_tpl = '<h2 class="text-center">我的相册</h2> ' +
    '<ul class="photo-list list-unstyled">'
    + list_tpl
    + '</ul>'
    + '<button type="button" id="takeMyPhotoBtn" class="btn btn-primary btn-lg btn-block">上传新图片</button>';
//图片浏览页面
var detail_tpl = '<img class="img-responsive imgdetail" src="{{url}}" alt=""/>';

var take_photo_html = '<button type="button" id="takePhotoBtn" class="btn btn-primary btn-lg btn-block">继续传图</button>';
var photo_list_html = '<button type="button" id="photoListBtn" class="btn btn-primary btn-lg btn-block">去我的相册</button>';
var upload_complete = '<h2 class="text-center">上传成功</h2>'
                        + photo_list_html
                        + take_photo_html;
var home_html = '<h2 class="text-center">上传照片</h2>' +
                '<input id="desc" class="form-control input-lg" type="text" placeholder="图片说明（选填）">' +
                ' <button type="button" id="take-photo" class="btn btn-primary btn-lg btn-block" >选择照片</button>';
