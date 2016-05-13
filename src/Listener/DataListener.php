<?php namespace AOP_UT\Listener;

use AOP_UT\DAL\DataFlowLogRepository;
use AOP_UT\DAL\DataFlowDirection;

class DataListener
{
    private $functionSignatures;
    private $dataRepository;

    public function __construct()
    {
        $this->functionSignatures = parse_ini_file(__DIR__ .'/../../config/listener.ini')['signatures'];
        $this->dataRepository = new DataFlowLogRepository();
    }

    public function start()
    {
        $dataRepo = $this->dataRepository;
        foreach ($this->functionSignatures as $signature) {
            aop_add_before($signature, function(\AopJoinPoint $ajp) use ($dataRepo){
                $dataRepo->saveParams(
                    1, $ajp->getPointcut(), $ajp->getArguments(), DataFlowDirection::CALLING
                );
            });
            aop_add_after($signature, function(\AopJoinPoint $ajp) use ($dataRepo){
                $dataRepo->saveParams(
                    1, $ajp->getPointcut(), $ajp->getReturnedValue(), DataFlowDirection::RETURNING
                );
            });
        }
    }


}
