<?php
/**
 * Created by PhpStorm.
 * File: wpfavorite.class.php
 * Desc: 用户收藏处理类
 * User: user
 * Date: 15/1/26
 * Time: 上午11:21
 */

define('WPF_META_KEY', 'wpf_favorites');

require_once (dirname(__FILE__).'/../requesthandler.class.php');
class WPFavorite extends RequestHandler {

    /**
     * @var int 文章ID
     */
    protected $postId = 0;
    /**
     * @var int 收藏类型
     */
    protected $postType = 1;  //帖子
    /**
     * @var int 用户uid
     */
    protected  $userId = 0;

    /**
     * @var int 当前页码
     */
    private $page = 1;
    /**
     * @var int 页面大小
     * @desc 如果$pageSize = 0，表示取所有的列表
     */
    private $pageSize = 0;

    private $offset = 0;
    /**
     * @param array $router
     * @desc 构造方法
     * @return : no return;
     */

    public function __construct ($router=array()){
        $router= $router ? $router : array('add'=>'add','getlist'=>'getMyFavList','all'=>'getAllFavList','check_fav'=>'check_fav');
        parent::__construct($router);
        $this->init_self();
    }

    public function init_self(){
        $this->postId = isset($_REQUEST['postid']) ? intval($_REQUEST['postid']) : 0;
        $this->postType = isset($_REQUEST['restype']) ? intval($_REQUEST['restype']) : 1;
        $this->pageSize = isset($_REQUEST['pageSize']) ? intval($_REQUEST['pageSize']) : 0;
        $this->page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
        if ($this->pageSize && $this->page){
            $this->offset = ($this->page-1) * $this->page;
        }

        $this->userId = get_current_user_id();
    }

    /**
     * 列表页面
     */
    public function index(){
        $this->response_json = false;
        if (!is_user_logged_in()){
            $redirectUrl = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";// home_url().'?favorite';
            header("Location:".home_url().'?signon&redirect='.urlencode($redirectUrl));
            exit();
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            show_admin_bar( false );
        }

        $ids = $this->getFavsPostIds();
        if ($ids){
            $post_type = isset($_REQUEST['post_type']) ?  $_REQUEST['post_type'] : 'post';
            $order_by = 'field(ID,'.implode(',',$ids).')';
            $args = array(
                'post_type' => $post_type,
                'orderby' => $order_by,
                'post__in' => $ids
            );
            query_posts($args);
        }
        /**
         * 设置收藏标题
         */
        add_filter('gettext','favorite_title');
        /**
         * 加载模板
         */
        load_template(TEMPLATEPATH.'/archive.php');
        /**
         * 去掉设置收藏的标题
         */
        remove_filter('gettext','favorite_title');
    }

    /**
     * @return bool 提供服务的接口，添加文章收藏
     */
    public function add(){
        //来自客户端请求
        if (isset($_SERVER['REQUEST_METHOD'])){
            $this->response_json = true;
            //如果没有登录
            if (!is_user_logged_in()){
                $this->errors->add('not_login','还没有登录');
                return false;
            }
        }
        if ($ret = $this->addFav()){
            //添加成功,更新帖子收藏次数
            $this->increFavs($ret==1? 1: -1);
            $this->response = $ret ;// $this->postId;
            return true;
        }else if ($this->response_json){
            $this->errors->add('add_favorite_fail','添加收藏失败');
        }
        return false;
    }

    /**
     * @return bool 删除收藏
     */
    public function del(){
        if ($this->del_fav()){
            $this->decreFavs(1);
            return true;
        }else if ($this->response_json){
            $this->errors->add('del_favorite_fail','删除收藏失败');
        }
        return false;
    }

    public function check_fav(){
        $this->response_json = true;
        if (!$this->isLogin()){//没有登录返回
            $this->errors->add('not_login','还没有登录');
            return false;
        }
        if ($this->has_fav()){
            $this->response = $this->postId;
            return true;
        }else{
            $this->errors->add('not_favorite','还没有收藏');
        }
        return false;
    }

    private function has_fav(){
        if ($this->userId && $this->postId){
            global $wpdb;
            $rst = $wpdb->get_row($wpdb->prepare('SELECT post_id FROM wp_favorite WHERE user_id = %d  AND post_id=%d ',$this->userId,$this->postId));
            return $rst && $rst->post_id;
        }
        return false;
    }

    /**
     * 获取我收藏的文章列表
     */
    public function getMyFavList(){
        global $wpdb;
        $this->response_json = true;
        $list = $wpdb->get_results($wpdb->prepare($this->selectFavListSql(true),WPF_META_KEY,$this->userId));
        if ($list){
            $this->listLinkHandle($list);
            $this->response = $list;
        }
        return $list;
    }

