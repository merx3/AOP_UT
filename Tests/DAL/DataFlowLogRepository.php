<?php namespace AOP_UT\Tests\DAL;

class DataFlowLogRepository
{
    public function save(DataFlowLog $dataFlowLog)
    {

    }

    // TODO: replase csv with YAML
    public function saveParams($dataFlowId, $functionSignature, $data, $flowDirection)
    {
        $fp = fopen('log.csv', 'a');
        fputcsv($fp, [$dataFlowId, $functionSignature, serialize($data), $flowDirection]);
    }

    public function getDataFlow($dataflowId)
    {
        $dataFlowStart = null;
        $currentDataLog = null;
        $fp = fopen('log.csv', 'r');
        while (($data = fgetcsv($fp, 1000)) !== FALSE) {
            $dataFlowLog = new DataFlowLog((int)$data[0], $data[1], unserialize($data[2]), (int)$data[3]);
            if($dataFlowStart == null) {
                $dataFlowStart = $dataFlowLog;
            } else {
                $currentDataLog->nextLog = $dataFlowLog;
            }
            $currentDataLog = $dataFlowLog;
        }
        return new DataFlow(1, $dataFlowStart);
    }

    public function getAllDataFlows()
    {

    }

    // TODO: getDataFlowsForMethods- get all data flows that have specific methods in them
    //  distribute the data and the functions  so the data flow logs for a specific
    //  function can be easily found
}
