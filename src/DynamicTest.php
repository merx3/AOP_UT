<?php namespace AOP_UT;

use AOP_UT\DAL\DataFlowLogRepository;
use AOP_UT\Advice\AdviceManager;
use AOP_UT\Advice\MethodsAdvice;
use AOP_UT\DAL\DataFlowDirection;
use AOP_UT\FlowHelpers\FunctionDescription;

abstract class DynamicTest extends \PHPUnit_Framework_TestCase
{
    protected $dataFlowRepo;
    protected $adviceManager;

    protected function setUp()
    {
        $this->dataFlowRepo = new DataFlowLogRepository();
        $this->adviceManager = new AdviceManager();
    }

    protected function unitTest($className, $methodName, $verifyCallsOrder = false)
    {
        $functionDescriptions = array(new FunctionDescription($className, $methodName));
        $this->runTests($functionDescriptions, $verifyCallsOrder);
        return $this;
    }

    // TODO: implement exception testing
    // TODO: check how recursions are handled
    protected function integrationTest($testedFunctions, $verifyCallsOrder = false)
    {
        $functionDescriptions = array();
        foreach ($testedFunctions as $className => $functions) {
            foreach ($functions as $function) {
                $functionDescriptions[] = new FunctionDescription($className, $function);
            }
        }
        $this->runTests($functionDescriptions, $verifyCallsOrder);
        return $this;
    }

// TODO: documentation and unit testsing(yo dawg, I heard you like unit tests)
// TODO: use migrations instead of csv/yaml?
    private function runTests($testedFunctionDescriptions, $verifyCallsOrder = false)
    {
        $dataFlow = $this->dataFlowRepo->getDataFlow(1);
        $this->adviceManager->ignoreFunctionsInAdvice($testedFunctionDescriptions);
        $executionStartPoints = array();

        $dataLog = $dataFlow->getStartLog();
        while ($dataLog != null) {
            if ($this->isStartingPoint($dataLog, $testedFunctionDescriptions)) {
                $executionStartPoints[] = $dataLog;
            }
            $dataLog = $dataLog->nextLog;
        }
        $this->adviceManager->setStubsFor($dataFlow, $testedFunctionDescriptions, $verifyCallsOrder);
        $this->executeMethodLogs($executionStartPoints, $testedFunctionDescriptions);
    }

    protected function executeConstructors($classNames)
    {
        $constructorSignatures = array();
        foreach ($classNames as $className) {
            $classRefl = new \ReflectionClass($className);
            $constructorSignatures[] = $className . '::' .
                $classRefl->getConstructor()->name . '()';
        }
        $this->adviceManager->removeIgnoredFunctions($constructorSignatures);
        return $this;
    }

    private function executeMethodLogs($methodLogs, $testedFunctionDescriptions)
    {
        if ($methodLogs) {
            $this->adviceManager->startListener();
            foreach ($methodLogs as $methodLog) {
                $functionDescription = new FunctionDescription();
                $functionDescription->setSignature($methodLog->functionSignature);
                if ($functionDescription->isStatic()) {
                    $output = call_user_func($functionDescription->className . '::' . $functionDescription->methodName);
                } else {
                    $testObj = $this->createTestObject($functionDescription->className, $testedFunctionDescriptions);
                    $output = call_user_func_array(array($testObj, $functionDescription->methodName), $methodLog->data);
                }
                $failures = MethodsAdvice::getFailures();
                if (!empty($failures)) {
                    MethodsAdvice::tearDown();
                    $this->fail(implode($failures, PHP_EOL));
                }
                $this->assertEquals($methodLog->getReturnLog()->data, $output);
            }
            $this->adviceManager->stopListener();
            MethodsAdvice::tearDown();
        } else {
            throw new Exception('No method in logs to unit test found. ' .
                'This is considered a bug in the Unit testing framework');
        }
    }

    private function isStartingPoint($dataLog, $testedFunctionDescriptions)
    {
        // if a function's parent is also a tested function, then it's indirectly called
        // so it's not a start point
        if ($dataLog->flowDirection === DataFlowDirection::CALLING &&
            in_array($dataLog->functionSignature, $testedFunctionDescriptions) &&
            (!$dataLog->previousLog || !in_array($dataLog->previousLog->functionSignature, $testedFunctionDescriptions))) {
            return true;
        }
        return false;
    }

    private function createTestObject($className, $testedFunctionDescriptions)
    {
        $mockMethods = $this->getAllMethodsExcept($className, $testedFunctionDescriptions);
        $object = $this->getMockBuilder($className)
            ->setMethods($mockMethods)
            ->disableOriginalConstructor()
            ->getMock();
        return $object;
    }

    private function getAllMethodsExcept($className, $excludedFunctionDescriptions)
    {
        $classMethods = get_class_methods($className);
        $excludedMethods = array();
        foreach ($excludedFunctionDescriptions as $fd) {
            if ($fd->className == $className) {
                $excludedMethods[] = $fd->methodName;
            }
        }
        return array_diff($classMethods, $excludedMethods);
    }
}
