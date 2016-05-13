<?php namespace AOP_UT;

class TestMe2
{
    private $someParams;

    public function __construct($params)
    {
        $this->someParams = $params;
    }

    public function normFunc($a)
    {
        return $a * 2;
    }

    private function setParams($a)
    {
        $this->params = $a;
    }

    private function saveResult($a)
    {
        return true; // it's saved
    }

    // TODO: mocking and logging raised exceptions
    public function throwExc()
    {
        throw new Exception("Error Processing Request");
    }

    private function endIt()
    {
        die('killed mid run');
    }

    public function advancedFunc($b)
    {
        $k = $this->normFunc(3);
        $this->setParams([0 => $k]);
        $this->saveResult($k);
        $this->throwExc();
    }

    public function advancedFunc2($b)
    {
        $k = $this->normFunc(3);
        $this->endIt();
        $k = $this->normFunc(6);
    }
}
