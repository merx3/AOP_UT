<?php

$iniSettings = parse_ini_file('config/listener.ini');
$functionSignatures = $iniSettings['signatures'];
foreach ($functionSignatures as $func) {
    aop_add_around($func, array('AOP_UT\Advice\MethodsAdvice', 'adviceAround'));
}
