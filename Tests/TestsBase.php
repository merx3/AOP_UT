<?php namespace Tests;

abstract class TestsBase extends \AOP_UT\DynamicTest
{
    protected static $listenerContent;

    public static function setUpBeforeClass()
    {
        file_put_contents('config/listener.ini', self::$listenerContent);
        include 'src/bootstrap.php';
    }

    public static function tearDownAfterClass()
    {
        unlink('config/listener.ini');
    }
}