<?php
/**
 * Created by PhpStorm.
 * File: WPFavoriteTest.php
 * User: user
 * Date: 15/1/26
 * Time: 下午12:38
 */
require_once dirname(__FILE__).'/bootstrap.php';
require_once TEST_PLUGIN_DIR . 'wxrobot/favorite/wpfavorite.class.php';

class WPFavoriteTest extends BaseTest {

    protected  static $fav;
    protected  static $postId = 330;
    protected  static $uid = 8;

    public static function setUpBeforeClass(){
        parent::setUpBeforeClass();
        self::$fav = new WPFavorite();
        self::$fav->postId = self::$postId;
        self::$fav->userId = self::$uid;
        self::$fav->response_json = false;
        self::$fav->postType = 1;
    }
    /**
     * 添加测试
     */
    public function testAdd(){

        $stub = $this->getMockBuilder('WPFavorite')
            ->getMock();
        $stub->method('isLogin')
            ->willReturn(true);

        $this->assertTrue($stub->isLogin());
        self::$fav->postId = 1644;
        $this->assertTrue(self::$fav->add(),'错误，添加失败');
    }

    public function testMyList(){
        $ids = (array) self::$fav->getFavsPostIds();
        $cnt = self::$fav->getCountMyFavs();
        $list = (array) self::$fav->getMyFavList();

        $this->assertCount($cnt,$list,'错误，取到的数量不对Count:'.$cnt.' list :'.count($list));
        //检查数量
        $this->assertCount($cnt,$ids,'错误，取到的数量不对Count:'.$cnt.' ids :'.count($ids));
    }

    public function testCheckFav(){
        self::$fav->postId = 1635;
        $this->assertTrue(self::$fav->check_fav(),'错误：已经收藏了');

        self::$fav->postId = 1630;
        $this->assertFalse(self::$fav->check_fav(),'错误：还没有收藏');
    }

    public function testDel(){
        $this->assertTrue(self::$fav->del(),'错误：删除收藏失败');
    }

//    public function testIncrease(){
//        $oldFavs = self::$fav->getFavCounts();
//        $this->assertTrue(self::$fav->increase());
//        $this->assertEquals($oldFavs+1,self::$fav->getFavCounts());
//    }
}
