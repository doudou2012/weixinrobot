<?php
/* WPJAM OPTIONS 
** Version: 1.0
*/

function wpjam_option_page($labels, $title='', $type='default', $icon='options-general'){
	extract($labels);
	?>
	<div class="wrap">
	<?php if($type == 'tab'){ ?>
		<h2 class="nav-tab-wrapper">
	        <?php foreach ( $sections as $section_name => $section) { ?>
	            <a class="nav-tab" href='javascript:void();' id="tab-title-<?php echo $section_name; ?>"><?php echo $section['title']; ?></a>
	        <?php } ?>    
	    </h2>
		<form action="options.php" method="POST">
			<?php settings_fields( $option_group ); ?>
			<?php foreach ( $sections as $section_name => $section ) { ?>
	            <div id="tab-<?php echo $section_name; ?>" class="div-tab hidden">
	                <?php wpjam_do_settings_section($option_page, $section_name); ?>
	            </div>                      
	        <?php } ?>
			<input type="hidden" name="<?php echo $option_name;?>[current_tab]" id="current_tab" value="" />
			<?php submit_button(); ?>
		</form>
		<?php wpjam_option_tab_script($option_name);?>
	<?php }else{ ?>
		<?php if($title){?>
			<?php if(preg_match("/<[^<]+>/",$title,$m) != 0){ ?>
				<?php echo $title; ?>
			<?php } else { ?>
				<h2><?php echo $title; ?></h2>
			<?php } ?>
		<?php }?>
		<form action="options.php" method="POST">
			<?php settings_fields( $option_group ); ?>
			<?php do_settings_sections( $option_page ); ?>
			<?php submit_button(); ?>
		</form>
	<?php } ?>
	</div>
	<?php
}

function wpjam_option_tab_script($option_name=''){
	$current_tab = '';

	if($option_name){
		$option = get_option( $option_name );
		if(!empty($_GET['settings-updated'])){
			$current_tab = $option['current_tab'];
		}
	}
	?>
	<script type="text/javascript">
	<?php if($current_tab){ ?>
		jQuery('#tab-title-<?php echo $current_tab; ?>').addClass('nav-tab-active');
		jQuery('#tab-<?php echo $current_tab; ?>').show();
	<?php } else{ ?>
		//设置第一个显示
		jQuery('a.nav-tab').first().addClass('nav-tab-active');
		jQuery('div.div-tab').first().show();
	<?php } ?>
		jQuery(document).ready(function(){
			jQuery('a.nav-tab').on('click',function(){
		        jQuery('a.nav-tab').removeClass('nav-tab-active');
		        jQuery(this).addClass('nav-tab-active');
		        jQuery('div.div-tab').hide();
		        jQuery('#'+jQuery(this)[0].id.replace('title-','')).show();
		        jQuery('#current_tab').val(jQuery(this)[0].id.replace('tab-title-',''));
		    });
		});
	</script>
<?php
}

function wpjam_add_settings($labels,$defaults){
	extract($labels);
	register_setting( $option_group, $option_name, $field_validate );
	$field_callback = 'wpjam_field_callback';
	if($sections){
		foreach ($sections as $section_name => $section) {
			add_settings_section( $section_name, $section['title'], $section['callback'], $option_page );

			$fields = isset($section['fields'])?$section['fields']:(isset($section['fileds'])?$section['fileds']:''); // 尼玛写错英文单词的 fallback

			if($fields){
				foreach ($fields as $field_name=>$field) {
					$field['option']	= $option_name;
					$field['name']		= $field_name;

					$filed_title		= $field['title'];

					if(in_array($field['type'], array('text','password','select','datetime','textarea','checkbox'))){
						$filed_title = '<label for="'.$field_name.'">'.$filed_title.'</label>';
					}

					$field['default'] 	= isset($defaults[$field_name])?$defaults[$field_name]:'';
					add_settings_field( 
						$field_name,
						$filed_title,		
						$field_callback,	
						$option_page, 
						$section_name,	
						$field
					);	
				}

			}
		}
	}
}

