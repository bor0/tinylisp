<?php
include_once '../AutoLoader.php';

$programs = array();

$programs['map-impl'] = "
(begin
  (define map
    (lambda (x y)
      (if
       (eq? (cdr y) (quote ()))
       (cons (x (car y)) (quote ()))
       (cons (x (car y)) (map x (cdr y)))
      )
    )
  )
  (print (map
    (lambda (x) (+ x 1))
    (quote (1 2 3))
  ))
)
";

$programs['factorial'] = "
(begin
  (define fact (lambda (n) (if (<= n 1) 1 (* n (fact (- n 1))))))
  (print (fact 10))
)
";

$programs['range'] = "
(begin
  (define range (lambda (a b) (if (= a b) (quote ()) (cons a (range (+ a 1) b)))))
  (print (range 0 10))
)
";

$env = array(
    '*' => function ($args) {
        return (floatval($args[0]) * floatval($args[1]));
    },

    '-' => function ($args) {
        return (floatval($args[0]) - floatval($args[1]));
    },

    '<=' => function ($args) {
        return (floatval($args[0]) <= floatval($args[1]));
    },

    '+' => function ($args) {
        return (floatval($args[0]) + floatval($args[1]));
    },

    '=' => function ($args) {
        return $args[0] == $args[1];
    },
);

$tl = new TinyLisp();

foreach ($programs as $programName => $code) {
    echo "Running program $programName...\n";
    $tl->run($code, $env);
    echo "\n";
}
