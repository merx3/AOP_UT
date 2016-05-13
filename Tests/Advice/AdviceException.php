<?php namespace AOP_UT\Tests\Advice;

class AdviceException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