function wpjam_field_callback($args) {

	$option_name	= $args['option'];
	$field_name		= $args['name'];

	$wpjam_option	= get_option( $option_name );

	$value			= (isset($wpjam_option[$field_name]))?$wpjam_option[$field_name]:$args['default'];
	$field 			= $option_name.'['.$field_name.']';
	$type			= $args['type'];
	$description	= (isset($args['description']))?($type == 'checkbox')?' <label for="'.$field_name.'">'.$args['description'].'</label>':'<br />'.$args['description']:'';

	if($type == 'text'){
		echo '<input type="text" id="'.$field_name.'" name="'.$field.'" value="'.$value.'" class="regular-text" />';
	}elseif($type == 'password'){
		echo '<input type="password" id="'.$field_name.'" name="'.$field.'" value="'.$value.'" class="regular-text" />';
	}elseif($type == 'checkbox'){
		echo '<input type="checkbox" id="'.$field_name.'" name="'.$field.'" value="1" '.checked("1",$value,false).' />';
	}elseif($type == 'textarea'){
		$rows = isset($args['rows'])?$args['rows']:10;
		echo '<textarea id="'.$field_name.'" name="'.$field.'" rows="'.$rows.'" cols="50" class="large-text  code">'.$value.'</textarea>';
	}elseif ($type == 'select'){
		echo '<select id="'.$field_name.'" name="'.$field.'">';
		foreach ($args['options'] as $option_value => $option_title){ 
			echo '<option value="'.$option_value.'" '.selected($option_value,$value,false).'>'.$option_title.'</option>';
		}
		echo '</select>';
	}
	echo $description;
}

// 拷贝自 do_settings_sections 函数，用于 tab 显示选项。
function wpjam_do_settings_section($option_page, $section_name){
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[$option_page] ) )
		return;

	$section = $wp_settings_sections[$option_page][$section_name];

	if ( $section['title'] )
		echo "<h3>{$section['title']}</h3>\n";

	if ( $section['callback'] )
		call_user_func( $section['callback'], $section );

	if ( isset( $wp_settings_fields ) && isset( $wp_settings_fields[$option_page] ) && !empty($wp_settings_fields[$option_page][$section['id']] ) ){
		echo '<table class="form-table">';
		do_settings_fields( $option_page, $section['id'] );
		echo '</table>';
	}
}


function wpjam_get_setting($option, $setting_name){
	if(isset($option[$setting_name])){
		return str_replace("\r\n", "\n", $option[$setting_name]);
	}else{
		return '';
	}
}

function wpjam_get_option($option_name,$defaults){
	$option = get_option( $option_name );
	return wp_parse_args($option, $defaults);
}

function wpjam_admin_pagenavi($total_count, $number_per_page=50){

	$current_page = isset($_GET['paged'])?$_GET['paged']:1;

	if(isset($_GET['paged'])){
		unset($_GET['paged']);
	}

	$base_url = add_query_arg($_GET,admin_url('admin.php'));

	$total_pages	= ceil($total_count/$number_per_page);

	$first_page_url	= $base_url.'&amp;paged=1';
	$last_page_url	= $base_url.'&amp;paged='.$total_pages;
	
	if($current_page > 1 && $current_page < $total_pages){
		$prev_page		= $current_page-1;
		$prev_page_url	= $base_url.'&amp;paged='.$prev_page;

		$next_page		= $current_page+1;
		$next_page_url	= $base_url.'&amp;paged='.$next_page;
	}elseif($current_page == 1){
		$prev_page_url	= '#';
		$first_page_url	= '#';
		if($total_pages > 1){
			$next_page		= $current_page+1;
			$next_page_url	= $base_url.'&amp;paged='.$next_page;
		}else{
			$next_page_url	= '#';
		}
	}elseif($current_page == $total_pages){
		$prev_page		= $current_page-1;
		$prev_page_url	= $base_url.'&amp;paged='.$prev_page;
		$next_page_url	= '#';
		$last_page_url	= '#';
	}
	?>
	<div class="tablenav bottom">
		<div class="tablenav-pages">
			<span class="displaying-num">每页 <?php echo $number_per_page;?> 共 <?php echo $total_count;?></span>
			<span class="pagination-links">
				<a class="first-page <?php if($current_page==1) echo 'disabled'; ?>" title="前往第一页" href="<?php echo $first_page_url;?>">«</a>
				<a class="prev-page <?php if($current_page==1) echo 'disabled'; ?>" title="前往上一页" href="<?php echo $prev_page_url;?>">‹</a>
				<span class="paging-input">第 <?php echo $current_page;?> 页，共 <span class="total-pages"><?php echo $total_pages; ?></span> 页</span>
				<a class="next-page <?php if($current_page==$total_pages) echo 'disabled'; ?>" title="前往下一页" href="<?php echo $next_page_url;?>">›</a>
				<a class="last-page <?php if($current_page==$total_pages) echo 'disabled'; ?>" title="前往最后一页" href="<?php echo $last_page_url;?>">»</a>
			</span>
		</div>
		<br class="clear">
	</div>
	<?php
}

