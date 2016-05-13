<?php namespace AOP_UT\Advice;

use AOP_UT\DAL\DataFlowLog;
use AOP_UT\DAL\DataFlowDirection;

class AdviceManager
{
    public function setStubsFor(DataFlowLog $functionLog, $verifyCalls = false) {
        $nextFunctionLog = $functionLog->nextLog;
        $mockFunctions = [];
        $callDepth = 0;
        $functionElements = preg_split('/::|\(\)/',  $functionLog->functionSignature);
        $methodChecker = new \ReflectionMethod('\\' . $functionElements[0], $functionElements[1]);
        $methodIsStatic = $methodChecker->isStatic();
        if ($methodIsStatic) {
            MethodsAdvice::setIgnoreCalls(array($functionLog->functionSignature));
        } else {
            MethodsAdvice::setIgnoreCalls(array($functionLog->functionSignature,
                $functionElements[0] . '::__construct()'));
        }
        while ($nextFunctionLog->functionSignature != $functionLog->functionSignature) {
            $nextFunctionLog->flowDirection === DataFlowDirection::CALLING ? $callDepth++ : $callDepth--;
            if ($callDepth == 1) {
                $mockFunctions[] = $nextFunctionLog;
            }
            $nextFunctionLog = $nextFunctionLog->nextLog;
        }
        MethodsAdvice::setVerifyCallOrder($verifyCalls);
        MethodsAdvice::enqueueStubs($mockFunctions);
    }
}
