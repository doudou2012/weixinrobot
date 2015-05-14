<?php
/**
 * Created by PhpStorm.
 * File: favorite-functions.php
 * User: user
 * Date: 15/1/26
 * Time: 下午1:42
 */

require_once (dirname(__FILE__).'/wpfavorite.class.php');

function favorite_init(){
    $favorite = new WPFavorite();
    if(isset($_GET['favorite'])){
        $favorite->request_handler();
    }
}

function add_favorite($post_id,$openid){
    $favorite = new WPFavorite();
    $account = new WPAccount();
    $account->openid = $openid;
    if ($post_id && $openid ){//如果已经绑定，则收藏
        if ($uid = $account->check_bind()){
            $favorite->postId = $post_id;
            $favorite->userId = $uid;
            $favorite->addFav();
        }else{
            query_log('','没有绑定');
        }
    }
}

/**
 * @desc 设置收藏列表标题
 * @param $title
 * @return string
 */
function favorite_title($title){
    if ($title == 'Archives')
        return '我的收藏';
    return $title;
}
/**
 * theme JS 和 CSS 注入
 */
add_action( 'wp_enqueue_scripts', 'load_favorite_script' );
function load_favorite_script(){
    wp_enqueue_script( 'alertifyjs-script', WP_PLUGIN_URL . '/wxrobot/static/alertifyjs/alertify.min.js', array( 'jquery' ), '', true );
    wp_enqueue_script( 'sign-script', WP_PLUGIN_URL . '/wxrobot/account/static/sign.js', array( 'jquery' ), '', true );
    if (is_single()){
        wp_enqueue_script( 'favorite-script', WP_PLUGIN_URL . '/wxrobot/favorite/static/favorite.js', array( 'jquery' ), '', true );
    }
}
add_action( 'wp_enqueue_scripts', 'load_favoriate_style' );
function load_favoriate_style(){
    wp_enqueue_style ('alertifyjs-style', WP_PLUGIN_URL.'/wxrobot/static/alertifyjs/css/alertify.min.css');
    wp_enqueue_style ('alert-theme-style', WP_PLUGIN_URL.'/wxrobot/static/alertifyjs/css/themes/bootstrap.min.css');
    wp_enqueue_style ('alert-style', WP_PLUGIN_URL.'/wxrobot/static/css/alert-style.css');
}