function wpjam_admin_display_fields($fields, $fields_type = 'table'){
	$new_fields = array();
	foreach($fields as $name => $field){ 
		$type		= $field['type'];
		$value		= $field['value'];

		$class		= isset($field['calss'])?$field['class']:'regular-text';
		$description= (isset($field['description']))?($type == 'checkbox')?' <label for="'.$name.'">'.$field['description'].'</label>':'<br />'.$field['description']:'';

		$title 		= $field['title'];
		if(in_array($type, array('text','password','select','datetime','textarea','checkbox'))){
			$title = '<label for="'.$name.'">'.$title.'</label>';
		}

		if($type == 'text' || $type == 'datetime'){
			$new_fields[$name] = array('title'=>$title, 'html'=>'<input name="'.$name.'" id="'. $name.'" type="text"  value="'.esc_attr($value).'" class="'.$class.'" />'.$description);
		}elseif($type == 'password'){
			$new_fields[$name] = array('title'=>$title, 'html'=>'<input name="'.$name.'" id="'. $name.'" type="password"  value="'.esc_attr($value).'" class="'.$class.'" />'.$description);
		}elseif ($type == 'hidden'){
			$new_fields[$name] = array('title'=>$title, 'html'=>'<input name="'.$name.'" id="'. $name.'" type="hidden"  value="'.esc_attr($value).'" />'.$description);
		}elseif ($type == 'checkbox'){
			$new_fields[$name] = array('title'=>$title, 'html'=>'<input name="'.$name.'" id="'. $name.'" type="checkbox"  value="'.esc_attr($value).'" '.$field['checked'].' />'.$description);
		}elseif($type == 'file'){
			$new_fields[$name] = array('title'=>$title, 'html'=>'<input name="'.$name.'" id="'. $name.'" type="text"  value="'.esc_attr($value).'" '.$field['checked'].' /><input onclick="wpjam_media_upload(\''. $name.'\')" class="button button-highlighted" type="button" value="上传'.$title.'" />').$description;
		}elseif($type == 'textarea'){
			$rows = isset($field['rows'])?$field['rows']:6;
			$new_fields[$name] = array('title'=>$title, 'html'=>'<textarea name="'.$name.'" id="'. $name.'" rows="'.$rows.'" cols="50"  class="'.$class.' code" >'.esc_attr($value).'</textarea>'.$description);
		}elseif ($type == 'select'){
			$new_field_html  = '<select name="'.$name.'" id="'. $name.'">';
			foreach ($field['options'] as $option_value => $option_title){ 
				$new_field_html .= '<option value="'.$option_value.'" '.selected($option_value,$value,false).'>'.$option_title.'</option>';
			}
			$new_field_html .= '</select>';
			$new_fields[$name] = array('title'=>$title, 'html'=>$new_field_html.$description);
		}elseif ($type == 'radio'){
			$new_field_html  = '';
			foreach ($field['options'] as $option_value => $option_title) {
				$new_field_html  .= '<p><input name="'.$name.'" type="radio" id="'.$name.'" value="'.$option_value .'" '.checked($option_value,$value,false).' /> '.$option_title.'</p>';
			}
			$new_fields[$name] = array('title'=>$title, 'html'=>$new_field_html.$description);
		}
	}
	
	?>
	<?php if($fields_type == 'table'){ ?>

	<table class="form-table" cellspacing="0">
		<tbody>
		<?php foreach ($new_fields as $name=>$field) { ?>

			<tr valign="top" id="tr_<?php echo $name; ?>">
				<th scope="row"><?php echo $field['title']; ?></th>
				<td><?php echo $field['html']; ?></td>
			</tr>

		<?php } ?>
		</tbody>
	</table>

	<?php } elseif($fields_type == 'list'){ ?>

	<ul>
	<?php foreach ($new_fields as $name=>$field) { ?>

		<li><?php echo $field['title']; ?> <?php echo $field['html']; ?> </li>

	<?php } ?>
	</ul>

	<?php } ?>

	<?php
}

