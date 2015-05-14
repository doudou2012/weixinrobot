<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 15/1/20
 * Time: 下午7:17
 */

define( 'ABSPATH', dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/');
define('TEST_PLUGIN_DIR',ABSPATH.'wp-content/plugins/');
require_once dirname(__FILE__).'/unittest_config.php';
require_once ABSPATH . 'wp-settings.php';


class BaseTest extends PHPUnit_Framework_TestCase {
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        PHPUnit_Framework_Error_Warning::$enabled = FALSE;
        PHPUnit_Framework_Error_Notice::$enabled = FALSE;
    }
}
