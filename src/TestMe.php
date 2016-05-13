<?php namespace AOP_UT;

class TestMe
{
    public function __construct($dbConnectionParams)
    {
        // setup some db connections
    }

    public static function statFunc($a)
    {
        return $a * 2;
    }

    public function normFunc($a)
    {
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