function wpjam_confim_delete_script(){
	?>
	<script type="text/javascript">
	jQuery(function(){
		jQuery('span.delete a').click(function(){
			return confirm('确实要删除吗?'); 
		}); 
	});
	</script> 
	<?php
}


//保存自定义字段
add_action('save_post', 'wpjam_save_post_options', 999);
function wpjam_save_post_options($post_id){
    // to prevent metadata or custom fields from disappearing...
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )
        return $post_id;

    $post = get_post($post_id);
    $wpjam_options = wpjam_get_post_options();
    foreach ($wpjam_options as $meta_box => $wpjam_group) {
        if(in_array($post->post_type,$wpjam_group['post_types'])){
            foreach($wpjam_group['fields'] as $key=>$wpjam_field){
                switch($wpjam_field['type']){
                    case 'file':
                        if($_POST['wpjam_delete_field'][$key]){
                            delete_post_meta($post_id,$key,$_POST['wpjam_delete_field'][$key]);
                        }
                        if(isset($_FILES[$key])){
                            require_once(ABSPATH . 'wp-admin/includes/admin.php');
                            $attachment_id=media_handle_upload($key,$post_id);
                            if(!is_wp_error($attachment_id)){
                                update_post_meta($post_id,$key,$attachment_id);
                            }
                            unset($attachment_id);
                        }
                        break;
                    case 'checkbox':
                        if(isset($_POST['checkbox_'.$key])){
                            //xxx特殊设置，防止在前台修改此值
                            if(is_admin())
                                update_post_meta($post_id,$key,$_POST[$key]);
                        }
                        break;
                    case 'mulit_image':
                        if(isset($_POST[$key]) && is_array($_POST[$key])){
                            //删除空图片
                            foreach($_POST[$key] as $image_key=>$image_value){
                                if(empty($image_value))
                                    unset($_POST[$key][$image_key]);
                            }
                            update_post_meta($post_id,$key,$_POST[$key]);
                        }
                        break;
                    case 'mulit_text':
                        if(isset($_POST[$key]) && is_array($_POST[$key])){
                            foreach($_POST[$key] as $multiple_text_key=>$item_value){
                                if(empty($item_value))
                                    unset($_POST[$key][$multiple_text_key]);
                            }
                            update_post_meta($post_id,$key,$_POST[$key]);
                        }
                        break;
                    default:
                        if(isset($_POST[$key])){
                            update_post_meta($post_id,$key,$_POST[$key]);
                        }
                }
            }
        }
    }
}

/*
 * 获取自定义字段设置
 */
function wpjam_get_post_options(){
    $wpjam_options = apply_filters('wpjam_options', array());
    return $wpjam_options;
}

