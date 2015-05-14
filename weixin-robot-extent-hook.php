<?php
/**
 * @desc 获取最近7天，还没有结束的展览，这个方法为后台设置的名字
 * @param $keyword
 * 配置方法：week 函数回复 weixin_robot_lastest_week
 */
function weixin_robot_lastest_week($keyword){
    add_filter('weixin_query','weixin_robot_week_query');
    weixin_robot_advanced_reply($keyword);
}

function weixin_robot_week_query($weixin_query_array){
    $time = strtotime('-7 days');
    $weixin_query_array['meta_query'] = array('key'=>'wpcf-end-time','value'=> $time,'compare'=>'>=','type'=>'UNSIGNED');
    return $weixin_query_array;
}

/**
 * @desc 微信回调，获取用户积分
 */
function get_credit(){
    global $wechatObj,$shorturl;
    $openid = $wechatObj->get_fromUsername();
    if (!wx_check_bind($openid)){
        $url = home_url(). '/?wx_reg=1&openid='.$openid;
        if ($shorturl && is_object($shorturl) && method_exists($shorturl,'add_external_link')){
            $url = home_url().'/'.$shorturl->add_external_link($url,'微信用户绑定');
        }
        echo sprintf($wechatObj->get_textTpl(),'<a href="'.$url.'">我要绑定</a>');
    }
    else{
        echo sprintf($wechatObj->get_textTpl(), credit_text($openid));
    }
    return;
}

function wx_check_bind($openid){
    if ($openid) {
        global $wpdb;
        $sql = "SELECT wp_users.user_login FROM {$wpdb->users} INNER JOIN {$wpdb->usermeta} ON (ID = user_id) WHERE meta_key='".WX_OPEN_ID."' AND meta_value = '{$openid}' ";
        $row =$wpdb->get_row($sql);
        return ($row && $row->user_login) ? true : false;
    }
    return false;
}

/**
 * @desc 按城市搜索，这个方法为后台设置的名字
 * @param $keyword
 * 配置方法：city_search 函数回复 weixin_robot_city_search
 */
function weixin_robot_city_search($keyword){
    global $wpdb,$wechatObj;
    $sql="SELECT DISTINCT * FROM wp_posts INNER JOIN wp_term_relationships ON(wp_posts.ID = wp_term_relationships.object_id) INNER JOIN wp_term_taxonomy ON(wp_term_relationships.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id) INNER JOIN wp_terms ON(wp_term_taxonomy.term_id = wp_terms.term_id) WHERE (wp_terms.name LIKE '%{$keyword}%' OR wp_terms.slug LIKE '%{$keyword}%' ) AND wp_posts.post_type='event' AND  wp_posts.post_status='publish'";
    $search_result = $wpdb->get_results($sql, OBJECT);
//    query_log('',$sql);
    $items = '';
    $weixin_count = weixin_robot_get_setting('weixin_count');
    if (count($search_result) > 0){
        $counter = 0;
        foreach ($search_result as $row){
            $title	= apply_filters('weixin_title',get_the_title($row));
            $excerpt= apply_filters('weixin_description', get_post_excerpt( $row,apply_filters( 'weixin_description_length', 150 ) ) );
            $url	= apply_filters('weixin_url', get_permalink($row->ID));
            if($counter == 0){
                $thumb = get_post_weixin_thumb($row, array(640,320));
            }else{
                $thumb = get_post_weixin_thumb($row, array(80,80));
            }
            $items = $items . $wechatObj->get_item($title, $excerpt, $thumb, $url);
            $counter ++;
        }
    }
    $cnt = count($search_result) > $weixin_count ? $weixin_count :  count($search_result);
    if ($items){
        echo sprintf($wechatObj->get_picTpl(),$cnt,$items);
        return;
    }
}

function weixin_robot_city_search_query($weixin_query_array){
    $s = $weixin_query_array['s'];
    $weixin_query_array['tax_query'] = array(
        'relation'  => 'OR',
        'include_children' => false,
        array(
            'taxonomy' => 'city',
            'terms'     => $s,
            'field'     =>  'name',
        ),
        array(
            'taxonomy' => 'city',
            'terms'     => $s,
            'field'     =>  'slug',
        ),
    );
    unset($weixin_query_array['s']);
    return $weixin_query_array;
}



