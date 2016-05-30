<?php namespace AOP_UT\Advice;

use AOP_UT\DAL\DataFlowLog;
use AOP_UT\DAL\DataFlowDirection;

class AdviceManager
{
    public function setStubsFor($dataFlow, $functionDescriptions, $verifyCalls = false)
    {
        $mockFunctions = array();
        $dataLog = $dataFlow->getStartLog();
        while ($dataLog != null) {
            if (in_array($dataLog->functionSignature, $functionDescriptions)) {
                $nextFunctionLog = $dataLog->nextLog;
                while ($nextFunctionLog && $nextFunctionLog->functionSignature != $dataLog->functionSignature) {
                    if (!in_array($nextFunctionLog->functionSignature, $functionDescriptions)) {
                        $mockFunctions[] = $nextFunctionLog;
                        $nextFunctionLog = $nextFunctionLog->getReturnLog()->nextLog;
                    } else {
                        $nextFunctionLog = $nextFunctionLog->nextLog;
                    }
                }
                $dataLog = $nextFunctionLog->nextLog;
            } else {
                $dataLog = $dataLog->nextLog;
            }
        }
        MethodsAdvice::setVerifyCallOrder($verifyCalls);
        MethodsAdvice::enqueueStubs($mockFunctions);
    }

    public function ignoreFunctionsInAdvice($functionDescriptions)
    {
        $ignoredCalls = array();
        foreach ($functionDescriptions as $funcDescription) {
            $classConstructor = $funcDescription->getClassConstructorSignature();
            if ($classConstructor) {
                $ignoredCalls[] = $classConstructor;
            }
            $ignoredCalls[] = $funcDescription->getSignature();
        }
        MethodsAdvice::setIgnoreCalls($ignoredCalls);
    }

    public function removeIgnoredFunctions($functionSignatures)
    {
        $ignoredCalls = MethodsAdvice::getIgnoreCalls();
        for ($i = 0; $i < count($ignoredCalls); $i++) {
            if (in_array($ignoredCalls[$i], $functionSignatures)) {
                unset($ignoredCalls[$i]);
            }
        }
        MethodsAdvice::setIgnoreCalls($ignoredCalls);
    }

    public function startListener()
    {
        ini_set('aop.enable', '1');
    }

    public function stopListener()
    {
        ini_set('aop.enable', '0');
    }
}
