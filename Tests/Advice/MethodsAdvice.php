<?php namespace AOP_UT\Tests\Advice;

use AOP_UT\Tests\DAL\DataFlowDirection;
use AOP_UT\Tests\DAL\DataFlowLog;

class MethodsAdvice
{
    // the $stubs is an array(of DataFlowLog)
    // with the stubbed functions only
    private static $stubs = array();
    private static $failures = array();
    private static $verifyCallOrder = false;
    private static $ignoreCalls = array();

    public static function adviceAround(\AopJoinPoint $joinPoint)
    {
        $functionSignature = $joinPoint->getPointcut();
        if(!self::isStubbingNeeded($functionSignature, $joinPoint)){
            return;
        }
        if (self::$verifyCallOrder) {
            self::executeNextStub($joinPoint);
        } else {
            if(!self::executeMatchingStub($joinPoint, $functionSignature)) {
                $joinPoint->process(); // normal method run since no stub found
            }
        }
    }

    public static function setVerifyCallOrder($verifyCallOrder = false)
    {
        self::$verifyCallOrder = $verifyCallOrder;
    }

    public static function getVerifyCallOrder($verifyCallOrder = false)
    {
        return self::$verifyCallOrder;
    }

    public static function setIgnoreCalls($ignoreCalls = array())
    {
        self::$ignoreCalls = $ignoreCalls;
    }

    public static function getIgnoreCalls($ignoreCalls = array())
    {
        return self::$ignoreCalls;
    }

    public static function enqueueStubs($dataFlowLogs)
    {
        self::$stubs = array_merge(self::$stubs, $dataFlowLogs);
    }

    public static function getFailures()
    {
        return self::$failures;
    }

    public static function tearDown()
    {
        self::$stubs = array();
        self::$failures = array();
        self::$verifyCallOrder = false;
        self::$ignoreCalls = array();
    }

    private static function isStubbingNeeded($functionSignature, $joinPoint)
    {
        if (in_array($functionSignature, self::$ignoreCalls)) {
            $joinPoint->process();
            return false;
        }
        if (empty(self::$stubs)) {
            if(self::$verifyCallOrder){
                throw new AdviceException('Method stubs array is empty but call to method ' . $functionSignature . ' expected');
            }
            $joinPoint->process(); // normal method run since no stubs exist
            return false;
        }
        if (self::$verifyCallOrder && self::$stubs[0]->functionSignature != $functionSignature) {
            throw new AdviceException('Next stub does not match called method(Expected: ' .
                self::$stubs[0]->functionSignature . '; Called: ' . $functionSignature . ')');
        }
        return true;
    }

    private static function executeNextStub($joinPoint)
    {
        $nextStub = array_shift(self::$stubs);
        if($nextStub->data !== $joinPoint->getArguments()) {
            array_push(self::$failures, 'Expected call to ' . $nextStub->functionSignature . ' with arguments ' .
                var_export($nextStub->data, true) . ', but called with arguments ' .
                var_export($joinPoint->getArguments(), true));
        }
        $joinPoint->setReturnedValue($nextStub->getReturnLog()->data);
    }

    private static function executeMatchingStub($joinPoint, $searchForSignature)
    {
        $searchForData = $joinPoint->getArguments();
        for ($i=0; $i < count(self::$stubs); $i++) {
            $stubMatch = self::$stubs[$i]->functionSignature == $searchForSignature &&
                self::$stubs[$i]->data === $searchForData;
            if ($stubMatch) {
                $joinPoint->setReturnedValue(self::$stubs[$i]->getReturnLog()->data);
                return true;
            }
        }
        return false;
    }
}
