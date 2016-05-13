<?php namespace AOP_UT\Tests\Advice;

use AOP_UT\Tests\DAL\DataFlowLog;
use AOP_UT\Tests\DAL\DataFlowDirection;

class AdviceManager
{
    public function setStubsFor(DataFlowLog $functionLog, $verifyCalls = false) {
        $nextFunctionLog = $functionLog->nextLog;
        $mockFunctions = [];
        $callDepth = 0;
        while($nextFunctionLog->functionSignature != $functionLog->functionSignature) {
            $nextFunctionLog->flowDirection === DataFlowDirection::CALLING ? $callDepth++ : $callDepth--;
            if($callDepth == 1) {        
                $mockFunctions[] = $nextFunctionLog;
            }
            $nextFunctionLog = $nextFunctionLog->nextLog;
        }
        MethodsAdvice::setVerifyCallOrder($verifyCalls);
        MethodsAdvice::enqueueStubs($mockFunctions);
        MethodsAdvice::setIgnoreCalls(array($functionLog->functionSignature));
    }
}
