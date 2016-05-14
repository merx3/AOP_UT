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
        $this->testFunctions($functionDescriptions,$verifyCallsOrder);
        return this;
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
        $this->testFunctions($functionDescriptions,$verifyCallsOrder);
        return this;
    }

// TODO: documentation and unit testsing(yo dawg, I heard you like unit tests)
    private function testFunctions($testedFunctionDescriptions, $verifyCallsOrder = false)
    {
        $dataFlow = $this->dataFlowRepo->getDataFlow(1);
        $this->adviceManager->ignoreFunctionsInAdvice($testedFunctionDescriptions);
        $executionStartPoints = array();
        $constructorLogs = array();

        $dataLog = $dataFlow->getStartLog();
        while ($dataLog != null) {
            if ($this->isStartingPoint($dataLog, $testedFunctionDescriptions)) {
                $executionStartPoints[] = $dataLog;
            } else  {
                $functionDescription = new FunctionDescription();
                $functionDescription->setSignature($dataLog->functionSignature);
                if ($functionDescription->isConstructor()) {
                    $constructorLogs[$functionDescription->className] = $dataLog;
                }
            }
            $dataLog = $dataLog->nextLog;
        }
        $this->adviceManager->setStubsFor($dataFlow, $testedFunctionDescriptions, $verifyCallsOrder);
        $this->executeMethodLogs($executionStartPoints, $constructorLogs);
    }

    protected function stubConstructors($classNames)
    {
        $constructorSignatures = array();
        foreach ($classNames as $className) {
            $classRefl = new \ReflectionClass($className);
            $constructorSignatures[] = $className . '::' .
                $classRefl->getConstructor()->name . '()';
        }
        $this->adviceManager->removeIgnoredFunctions($constructorSignatures);
        return this;
    }

    private function executeMethodLogs($methodLogs, $constructorLogs)
    {
        if ($methodLogs) {
            foreach ($methodLogs as $methodLog) {
                $functionDescription = new FunctionDescription();
                $functionDescription->setSignature($methodLog->functionSignature);
                if ($functionDescription->isStatic()) {
                    $output = call_user_func($functionDescription->className . '::' . $functionDescription->methodName);
                } else {
                    $escapedClassName = '\\' . $functionDescription->className;
                    $objectConstructor = $constructorLogs[$functionDescription->className];
                    $testObj = new $escapedClassName($objectConstructor->data);
                    $output = call_user_func_array(array($testObj, $functionDescription->methodName), $methodLog->data);
                }
                $failures = MethodsAdvice::getFailures();
                if (!empty($failures)) {
                    $this->fail(implode($failures, PHP_EOL));
                }
                $this->assertEquals($methodLog->getReturnLog()->data, $output);
                MethodsAdvice::tearDown();
            }
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
            !in_array($dataLog->previousLog->functionsignature, $testedFunctionDescriptions)) {
            return true;
        }
        return false;
    }
}
