<?php namespace AOP_UT\DAL;

class DataFlow
{
    /**
    * @param int
    */
    private $id;

    /**
    * @param DataFlowLog
    */
    private $startLog;

    public function __construct($flowId, $startingLog)
    {
        $this->id = $flowId;
        $this->startLog = $startingLog;
    }

    public function getStartLog()
    {
        return $this->startLog;
    }
}
