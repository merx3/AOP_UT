<?php namespace AOP_UT;

class SampleDynamicTest extends DynamicTest
{
    public function testDynamically_advancedFunc()
    {
        $this->unitTest('AOP_UT\TestMe', 'advancedFunc');
    }
}
