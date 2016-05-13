<?php namespace AOP_UT\Tests;

use AOP_UT\Tests\DAL\DataFlowLogRepository;
use AOP_UT\Tests\Advice\AdviceManager;
use AOP_UT\Tests\Advice\MethodsAdvice;
use AOP_UT\Tests\DAL\DataFlowDirection;

abstract class DynamicTest extends \PHPUnit_Framework_TestCase
{
    protected $dataFlowRepo;
    protected $adviceManager;

    protected function setUp()
    {
        $this->dataFlowRepo = new DataFlowLogRepository();
        $this->adviceManager = new AdviceManager();
    }

    protected function unitTest($className, $methodName)
    {
        $dataFlow = $this->dataFlowRepo->getDataFlow(1);
        $methodLog = $dataFlow->getStartLog();
        $escapedClassName = '\\'.$className;
        $methodSignature = $className.'::'.$methodName.'()';
        $objectConstructor = null;

        $methodChecker = new \ReflectionMethod($escapedClassName, $methodName);
        $methodIsStatic = $methodChecker->isStatic();
        while ($methodLog != null) {
            if ($methodLog->functionSignature == $className.'::__construct()') {
                $objectConstructor = $methodLog;
            }
            if($methodLog->functionSignature == $methodSignature && $methodLog->flowDirection === DataFlowDirection::CALLING) {
                if($methodIsStatic){
                    $executionClosure = function($methodLog) use ($className, $methodName) {
                        return call_user_func($className . '::' . $methodName);
                    };
                    $this->executeMethodLog($methodLog, $executionClosure);
                } else {
                    $executionClosure = function($methodLog) use ($escapedClassName, $methodName, $objectConstructor) {
                        $testObj = new $escapedClassName($objectConstructor->data);
                        return call_user_func_array(array($testObj, $methodName), $methodLog->data);
                    };
                    $this->executeMethodLog($methodLog, $executionClosure);
                }
            }
            $methodLog = $methodLog->nextLog;
        }
    }

    // for later
    protected function integrationTest($signatures)
    {

    }

    private function executeMethodLog($methodLog, $executionClosure)
    {
        if($methodLog){
            $this->adviceManager->setStubsFor($methodLog, true);
            $output = $executionClosure($methodLog);
            $failures = MethodsAdvice::getFailures();
            if (!empty($failures)) {
                $this->fail(implode($failures, PHP_EOL));
            }
            $this->assertEquals($methodLog->getReturnLog()->data, $output);
            MethodsAdvice::tearDown();
        } else {
            throw new Exception('No method in logs to unit test found. ' .
                'This is considered a bug in the Unit testing framework');
        }
    }
}