//输出自定义字段表单
function wpjam_post_options_callback( $post, $metabox){
    if(isset($metabox['args']['meta_box'])){
        $meta_box = $metabox['args']['meta_box'];
    } else{
        $meta_box = '';
    }
    $wpjam_options = wpjam_get_post_options();
    echo '<table width="100%">';
    foreach ($wpjam_options[$meta_box]['fields'] as $key => $wpjam_field) {
        $label = $wpjam_field['name'];
        if(isset($_REQUEST[$key])){
            $value  = $_REQUEST[$key];
        }else{
            $value = get_post_meta($post->ID, $key, true);
        }
        ?>
        <tr>
            <td valign="top" width="150"><label for="<?php echo $key;?>" style="width:150px; display: inline-block; text-align:right; margin-right:10px; vertical-align: top;"><?php echo $label; ?>：</label></td>
            <td valign="top" align="left">
                <?php
                switch($wpjam_field['type']){
                    case 'textarea':
                        ?>
                        <textarea id="<?php echo $key;?>" name='<?php echo $key;?>' rows="5" style="width:70%" ><?php echo esc_html($value);?></textarea>
                        <?php
                        break;
                    case 'image':
                        ?>
                        <input type="text" name="<?php echo $key;?>" value="<?php echo esc_attr($value);?>" id="<?php echo $key;?>" style="width:70%;" /><input type="botton" class="wpjam_upload button" style="width:80px;" value="选择图片">
                        <?php
                        break;
                    case 'mulit_image':
                        if(is_array($value)){
                            foreach($value as $image_key=>$image){
                                if(!empty($image)){
                                    ?>
                                    <span><input type="text" name="<?php echo $key;?>[]" value="<?php echo esc_attr($image);?>" style="width:70%;" /><a href="javascript:;" class="button del_image">删除</a></span>
                                <?php
                                }
                            }
                        }
                        ?>
                        <span><input type="text" name="<?php echo $key;?>[]" value="" id="<?php echo $key;?>" style="width:70%;" /><input type="botton" class="wpjam_mulit_upload button" style="width:110px;" value="选择图片[多选]" title="按住Ctrl点击鼠标左键可以选择多张图片"></span>
                        <?php
                        break;
                    case 'mulit_text':
                        if(is_array($value)){
                            foreach($value as $text_key=>$item){
                                if(!empty($item)){
                                    ?>
                                    <span><input type="text" name="<?php echo $key;?>[]" value="<?php echo esc_attr($item);?>" style="width:70%;" /><a href="javascript:;" class="button del_image">删除</a></span>
                                <?php
                                }
                            }
                        }
                        ?>
                        <span><input type="text" name="<?php echo $key;?>[]" value="" id="<?php echo $key;?>" style="width:70%;" /><a class="wpjam_mulit_text button">添加选项</a> </span>
                        <?php
                        break;

                    case 'file':
                        ?>
                        <input type="file" id="<?php echo $key;?>" name='<?php echo $key;?>'>
                        <?php
                        if($file_id = get_post_meta($post->ID,$key,true)){
                            echo '已上传：'.wp_get_attachment_link($file_id);
                        }
                        break;
                    case 'checkbox':
                        ?>
                        <input type="checkbox" id="<?php echo $key;?>" name='<?php echo $key;?>' value="1" <?php checked($value,1);?>>
                        <input type="hidden" name="checkbox_<?php echo $key;?>" value="1">
                        <?php
                        break;
                    default:
                        ?>
                            <input type="text" name="<?php echo $key;?>" value="<?php echo esc_attr($value);?>" id="<?php echo $key;?>" style="width:70%;"  />
                        <?php
                        break;
                }

                if($wpjam_field['help']){
                    echo '<span class="help">'.$wpjam_field['help'].'</span>';
                }
                ?>
            </td>
        </tr>
    <?php
    }
    echo '</table>';
    ?>
    <script type="text/javascript">
        jQuery(function(){
            jQuery("form#post").attr('enctype','multipart/form-data');
        });
    </script>
<?php
}

add_action('admin_head', 'wpjam_post_options_box');
function wpjam_post_options_box() {
    $wpjam_options = wpjam_get_post_options();
    foreach($wpjam_options as $key=>$wpjam_option){
        foreach($wpjam_option['post_types'] as $post_type){
            add_meta_box($key, $wpjam_option['name'], 'wpjam_post_options_callback', $post_type, 'normal', 'high', array('meta_box'=>$key));
        }
    }
    //remove_meta_box('postcustom', 'post', 'normal');
}

/*
 * 图片上传js
 * author: Mark
 */
