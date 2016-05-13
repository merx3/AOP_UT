<?php namespace AOP_UT;

use AOP_UT\DAL\DataFlowLogRepository;
use AOP_UT\Advice\AdviceManager;
use AOP_UT\Advice\MethodsAdvice;
use AOP_UT\DAL\DataFlowDirection;

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
        $dataFlow = $this->dataFlowRepo->getDataFlow(1);
        $methodLog = $dataFlow->getStartLog();
        $escapedClassName = '\\'.$className;
        $methodSignature = $className.'::'.$methodName.'()';
        $objectConstructor = null;

        $methodChecker = new \ReflectionMethod($escapedClassName, $methodName);
        $methodIsStatic = $methodChecker->isStatic();
        while ($methodLog != null) {
            $methodLogIsConstructor = $methodLog->functionSignature == $className.'::__construct()' ||
                    $methodLog->functionSignature == $className.'::' . $className . '()';
            if ($methodLogIsConstructor) {
                $objectConstructor = $methodLog;
            }
            if($methodLog->functionSignature == $methodSignature && $methodLog->flowDirection === DataFlowDirection::CALLING) {
                if($methodIsStatic){
                    $executionClosure = function($methodLog) use ($className, $methodName) {
                        return call_user_func($className . '::' . $methodName);
                    };
                    $this->executeMethodLog($methodLog, $executionClosure, $verifyCallsOrder);
                } else {
                    $executionClosure = function($methodLog) use ($escapedClassName, $methodName, $objectConstructor) {
                        $testObj = new $escapedClassName($objectConstructor->data);
                        return call_user_func_array(array($testObj, $methodName), $methodLog->data);
                    };
                    $this->executeMethodLog($methodLog, $executionClosure, $verifyCallsOrder);
                }
            }
            $methodLog = $methodLog->nextLog;
        }
    }

// TODO: documentation and unit testsing(yo dawg, I heard you like unit tests)
    protected function integrationTest($signaturePartsList, $verifyCallsOrder = false)
    {
        $dataFlow = $this->dataFlowRepo->getDataFlow(1);
        foreach ($signaturePartsList as $signatureParts) {
            $methodLog = $dataFlow->getStartLog();
            $escapedClassName = '\\'.$signatureParts[0];
            $methodSignature = $signatureParts[0].'::'.$signatureParts[1].'()';
            $objectConstructor = null;

            $methodChecker = new \ReflectionMethod($escapedClassName, $signatureParts[1]);
            $methodIsStatic = $methodChecker->isStatic();
            while ($methodLog != null) {
                $methodLogIsConstructor = $methodLog->functionSignature == $className.'::__construct()' ||
                        $methodLog->functionSignature == $className.'::' . $className . '()';
                if ($methodLogIsConstructor) {
                    $objectConstructor = $methodLog;
                }
                // This part is a bit more complicated than I thought, need to think over it later
                // if($methodLog->functionSignature == $methodSignature && $methodLog->flowDirection === DataFlowDirection::CALLING) {
                //     if($methodIsStatic){
                //         $executionClosure = function($methodLog) use ($className, $methodName) {
                //             return call_user_func($className . '::' . $methodName);
                //         };
                //         $this->executeMethodLog($methodLog, $executionClosure, $verifyCallsOrder);
                //     } else {
                //         $executionClosure = function($methodLog) use ($escapedClassName, $methodName, $objectConstructor) {
                //             $testObj = new $escapedClassName($objectConstructor->data);
                //             return call_user_func_array(array($testObj, $methodName), $methodLog->data);
                //         };
                //         $this->executeMethodLog($methodLog, $executionClosure, $verifyCallsOrder);
                //     }
                // }
                // $methodLog = $methodLog->nextLog;
            }
        }
    }

    private function executeMethodLog($methodLog, $executionClosure, $verifyCallsOrder)
    {
        if($methodLog){
            $this->adviceManager->setStubsFor($methodLog, $verifyCallsOrder);
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