/**
 * @desc 按productid搜索(wpcf-wechat_id)，这个方法为后台设置的名字
 * @param $keyword
 * 配置方法：productid_search 函数回复 weixin_robot_productid_search
 * 如果有product link，文本回复返回title + link （for goodhunt)
 * 若无，图文回复返回文章(for others)
 */
function weixin_robot_productid_search($keyword){
    global  $wp_the_query, $wechatObj;
    $query_array = $wechatObj->get_query_param($keyword);
    $query_array['meta_query'] = array(
        array(
            'key'       => 'wpcf-wechat_id',
            'value'     => $keyword,
            'compare'   => '=',
            'type'      => 'UNSIGNED',
        ),
    );
    unset($query_array['s']);
    $the_query = new WP_Query( $query_array );
    if ($the_query->post_count > 0){
        $items = '';
        $has_link = false;
        while ( $the_query->have_posts() ){
            $the_query->the_post();
            $id = $the_query->post->ID;
            /**
             * 如果绑定了，就加入收藏列表中
             */
            add_favorite($id,$wechatObj->get_fromUsername());

            $title	= apply_filters('weixin_title', get_the_title());
            $product_link = get_post_meta($id,'link',true);
            $url	= apply_filters('weixin_url', get_permalink());
            $thumb = get_post_weixin_thumb('', array(640,320));
            if ($product_link != false && trim($product_link)){
                if (strpos($product_link,'?') !== false) {
                    $product_link = $product_link."&utm_source=GoodHunt";
                }else{
                    $product_link = $product_link."?utm_source=GoodHunt";
                }
                $has_link = true;
                $items = $title."\n".$product_link;
            }
            else{
                $items = $items . $wechatObj->get_item($title, '', $thumb,$url);
            }
        }
        if ($has_link){
            $account = new WPAccount();
            $account->openid = $wechatObj->get_fromUsername();
            $tips = '';
            if (!$account->check_bind()){
                $tips .='

提示：<a href="'.home_url($_SERVER['PATH_INFO']).'?wx_reg=1&openid='.$account->openid.'" > 登录之后可以收藏查询的产品</a>';
            }else{
            	$tips.='

<a href="'.home_url($_SERVER['PATH_INFO']).'?favorite">查看我的收藏»</a>';
            }
            echo sprintf($wechatObj->get_textTpl(),$items.$tips);
            return;
        }
        else{
            $cnt = $the_query->post_count > $query_array['posts_per_page'] ? $query_array['posts_per_page'] :  $the_query->post_count ;
            if ($items){
                echo sprintf($wechatObj->get_picTpl(),$cnt,$items);
                return;
            }
        }
    }
    echo '';
    return;
}

/**
 * @desc 获取我的收藏列表 图文回复返回文章
 * @param $keyword
 * @return null
 */
function my_favorite($keyword){
    global $wechatObj;
    $favorite = new WPFavorite();
    $account = new WPAccount();
    $account->openid = $wechatObj->get_fromUsername();
    $uid = $account->check_bind();
    if ($account->openid && $uid){
        //已经绑定登录,获取登录列表
        $favorite->userId = $uid;
        $cnt  = $favorite->getCountMyFavs();
        if ($cnt > 0){
            echo sprintf($wechatObj->get_textTpl(),'<a href="'.home_url($_SERVER['PATH_INFO']).'?favorite" >查看我的收藏»</a>');
        }
        else{
            echo sprintf($wechatObj->get_textTpl(),'根据文章提示回复数字ID，可以收藏喜欢的产品~');
        }
    }else{
        $redirect = urlencode(home_url($_SERVER['PATH_INFO']).'?favorite');
        $bind_url = home_url($_SERVER['PATH_INFO']).'?wx_reg&openid='.$account->openid.'&redirect='.$redirect;
//        $sigon_url = home_url($_SERVER['PATH_INFO']).'?signon&openid='.$account->openid.'&redirect='.$redirect;
        $link = '<a href="'.$bind_url.'">登录之后才能查看收藏哦~</a>';
        echo sprintf($wechatObj->get_textTpl(),$link);
    }
    return;
}