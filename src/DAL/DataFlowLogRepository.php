<?php namespace AOP_UT\DAL;

class DataFlowLogRepository
{
    public function saveParams($dataFlowId, $functionSignature, $data, $flowDirection)
    {
        $fp = fopen('log.csv', 'a');
        fputcsv($fp, array($dataFlowId, $functionSignature, serialize($data), $flowDirection));
    }

    // TODO: improve logging file structure
    public function getDataFlow($dataFlowId)
    {
        $dataFlowStart = null;
        $currentDataLog = null;
        $previousDataLog = null;
        $fp = fopen('log.csv', 'r');
        while (($data = fgetcsv($fp, 1000)) !== FALSE) {
            $dataFlowLog = new DataFlowLog((int)$data[0], $data[1], unserialize($data[2]), (int)$data[3]);
            if($dataFlowStart == null) {
                $dataFlowStart = $dataFlowLog;
            } else {
                $currentDataLog->nextLog = $dataFlowLog;
                $dataFlowLog->previousLog = $currentDataLog;
            }
            $currentDataLog = $dataFlowLog;
        }
        return new DataFlow(1, $dataFlowStart);
    }

    // TODO: getDataFlowsForMethods- get all data flows that have specific methods in them
    //  distribute the data and the functions  so the data flow logs for a specific
    //  function can be easily found
    public function getDataFlowsForMethods()
    {

    }
}
