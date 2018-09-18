<?php
$vars = array(
    1 => "Hallo Welt!\nIch bin's, Dennis. :\")  Das ist ein längerer Text, um einen Overflow zu provozieren..",
    'currYear' => 2017,
    'working' => true,
    'implemented' => false,
    'progress' => 100/3,
    'foo' => null,
    'obj' => new stdClass(),
    'depth' => array(
        'foo',
        'works' => true
    )
);

new Dumper($vars);
new Dumper(null);
new Dumper(
'<div class="foo">
    <span>&copy; Copyright by D4rK&#9760;RuLLz</span>
</div>');