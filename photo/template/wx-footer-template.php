<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/12
 * Time: 下午3:01
 */
global $photo;
$signPackage = $photo->signPackage;
?>
    </div><!-- container end -->
<script type="text/javascript" src="http://cdn.bootcss.com/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo WEIXIN_ROBOT_PLUGIN_URL;?>/static/template.js"></script>
<script type="text/javascript" src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript" src="<?php echo WEIXIN_ROBOT_PLUGIN_URL;?>/account/static/toast.min.js"></script>
<script type="text/javascript" src="<?php echo WEIXIN_ROBOT_PLUGIN_URL;?>/static/tools.js"></script>
<script type="text/javascript" src="<?php echo WEIXIN_ROBOT_PLUGIN_URL;?>/photo/static/tpl.js?v=1.0"></script>
<script type="text/javascript">
    wx.config({
        debug: false,
        appId: '<?php echo $signPackage["appId"];?>',
        timestamp: <?php echo $signPackage["timestamp"];?>,
        nonceStr: '<?php echo $signPackage["nonceStr"];?>',
        signature: '<?php echo $signPackage["signature"];?>',
        jsApiList: [
            'chooseImage',
            'previewImage',
            'uploadImage',
            'downloadImage',
            'openLocation',
            'getLocation'
        ]
    });
    var photoSavedKey = 'album_photos';
    wx.error(function(res){
        danger_toast(res.errMsg);
    });
    wx.ready(function () {
    });
</script>
<script type="text/javascript" src="<?php echo WEIXIN_ROBOT_PLUGIN_URL;?>/photo/static/photo.js?v=1.1.0"></script>
</body>
</html>