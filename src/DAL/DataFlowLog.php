<?php namespace AOP_UT\DAL;

class DataFlowLog
{
    /**
    * @param int
    */
    public $dataFlowId;

    /**
    * @param string
    */
    public $functionSignature;

    /**
    * @param array
    */
    public $data;

    /**
    * @param array
    */
    public $flowDirection;

    /**
    * @param DataFlowLog
    */
    public $nextLog;

    /**
     * @param DataFlowLog
     */
    public $previousLog;

    public function DataFlowLog($dataFlowId, $functionSignature, $data, $flowDirection, $nextLog = null, $previousLog = null)
    {
        $this->dataFlowId = $dataFlowId;
        $this->functionSignature = $functionSignature;
        $this->data = $data;
        $this->flowDirection = $flowDirection;
        $this->nextLog = $nextLog;
        $this->previousLog = $previousLog;
    }

    public function getReturnLog()
    {
        $nextLog = $this->nextLog;
        $callDepth = 1;
        while ($nextLog != null) {
            if ($this->functionSignature == $nextLog->functionSignature) {
                $nextLog->flowDirection === DataFlowDirection::CALLING ? $callDepth++ : $callDepth--;
                if ($callDepth == 0) {
                    return $nextLog;
                }
            }
            $nextLog = $nextLog->nextLog;
        }
        throw new Exception('No return log found of function ' . $this->functionSignature . ' in logs for data flow ' . $this->dataFlowId);
    }
}
