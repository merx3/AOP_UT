<?php namespace AOP_UT\Listener;

use AOP_UT\DAL\DataFlowLogRepository;
use AOP_UT\DAL\DataFlowDirection;

class DataListener
{
    private $functionSignatures;
    private $dataRepository;

    public function __construct()
    {
        $iniSettings = parse_ini_file('config/listener.ini');
        $this->functionSignatures = $iniSettings['signatures'];
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

    // Not Working, bug in AOP PHP
//    public function pause()
//    {
//        ini_set('aop.enable', '0');
//    }
//
//    public function resume()
//    {
//        ini_set('aop.enable', '1');
//    }
}
