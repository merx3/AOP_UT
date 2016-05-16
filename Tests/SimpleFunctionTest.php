<?php namespace Tests;

class SimpleFunctionTests extends TestsBase
{
    public function __construct()
    {
        $this->listenerContent = '[function_signatures]' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::__construct()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::statFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::normFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::helperFunc()"' . PHP_EOL .
            'signatures[] = "Tests\Samples\SimpleFunction::advancedFunc()"' . PHP_EOL
        ;
    }

    public function testSimple()
    {
        $this->assertTrue(true);
    }
}