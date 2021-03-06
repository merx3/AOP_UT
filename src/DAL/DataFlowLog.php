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

    public function __construct($dataFlowId, $functionSignature, $data, $flowDirection, $nextLog = null, $previousLog = null)
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
            $nextLog->flowDirection === DataFlowDirection::CALLING ? $callDepth++ : $callDepth--;
            if ($this->functionSignature == $nextLog->functionSignature && $callDepth == 0) {
                return $nextLog;
            }
            $nextLog = $nextLog->nextLog;
        }
        throw new Exception('No return log found of function ' . $this->functionSignature . ' in logs for data flow ' . $this->dataFlowId);
    }

    public function __toString()
    {
        $nextLog = $this->nextLog;
        $prevLog = $this->previousLog;
        $data = $this->data;
        $this->nextLog = null;
        $this->previousLog = null;
        $this->data = null;
        $stringVal = var_export($this, true);
        $this->nextLog = $nextLog;
        $this->previousLog = $prevLog;
        $this->data = $data;
        return $stringVal;
    }
}
