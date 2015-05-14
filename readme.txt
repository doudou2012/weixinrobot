WXROBOT微信机器人 - WordPress plugin
- JS文件说明
    1、account/static/account.js   处理用户登录、注册相关页面逻辑
    2、account/static/sign.js      其他页面调用的 用户登录注册弹框 相关逻辑
    3、account/static/toast.min.js 提示框组件
    4、account/static/av-mini.js   leancloud JS SDK 主要用来发短信
    5、favorite/static/favorite.js 收藏前端页面逻辑处理JS
    6、photo/static/photo.js       微信JS-SDK相册处理JS

Version: 6.5.1
- BUG修复
    1、修正七牛CDN失效问题
    2、网页端收藏列表页退出逻辑
- 新加功能
    1、在微信机器人插件中，增加 打开和关闭拍照功能的设置
Version: 6.5.0
- 增加微信内收藏功能
    1、绑定过的用户输入ID查询后，自动加入收藏列表
    2、绑定用户在微信回复公众号 S，获取自己的列表
- 网站收藏功能
    1、文章详细页面可以收藏
    2、可以查看收藏列表
- 代码修改说明
    1、增加收藏模块 favorite
    2、加入第三方Jquery插件  static/alertifyjs
    3、在当前主题中，注入收藏的JS和样式文件 favorite/favorite-functions.php
Version: 6.1.0
- 修复微信插件激活的时候，数据表创建不成功的bug
- 修改类名
    1、WXBase->RequestHandler
    2、Photo->WPPhoto
    3、Account->WPAccount
- 修改文件名
    1、account.class.php->wpaccount.class.php
    2、photo.class.php->wpphoto.class.php
- 添加多文件上传的模板和调试处理方法

Version: 6.0.0
- 整合用户登录注册、拍照及浏览照片、自定义回复的功能。
- 增加了对WXBase类的单元测试代码。
    1、单元测试代码在unittest下面。
- 调整了页面样式，优化代码结构。

Version: 5.3.0
- 增加功能：使用微信JSSDK，实现拍照、照片浏览和分享功能
- 使用说明：
    1、在自定义回复中，增加自定义关键词，对应的处理函数为：take_photo
    2、在微信公众号会话中输入对应的关键词，会返回拍照链接，点击后，进入拍照应用
    3、使用本应用，需要登录状态
5.2.1
- 重构用户和微信绑定部分，后端代码
- 处理请求的方法
    $router = array('signon' => 'login',//登录页面和登录提交处理
                    'signup'=>'register',//注册页面和注册提交处理
                    'findpwd'=>'reset_pwd',//找回密码页面和提交处理,
                    'resetpwd' =>'reset_pwd',//修改密码和提交处理
                    'check_user'=>'check_exits_json',//检查用户是否存在 返回json (ajax）
                    'verify_sms_code'=>'verify_code_json',//远程验证码处理 返回json (ajax)
                    'welcome' => 'welcome', //欢迎页面
                    'wx_reg' => 'wx_bind',//微信用户绑定  页面和提交处理
                    'logout' => 'logout',   //退出

    目前提交处理中，都是使用ajax方式提交。check_user 为GET提交，其他都是POST提交.
    ajax提交返回数据格式为json
        成功：{"success":true,"data":[{key:value},...],"error":""}
        失败：{"success":false,"data":"","error":{"code":"xxx","msg":"xxx"}}

5.2.0
- 增加用户登录、注册、修改和找回密码功能
- 使用说明
    1、用户注册 http://domain/?signup
    2、用户登录 http://domain/?signon
    3、找回修改密码 http://domain/?findpwd

5.1.1
- 优化用户绑定、登陆、注册的代码及目录结构
- 修正部分bug
5.1.0
- 增加功能：
	1、微信用户手机号注册及绑定功能。
- 增加文件：
	1、account.class.php  处理用户登录注册机微信绑定的类
	2、weixin-robot-account.php 微信用户绑定回调及登录注册
	3、iclude/account-functions.php  处理用户相关函数

使用说明：
    get_credit  函数回复：获取积分。
    (未注册用户，提示输入手机号绑定，从而进入用户注册流程。)

未来待处理问题：
- 微信内置浏览器缓存问题，已绑定用户访问注册页面，不会走像已经绑定的提示信息。

5.0.0
- 基于微信机器人高级版4.3进行定制，去掉授权限定
- 增加函数回复：
    weixin_robot_lastest_week 上周展览
    weixin_robot_city_search  展览城市搜索
    weixin_robot_productid_search 搜索post meta中的wpcf-wechat_id自定义字段，返回link或文章
    （以上改动在weixin-robot-extent-hook.php)

未来待处理问题：
- 未经函数回复处理的，没法按wxrobot逐层往下，直到搜索或交由第三方。

=== 微信机器人高级版 ===
Contributors: denishua
Donate link: https://me.alipay.com/denishua
Tags: weixin,微信
Requires at least: 3.0
Tested up to: 3.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

微信机器人高级版 WordPress 插件。

== Description ==

微信机器人是我开发的一款 WordPress 插件，它功能很简单，就是将你的微信公众账号和你的 WordPress 博客联系起来，搜索到和用户发送信息匹配的日志，并自动回复用户，让你使用微信进行营销事半功倍。

== Installation ==

1. 上传到 `/wp-content/plugins/` 目录
2. 在后台插件菜单激活该插件
3. 然后设置
