<?php namespace AOP_UT;

require __DIR__ . '/../vendor/autoload.php';

use AOP_UT\Tests\Listener\DataListener;

$dl = new DataListener();
$dl->start();

$tm = new TestMe('dbConnectionString');
echo "RUN COMPLETE: " . $tm->advancedFunc(5) . PHP_EOL;
