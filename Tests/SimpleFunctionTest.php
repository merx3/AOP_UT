<?php namespace Tests;

class SimpleFunctionTests extends TestsBase
{
    public function __construct()
    {
        self::$listenerContent = '[function_signatures]' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::__construct()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::statFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::normFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::helperFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::advancedFunc()"' . PHP_EOL
        ;
    }


    public function testUnitTesting_advancedFunc_callOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/simpleFunctionLogs_normal.csv');
        $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc', true);
        unlink('log.csv');
    }

    public function testUnitTesting_advancedFunc_noCallOrder()
    {
        $this->loadTestLog('Tests/SampleLogs/simpleFunctionLogs_normal.csv');
        $this->unitTest('Tests\Samples\SimpleFunction', 'advancedFunc');
        unlink('log.csv');
    }

    private function loadTestLog($filePath)
    {
        $logContent = file_get_contents($filePath);
        file_put_contents('log.csv', $logContent);
    }


//      for negative testing:
//    /**
//     * @expectedException PHPUnit_Framework_AssertionFailedError
//     */
}