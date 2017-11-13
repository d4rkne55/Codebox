<?php
echo $a;
$b = array();
echo $b;
echo $b["foo"];
echo $b[1];
strlen($b);
trigger_error('Non-existent error type', E_USER_STRICT);
trigger_error('Unknown Error', E_USER_WARNING);
#inexistentFunction();
$class = new stdClass();
#$class->undefinedMethod();
trigger_error('Wut?!', E_USER_ERROR);
echo "Hello World!";