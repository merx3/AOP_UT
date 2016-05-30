<?php
ini_set('aop.enable', '0');

$functionSignatures = parse_ini_file('config/listener.ini')['signatures'];
foreach ($functionSignatures as $func) {
    aop_add_around($func, array('AOP_UT\Advice\MethodsAdvice', 'adviceAround'));
}
