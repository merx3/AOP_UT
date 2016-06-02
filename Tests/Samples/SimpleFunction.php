<?php namespace Tests\Samples;

class SimpleFunction
{
    public function __construct($dbConnectionParams)
    {
        if($dbConnectionParams != 'doNotThrowException') {
            throw new \Exception('Constructor is not supposed to be called when unit testing');
        }
        // setup some db connections
    }

    public static function statFunc($a)
    {
        return $a * 2;
    }

    private function secondLevelFunc($a)
    {
        return $a;
    }

    public function normFunc($a)
    {
        $this->secondLevelFunc($a);
        return $a - 5;
    }

    private function helperFunc($a)
    {
        return $a + 8;
    }

    private function saveResult($a)
    {
        return true; // it's saved
    }

    public function advancedFunc($b)
    {
        $t = $this->normFunc($b);
        $t = $this->normFunc($t);
        $t = $this->helperFunc($t);
        $this->saveResult($t); // $b - 5 - 5 + 8 = $b - 2
        return true;
    }
}
