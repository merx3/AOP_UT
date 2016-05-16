<?php namespace Tests;

use AOP_UT\Listener\DataListener;
use Tests\Samples\SimpleFunction;
use AOP_UT\DAL\DataFlowDirection;

class DataListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listenerContent;

    public function setUp()
    {
        $this->listenerContent = '[function_signatures]' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::__construct()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::statFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::normFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::helperFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::advancedFunc()"' . PHP_EOL
        ;

        file_put_contents('config/listener.ini', $this->listenerContent);
        file_put_contents('log.csv', '');
    }

    /**
     * @runInSeparateProcess
     */
    public function testSimpleFunctionListener()
    {
        $dl = new DataListener();
        $dl->start();
        $funcObj = new SimpleFunction('dbConnectionString');
        $funcObj->advancedFunc(11);
        $logs = file_get_contents('log.csv');
        $expectedLogs = $this->generateListenerEntries(1,
            array('Tests\Samples\SimpleFunction' => array(
                array('__construct()', array("dbConnectionString"), DataFlowDirection::CALLING),
                array('__construct()', null, DataFlowDirection::RETURNING),
                array('advancedFunc()', array(11), DataFlowDirection::CALLING),
                array('normFunc()', array(11), DataFlowDirection::CALLING),
                array('normFunc()', 6, DataFlowDirection::RETURNING),
                array('normFunc()', array(6), DataFlowDirection::CALLING),
                array('normFunc()', 1, DataFlowDirection::RETURNING),
                array('helperFunc()', array(1), DataFlowDirection::CALLING),
                array('helperFunc()', 9, DataFlowDirection::RETURNING),
                array('advancedFunc()', true, DataFlowDirection::RETURNING),
            ))
        );
        $this->assertEquals($logs, $expectedLogs);
    }

    private function generateListenerEntries($dataFlowId, $functionSignatures)
    {
        $logEntry = '';
        foreach ($functionSignatures as $fClass => $fMethods) {
            foreach ( $fMethods as $fMethod) {
                $logEntry .= $this->str_putcsv([$dataFlowId, $fClass . '::' . $fMethod[0], serialize($fMethod[1]), $fMethod[2]]) . PHP_EOL;
            }
        }
        return $logEntry;
    }

    private function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        // Open a memory "file" for read/write...
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = fread($fp, 1048576);
        fclose($fp);
        return rtrim($data, "\n");
    }

    public function tearDown()
    {
        unlink('log.csv');
        unlink('config/listener.ini');
    }
}