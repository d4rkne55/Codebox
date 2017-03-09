<?php
$vars = array(
    1 => "Hallo Welt!\nIch bin's, Dennis. :\")  Das ist ein längerer Text, um einen Overflow zu provozieren..",
    'currYear' => 2017,
    'working' => true,
    'implemented' => false,
    'progress' => 100/3,
    'foo' => null
);

new Dumper($vars);