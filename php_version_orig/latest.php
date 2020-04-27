<?php

$f = file_get_contents("data");

$measurements = array_reverse(explode("\n", $f));

echo $measurements[1];
