<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/12
 * Time: 下午2:11
 * Desc: 微信图片上传类
 */
require_once (dirname(__FILE__).'/../requesthandler.class.php');
require_once (dirname(__FILE__).'/../wxjssdk/jssdk.php');


class WPPhoto extends RequestHandler {
    /**
     * @var string 微信APP id
     */
    private $wXAppId = '';
    /**
     * @var string 微信APP Secret
     */
    private $wXAPPSecret = '';

    protected  $signPackage = '';

    protected $jssdk = '';

    public function __construct ($router=array()){
        $router = !empty($router) ? $router : array('list'=>'list_json','takephoto'=>'takePhoto','detail'=>'detail','upload'=>'upload');
        parent::__construct($router);
        $this->init_self();
    }

    public function init_self(){
        global $wpdb;
        $wpdb->weixin_photos = $wpdb->prefix.'weixin_photos';
        $this->wXAppId = weixin_robot_get_setting('weixin_app_id');
        $this->wXAPPSecret = weixin_robot_get_setting('weixin_app_secret');
        if ($this->wXAppId && $this->wXAPPSecret){
            $this->jssdk = new JSSDK($this->wXAppId, $this->wXAPPSecret);
            $this->signPackage = $this->jssdk->GetSignPackage();
        }else{
//            $msg = '您的微信APPID或者APP_SECRET配置有误';
//            if ($this->is_post()){
//                $this->errors->add('wx_config_error',$msg);
//                return;
//            }else{
//                die($msg);
//            }
        }
    }

    public function getAccessToken(){
        return $this->jssdk->getAccessToken();
    }

    /**
     * 拍照
     */
    public function takePhoto(){
        if (!is_user_logged_in()){
            $url = home_url($_SERVER['PATH_INFO'].'?signon&redirect='.urlencode(home_url($_SERVER['PATH_INFO'].'?takephoto')));
            header("Location:$url");
            return;
        }
        load_photo_content('take-photo-template');
    }

    public function list_json(){
        global $wpdb;
        $this->response_json = true;
        $uid = get_current_user_id();
        $list = $wpdb->get_results($wpdb->prepare("SELECT url,message FROM {$wpdb->weixin_photos} WHERE uid = %d ORDER BY add_time DESC ",$uid),ARRAY_A);
        if ($list){
            $urls = array();
            foreach ($list as $v){
                $urls[] = array('url'=>WEIXIN_ROBOT_PLUGIN_URL.WX_UPLOAD_DIR.$v['url'],'title'=>html_entity_decode(stripslashes($v['message'])));
            }
            $this->response = $urls;
        }else{
            $this->errors->add('no_data', '没有数据');
        }
    }

    public function detail(){
        load_photo_content('photo-detail-template');
    }
    /**
     * 照片列表
     */
    public function upload(){
        global $wpdb;
        $this->response_json = true;
        $uid = get_current_user_id();
        $serverid = $_REQUEST['serverid'];
        $message = $_REQUEST['desc'];
        if ($serverid){
            $download_ids = array();
            $response_url = array();
            $serverid = explode(',',$serverid);
            foreach ($serverid as $v){
                if ($wpdb->query($wpdb->prepare("REPLACE INTO {$wpdb->weixin_photos} (uid,serverid,message,url) VALUES({$uid},%s,%s,'') ",$v,$message))){
                    $download_ids[] = $v;
                }
            }
            /**
             * 如果成功，则下载图片
             */
            if (!empty($download_ids)){
                foreach ($download_ids as $rid){
                    $file =  fetch_photo($rid);
                    if ($file[0] && $rid){
                        if (!$message){
                            $message = date('Y-m-d');
                            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->weixin_photos} SET url=%s,message=%s WHERE uid=%d AND serverid=%s",$file[0],$message,$uid,$rid));
                        }
                        else {
                            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->weixin_photos} SET url=%s WHERE uid=%d AND serverid=%s",$file[0],$uid,$rid));
                        }
                        $response_url[] = $file[0];
                    }
                }
                $this->response = $response_url;
            }
        }else{
            $this->errors->add('invalid_args', '参数错误');
        }
    }
}

$photo = new WPPhoto();