    public function getAllFavList(){
        global $wpdb;
        $list = $wpdb->get_results($wpdb->prepare($this->selectFavListSql(false),WPF_META_KEY));
        if ($list){
            $this->listLinkHandle($list);
            $this->response_json = true;
            $this->response = $list;
        }
        return $list;
    }

    /**
     * @return int 获取我收藏总数
     */
    public function getCountMyFavs(){
        global $wpdb;
        $rst = $wpdb->get_row($wpdb->prepare('SELECT COUNT(*) AS cnt FROM wp_favorite WHERE user_id = %d  ',$this->userId));
        return $rst->cnt > 0 ? $rst->cnt : 0;
    }

    public function isLogin(){
        return is_user_logged_in();
    }

    private function getFavsPostIds(){
        global $wpdb;
        $sql = $this->limiteSql('SELECT post_id FROM wp_favorite WHERE user_id = %d ORDER BY add_time DESC');
        $rst = $wpdb->get_results($wpdb->prepare($sql,$this->userId));

        $ids = array();
        if ($rst){
            foreach ($rst as $obj){
                $ids[] = intval($obj->post_id);
            }
        }
        return !empty($ids) ? $ids : false;
    }
    /**
     * 添加收藏
     */
    public function addFav(){
        global $wpdb;
        if ($this->postId && $this->userId){
            $ret = false;
            if ($this->has_fav()){//取消收藏
                if ($wpdb->query($wpdb->prepare('DELETE FROM wp_favorite WHERE user_id = %d AND post_id = %d ',$this->userId,$this->postId))){
                    $ret = 2;
                }
            }else{//收藏
                if ($wpdb->query($wpdb->prepare('REPLACE INTO wp_favorite SET user_id = %d,post_id = %d ,post_type = 1 ',$this->userId,$this->postId))){
                    $ret = 1;
                }
            }
            return $ret;
        }
        return false;
    }

    private function listLinkHandle(&$list){
        if ($list) {
            foreach ($list as &$obj) {
                $obj->url = get_permalink($obj->ID);
            }
        }
        return $list;
    }

    /**
     * @return int 获取收藏的文章收藏数量
     */
    public function getFavCounts(){
        $val = get_post_meta($this->postId, WPF_META_KEY, true);
        if (!$val || $val < 0) $val = 0;
        return intval($val);
    }

    private function increFavs($incre_val = 1){
        $val = $this->getFavCounts();
        $val+=  $incre_val;
        return update_post_meta($this->postId,WPF_META_KEY,$val);
    }

//    public function increase(){
//        return $this->increFavs(1) ? true : false;
//    }

    private function decreFavs($decre_val = 1){
        $val = $this->getFavCounts();
        if ( $val < intval($decre_val)) $val = 0;
        else {
            $val-=intval($decre_val);
            $val = $val < 0 ? 0 : $val;
        }
        return update_post_meta($this->postId,WPF_META_KEY,$val);
    }

//    public function decrease(){
//        return $this->decreFavs(1);
//    }

    /**
     * 获取收藏列表
     */
    private function list_fav(){
        global $wpdb;
        $sql = '';
        $list = $wpdb->get_results($wpdb->prepare($sql,''));
        if ($list){

        }
    }

    private function limiteSql($sql){
        if ($this->pageSize > 0){
            $offset = ($this->page-1)*$this->page;
            $sql.= " LIMITE {$offset},{$this->page} ";
        }
        return $sql;
    }

    private function selectFavListSql($isOnlyMe = true){
        $sql = 'SELECT posts.ID ,posts.post_date,posts.post_title,meta.meta_value AS favs,fav.add_time
                FROM wp_posts as posts,wp_postmeta as meta,wp_favorite as fav
                WHERE posts.ID = fav.post_id AND posts.ID = meta.post_id AND posts.post_status = \'publish\' AND meta.meta_key = %s ';
        if ($isOnlyMe){
            $sql.=' AND fav.user_id =  %d';
        }
        $sql.=' ORDER BY fav.add_time DESC ';
        return $this->limiteSql($sql);
    }
    /**
     * 删除收藏
     */
    private function del_fav(){
        global $wpdb;
        if ($this->postId && $this->userId){
            return $wpdb->query($wpdb->prepare('DELETE FROM wp_favorite WHERE user_id=%d AND post_id = %d',$this->userId,$this->postId));
        }
        return false;
    }
}

//$favorite = new WPFavorite();