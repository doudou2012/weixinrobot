<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/12
 * Time: 下午2:21
 * Desc: 处理请求的基类
 */
if (!class_exists('RequestHandler')){
    class RequestHandler {

        /**
         * @var WP_Error errors
         */
        protected  $errors;
        /**
         * @var string json 响应数据
         */
        protected  $response = '';
        /**
         * @var bool 是否为json返回
         */
        protected  $response_json = false;
        /**
         * @var array 简单路由配置
         */
        protected  $router = array();
        /**
         * @var string 当前请求的处理方法
         */
        private $action = 'index';
        /**
         * @var string 请求方式
         */
        protected $request_method = 'GET';

        private static $_instance;
        /**
         * @param array $router 路由配置
         */
        public function __construct($router = array())
        {
            $this->errors = new WP_Error();
            $this->router = $router;
            if (!empty($this->router)){
                foreach (array_keys($this->router) as $v) {
                    if (isset($_GET[$v])) {
                        $this->action = $v;
                        break;
                    }
                }
            }
            $this->request_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            if (isset($_REQUEST['ajax'])){
                $this->response_json = true;
            }
        }

        public static function getInstance($router = array()){
            if(! (self::$_instance instanceof self) )
            {
                self::$_instance = new self($router);
            }
            return self::$_instance;
        }

        public function request_handler(){
            if ($this->action){
                $method = $this->router[$this->action];
                if (!$method ) $method = $this->action;
                if (method_exists($this,$method)){
                    $this->$method();
                    if ($this->response_json){
                        $this->render_json();
                    }
                    exit();
                }
            }
        }

        public function __call($name, $arguments)
        {
            $action = substr($name, 0, 3);
            switch ($action) {
                case 'get':
                    $property = strtolower(substr($name, 3));
                    if (property_exists($this, $property)) {
                        return $this->{$property};
                    } else {
                        return false;
                    }
                    break;
                case 'set':
                    $property = strtolower(substr($name, 3));
                    if (property_exists($this, $property)) {
                        $this->{$property} = $arguments[0];
                    } else {
                        return false;
                    }
                    break;
                default :
                    return false;
            }
        }
        public function __get($name){
            if(property_exists($this,$name)){
                return $this->$name;
            }
        }
        public function __set($name,$value){
            if(property_exists($this,$name)){
                $this->$name = $value;
            }
        }


        public function output_response_json(){
            if ($code = $this->errors->get_error_code()){
                $msg = $this->errors->get_error_message();
                if (in_array($code,array('invalid_username','incorrect_password'))) {
                    $msg = '您的用户名和密码不正确';
                }
                return json_encode(array('success'=>false,'data'=>'','error'=>array('code'=>$code,'msg'=>$msg)));
            }else{
                return json_encode(array('success'=>true,'data'=>$this->response,'error'=>''));
            }
        }

        public function render_json(){
            header('Content-type: application/json');
            echo $this->output_response_json();
        }

        public function is_post(){
            return $this->request_method == 'POST';
        }

        public function is_get(){
            return $this->request_method == 'GET';
        }
    }
}