add_action('admin_footer', 'wpjam_upload_image_script');
function wpjam_upload_image_script(){
    ?>
    <script type="text/javascript">
        jQuery(function($){
            //上传单个图片
            $('.wpjam_upload').on("click",function(e) {
                var obj = $(this);
                e.preventDefault();
                var custom_uploader = wp.media({
                    title: '插入选项缩略图',
                    button: {
                        text: '选择图片'
                    },
                    multiple: false  // Set this to true to allow multiple files to be selected
                })
                    .on('select', function() {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        //var dataobj = '<img src="'+attachment.url+'"><div class="close">X</div>';
                        obj.prev("input").val(attachment.url)
                        obj.after(dataobj).hide();
                    })
                    .open();
            });
            // 添加多个选项
            var item =  '';

            $('body').on('click', 'a.wpjam_mulit_text', function(){
            	var value = $(this).prev().val();
            	var name = $(this).prev().attr("name");
            	var option = '<span><input type="text" name="'+name+'" value="'+value+'" style="width:70%;" /><a href="javascript:;" class="button del_image">删除</a></span>';
            	$(this).parent().before(option);
            	$(this).prev().val('');
				return false;
            });

            //上传多个图片
            var html = '';
            $('body').on('click', '.wpjam_mulit_upload', function(e) {
                var position = $(this).prev("input");
                var key_name = position.attr('name');
                var custom_uploader;
                var obj = $(this);
                var ids = new Array();
                e.preventDefault();
                if (custom_uploader) {
                    custom_uploader.open();
                    return;
                }
                custom_uploader = wp.media.frames.file_frame = wp.media({
                    title: 'Choose Image',
                    button: {
                        text: 'Choose Image'
                    },
                    multiple: true
                }).on('select', function() {
                    var data = custom_uploader.state().get('selection');
                    data.map( function( data ) {
                        data = data.toJSON();
                        value = data.url;
                        // console.log(data);
                        html = '<span><input type="text" name="'+key_name+'" value="'+value+'" style="width:70%;"  /><a href="javascript:;" class="button del_image">删除</a></span>';
                        position.before(html);
                    });
                    response = ids.join(",");
                    obj.prev().val(response);
                }).open();

                return false;
            });
            //  删除图片
            $('body').on('click', '.del_image', function(){
                $(this).parent().fadeOut(1000, function(){
                    $(this).remove();
                });
            });

            return false;
        });
    </script>
<?php
}


/*已经舍弃的函数*/
function wpjam_admin_display_form_table($form_fields){
 	?>
	<table class="form-table" cellspacing="0">
		<tbody>
			<?php foreach($form_fields as $form_field){ ?>
			<?php 
				$type		= $form_field['type'];
				$value		= $form_field['value'];

				$name		= $form_field['name'];

				$class		= isset($form_field['calss'])?$form_field['class']:'regular-text';
			?>
			<tr valign="top" id="tr_<?php echo $name; ?>">
				<th scope="row"><label for="<?php echo $form_field['name']; ?>"><?php echo $form_field['label'];?></label></th>
				<td>
				<?php if($type == 'text'){ ?>
					<input name="<?php echo $name;?>" id="<?php echo $name;?>" type="text"  value="<?php echo esc_attr($value); ?>" class="<?php echo $class; ?>" />
				<?php }elseif($type == 'datetime'){ ?>
					<input name="<?php echo $name;?>" id="<?php echo $name;?>" type="text"  value="<?php echo $value; ?>" class="<?php echo $class; ?>" />
				<?php }elseif($type == 'textarea'){ ?>
					<textarea name="<?php echo $name;?>" id="<?php echo $name;?>" rows="6" cols="50"  class="<?php echo $class; ?> code"><?php echo esc_textarea($value); ?></textarea>
				<?php }elseif ($type == 'hidden'){ ?>
					<input name="<?php echo $name;?>" id="<?php echo $name;?>" type="hidden"  value="<?php $value ;?>" />
				<?php }elseif ($type == 'checkbox'){ ?>
					<input name="<?php echo $name;?>" id="<?php echo $name;?>" type="checkbox"  value="<?php echo $value ?>" <?php echo $form_field['checked']; ?> /> 是否激活
				<?php }elseif ($type == 'select'){ ?>
					<select name="<?php echo $name;?>" id="<?php echo $name;?>"  >
					<?php foreach ($form_field['options'] as $option_value => $option_title){ $selected = ($option_value == $value)?'selected':''; ?>
						<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $option_title; ?></option>
					<?php }?>
					</select>
				<?php }elseif($type == 'file'){ ?>
					<input name="<?php echo $name;?>" id="<?php echo $name;?>" type="text"  value="<?php echo $value; ?>" class="<?php echo $class; ?>" /><input onclick="wpjam_media_upload('<?php $form_field['name']; ?>')" class="button button-highlighted" type="button" value="上传' <?php echo $form_field['label']; ?>" />
				<?php } ?>
				<?php if(isset($form_field['description'])) { ?><p class="description"><?php echo $form_field['description'];?></p><?php } ?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	<?php
}