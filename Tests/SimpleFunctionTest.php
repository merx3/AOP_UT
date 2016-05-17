<?php namespace Tests;

class SimpleFunctionTests extends TestsBase
{
    public function __construct()
    {
        self::$listenerContent = '[function_signatures]' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::__construct()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::statFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::normFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::secondLevelFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::helperFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::advancedFunc()"' . PHP_EOL
        ;
    }



    public function testUnitTesting_callOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/normal.csv');
        $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc', true);
    }

    public function testUnitTesting_noCallOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/normal.csv');
        $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc');
    }

    public function testIntegrationTest_callOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/normal.csv');
        $this->integrationTest(array(
            'Tests\Samples\SimpleFunction' => array('advancedFunc', 'normFunc')), true);
    }

    public function testIntegrationTest_noCallOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/normal.csv');
        $this->integrationTest(array(
            'Tests\Samples\SimpleFunction' => array('advancedFunc', 'normFunc')), true);
    }

    public function testUnitTesting_wrongCallParameters()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/wrongNormFuncCallParameters.csv');
        $noFailure = false;
        try{
            $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc', true);
            $noFailure = true;
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $expectedMessage =
'Expected call to Tests\Samples\SimpleFunction::normFunc() with arguments array (
  0 => 2,
), but called with arguments array (
  0 => 6,
)';
            $this->assertEquals($e->getMessage(), $expectedMessage);
        }
        if($noFailure) {
            $this->fail('AOP Test should have failed with a phpunit exception');
        }
    }

    public function testUnitTesting_wrongCallParameters_noCallOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/wrongNormFuncCallParameters.csv');
        // No error should be thrown because the function is called without verifying parameter call order, so
        // it just executes the function since it's missing in the logs
        $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc');
    }

    public function testUnitTesting_wrongReturnValue()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/wrongReturnValue.csv');
        $noFailure = false;
        try{
            $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc', true);
            $noFailure = true;
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $expectedMessage =
'Expected call to Tests\Samples\SimpleFunction::normFunc() with arguments array (
  0 => 6,
), but called with arguments array (
  0 => 3,
)';
            $this->assertEquals($e->getMessage(), $expectedMessage);
        }
        if($noFailure) {
            $this->fail('AOP Test should have failed with a phpunit exception');
        }
    }

    public function testIntegrationTesting_wrongCallParametersInTested()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/wrongNormFuncCallParameters.csv');
        try{
            $this->integrationTest(array(
                'Tests\Samples\SimpleFunction' => array('advancedFunc', 'normFunc')), true);
         } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $this->fail('AOP Test should have NOT failed when integrated function\'s arguments have differences in logs and in execution');
        }
    }

    public function testIntegrationTesting_wrongCallParametersInStubs()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/wrongHelperFuncCallParameters.csv');
        try{
            $this->integrationTest(array(
                'Tests\Samples\SimpleFunction' => array('advancedFunc', 'normFunc')), true);
        } catch (\PHPUnit_Framework_AssertionFailedError $e) {
            $expectedMessage =
'Expected call to Tests\Samples\SimpleFunction::helperFunc() with arguments array (
  0 => 4,
), but called with arguments array (
  0 => 1,
)';
            $this->assertEquals($e->getMessage(), $expectedMessage);
        }
    }

    public function testUnitTesting_multipleStartPoints()
    {
        $this->loadTestLog('Tests/SampleLogs/SimpleFunction/wrongNormFuncCallParameters.csv');
        // No error should be thrown because the function is called without verifying parameter call order, so
        // it just executes the function since it's missing in the logs
        $this->unitTest('Tests\Samples\SimpleFunction', 'normFunc');
    }



    private function loadTestLog($filePath)
    {
        $logContent = file_get_contents($filePath);
        file_put_contents('log.csv', $logContent);
    }

    protected function tearDown()
    {
        unlink('log.csv');
    }
}