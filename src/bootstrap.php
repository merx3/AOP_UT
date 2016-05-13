<?php

require __DIR__ . '/../vendor/autoload.php';

$functionSignatures = parse_ini_file(__DIR__ .'/../config/listener.ini')['signatures'];
foreach ($functionSignatures as $func) {
    aop_add_around($func, array('AOP_UT\Advice\MethodsAdvice', 'adviceAround'));
}
