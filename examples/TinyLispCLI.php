<?php
require_once '../AutoLoader.php';

while (true) {
    echo "> ";
    try {
        $x = new TinyLisp();
        echo $x->run(readline()) . "\n";
    } catch (Exception $e) {
        echo "ERROR: {$e->getMessage()}\n";
    }
}
