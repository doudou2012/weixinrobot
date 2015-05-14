<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/12
 * Time: 下午2:54
 */
define ('WX_UPLOAD_DIR','/uploads/weixin/');

require_once (dirname(__FILE__).'/wpphoto.class.php');
require_once (dirname(__FILE__).'/ImageDownload.class.php');

function photo_init(){
//    initTable();
    $photo = new WPPhoto();
    $photo->request_handler();
}

function take_photo(){

    if (!weixin_robot_get_setting('weixin_take_photo')){
        return;
    }
    global $wechatObj;
    echo sprintf($wechatObj->get_textTpl(),'<a href="'.home_url($_SERVER['PATH_INFO'].'?takephoto">拍照</a>'));
    return;
}


function photo_list(){
    if (!weixin_robot_get_setting('weixin_take_photo')){
        return;
    }
    global $wechatObj;
    echo sprintf($wechatObj->get_textTpl(),'<a href="'.home_url($_SERVER['PATH_INFO'].'?takephoto&act=list">进入我的相册</a>'));
    return;
}

//function initTable(){
//    global $wpdb;
//    if (!$wpdb->weixin_photos){
//        $wpdb->weixin_photos = $wpdb->prefix.'weixin_photos';
//    }
//    if($wpdb->get_var("show tables like '$wpdb->weixin_photos'") != $wpdb->weixin_photos) {
//        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->weixin_photos}` (
//                    `uid` int(10) unsigned NOT NULL DEFAULT '1',
//                    `serverid` varchar(200) NOT NULL DEFAULT '',
//                    `url` varchar(200) DEFAULT NULL,
//                    `message` varchar(255) DEFAULT NULL,
//                    `add_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
//                    PRIMARY KEY (`uid`,`serverid`)
//                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//        $wpdb->query($sql);
//    }
//}

function fetch_photo($mediaid){
    global $photo;
    $accessToken =  $photo->getAccessToken();
    if ($accessToken) {
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$accessToken}&media_id={$mediaid}";
        query_log('',$url);
        $file_url = download_photo($url);
        return $file_url;
    }
}

function download_photo($url){
    $download = new RemoteFileDownloader('weixin', 'pic', true);
    $save_dir = WEIXIN_ROBOT_PLUGIN_DIR.WX_UPLOAD_DIR;
    if (!is_dir($save_dir)){
        //尝试创建目录
        if (!wp_mkdir_p($save_dir)){
            return false;
        }
    }
    $download->set_destination($save_dir, 0755, true);
    $download->set_sources($url);
    $files = '';
    try {
        $files = $download->init();
        return $files;
    }catch (InvalidSourceException $e){
        var_dump($e);
    }catch (InvalidDestinationException $e) {
        var_dump($e);
    }catch (Exception $e) {
        var_dump($e);
    }
    return false;
}


function load_photo_content($tpl){
    header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    header('Expires: Sat, 26 Jul 2014 05:00:00 GMT');
    load_template(WEIXIN_ROBOT_PLUGIN_DIR.'/account/template/account-header-template.php',true);
    load_template(WEIXIN_ROBOT_PLUGIN_DIR.'/photo/template/'.$tpl.'.php',true);
    load_template(WEIXIN_ROBOT_PLUGIN_DIR.'/photo/template/account-footer-template.php',true);
}
