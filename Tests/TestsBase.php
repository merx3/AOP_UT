<?php namespace Tests;

abstract class TestsBase extends \PHPUnit_Framework_TestCase
{
    protected $listenerContent;

    public function setUp()
    {
        file_put_contents('config/listener.ini', $this->listenerContent);
        $functionSignatures = parse_ini_file('config/listener.ini')['signatures'];
        foreach ($functionSignatures as $func) {
            aop_add_around($func, array('AOP_UT\Advice\MethodsAdvice', 'adviceAround'));
        }

    }

    public function tearDown()
    {
        unlink('config/listener.ini');
    }
}