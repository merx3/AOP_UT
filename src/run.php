<?php namespace AOP_UT;

require __DIR__ . '/../vendor/autoload.php';

use AOP_UT\Tests\Listener\DataListener;
//
// $dl = new DataListener();
// $dl->start();
//
// $tm = new TestMe('dbConnectionString');
// echo "RUN COMPLETE: " . $tm->advancedFunc(5) . PHP_EOL;

function testThis(){
    echo '121';
}

function someOtherTest(){
    echo 'meh...';
}

class MyAdvice
{
    public static $toChange =  "testesin \n";
    public static function adv()
    {
        file_put_contents('test.txt', self::$toChange, FILE_APPEND);
    }
}

aop_add_before('AOP_UT\\*()', array('AOP_UT\MyAdvice', 'adv'));
// aop_add_before('*()', array($asdf, 'adv'));

testThis();

MyAdvice::$toChange = "NOT testesin \n";

testThis();

someOtherTest();

MyAdvice::$toChange = 'Wooop ';

testThis();
testThis();

// var_dump(parse_ini_file('src/temp/test.ini'));
