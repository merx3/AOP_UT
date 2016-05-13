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

    public function __construct($dataFlowId, $functionSignature, $data, $flowDirection, $nextLog = null)
    {
        $this->dataFlowId = $dataFlowId;
        $this->functionSignature = $functionSignature;
        $this->data = $data;
        $this->flowDirection = $flowDirection;
        $this->nextLog = $nextLog;
    }

    public function getReturnLog()
    {
        $nextLog = $this->nextLog;
        while ($nextLog != null) {
            if ($this->functionSignature == $nextLog->functionSignature) {
                return $nextLog;
            }
            $nextLog = $nextLog->nextLog;
        }
        throw new Exception('No return log found of function ' . $this->functionSignature . ' in logs for data flow ' . $this->dataFlowId);
    }
}
