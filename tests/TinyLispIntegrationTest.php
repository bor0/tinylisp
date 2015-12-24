<?php
/**
 * Integration tests for the project
 * @author bsitnikovski
 */
class TinyLispIntegrationTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tl = new TinyLisp();
        $this->tl->initEnvironment(array(
            '*' => function ($args) {
                return (floatval($args[0]) * floatval($args[1]));
            },

            '-' => function ($args) {
                return (floatval($args[0]) - floatval($args[1]));
            },

            '<' => function ($args) {
                return (floatval($args[0]) < floatval($args[1]));
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
        ));
    }

    /**
     * @dataProvider programProvider
     */
    public function testProgram($name, $code, $expected)
    {
        $this->assertEquals($expected, $this->tl->run($code), "Program '$name' did not return correct value");
    }

    /**
     * Function that provides lisp programs
     */
    public function programProvider()
    {
        return array(
            array(
                'map-impl',
                '(begin
                  ; definition of higher-function map
                  (define map
                    (lambda (x y)
                      (if
                       (eq? (cdr y) (quote ()))
                       (cons (x (car y)) (quote ()))
                       (cons (x (car y)) (map x (cdr y)))
                      )
                    )
                  )
                  (map
                    (lambda (x) (+ x 1))
                    (quote (1 2 3))
                  )
                )',
                array(2, 3, 4)
            ),
            array(
                'factorial',
                '(begin
                  ; definition of factorial
                  (define fact (lambda (n) (if (<= n 1) 1 (* n (fact (- n 1))))))
                  (fact 10)
                )',
                3628800
            ),
            array(
                'factorial',
                '(begin
                  ; definition of range
                  (define range (lambda (a b) (if (= a b) (quote ()) (cons a (range (+ a 1) b)))))
                  (range 0 10)
                )',
                array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9)
            ),
            array(
                'currying-scope',
                '(begin
                  (define x 1)
                  (define y (lambda (z) (lambda (x) (+ z x))))
                  ((y 3) 4)
                )',
                7
            ),
            array(
                'fib',
                '(begin
                  ; definition of fib
                  (define fib (lambda (n) (if (< n 2) 1 (+ (fib (- n 1)) (fib (- n 2))))))
                  (fib 10)
                )',
                89
            ),
            array(
                'count',
                '(begin
                  ; define first to be alias of car
                  (define first car)
                  ; define rest to be alias of cdr
                  (define rest cdr)
                  ; define atoms count function
                  (define count (lambda (item L) (if L (+ (equal? item (first L)) (count item (rest L))) 0)))
                  (list
                    (count 0 (list 0 1 2 3 0 0))
                    (count (quote the) (quote (the more the merrier the bigger the better)))
                  )
                )',
                array(3, 4),
            ),
            array(
                'circle-area',
                '(begin
                  ; define the Pi number
                  (define pi 3.1415926535)
                  ; define function circle-area that takes radius as an input
                  (define circle-area (lambda (r) (* pi (* r r))))
                  (circle-area 3) ; some inline comment
                )',
                28.2743338814
            ),
        );
    }
}
