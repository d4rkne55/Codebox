<?php
echo $a;
$b = array();
echo $b;
echo $b["foo"];
strlen($b);
trigger_error('Non-existent error type', E_USER_STRICT);
trigger_error('Unknown Error', E_USER_WARNING);
#inexistentFunction();
trigger_error('Wut?!', E_USER_ERROR);
echo "Hello World!";