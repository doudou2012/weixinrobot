<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/20
 * Time: 下午7:01
 */
define( 'THE_PLUGIN_DIR', dirname(__FILE__).'/../../');
require_once THE_PLUGIN_DIR.'../../wp-includes/class-wp-error.php';
require_once THE_PLUGIN_DIR . 'wxrobot/requesthandler.class.php';

class WXBaseTest extends PHPUnit_Framework_TestCase {

    public function testIsPost(){
        $base = new RequestHandler();
        $base->request_method = 'POST' ;
        $this->assertTrue($base->is_post(),'错误:应该是POST');
    }

    public function testIsGet(){
        $base = new RequestHandler();
        $base->request_method = 'GET' ;
        $this->assertTrue($base->is_get(),'错误:应该是GET');
    }

    public function testRenderJsonSuccess(){
        $base = new RequestHandler();
        $base->response_json = true;
        $base->response = 'test';
        $this->expectOutputString(json_encode(array('success'=>true,'data'=>$base->response,'error'=>'')));
        $base->output_response_json();
    }

    public function testRenderJsonFail(){
        $base = new RequestHandler();
        $base->response_json = true;
        $base->errors->add('test_code','单元测试错误');
        $this->expectOutputString(json_encode(array('success'=>false,'data'=>'','error'=>array('code'=>'test_code','msg'=>'单元测试错误'))));
        $base->output_response_json();
    }

}
 