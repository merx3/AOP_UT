<?php namespace AOP_UT\Tests;

class SampleTest extends \PHPUnit_Framework_TestCase
{
    public function testStatic()
    {
        // $dl = new AOP_UT\Tests\Listener\DataListener();
        // $dl->start();
        $tm = new \AOP_UT\TestMe("dbConnectionString");
        $this->assertTrue($tm->advancedFunc(5));
    }
}
