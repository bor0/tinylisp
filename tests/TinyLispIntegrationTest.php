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
        $this->assertEquals($expected, $this->tl->run($code), "Program $name did not return correct value");
    }

    public function programProvider()
    {
        return array(
            array(
                'map-impl',
                '(begin
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
                  (define fact (lambda (n) (if (<= n 1) 1 (* n (fact (- n 1))))))
                  (fact 10)
                )',
                3628800
            ),
            array(
                'factorial',
                '(begin
                  (define range (lambda (a b) (if (= a b) (quote ()) (cons a (range (+ a 1) b)))))
                  (range 0 10)
                )',
                array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9)
            ),
            array(
                'currying-scope',
                '(begin
                  (define x 1)
                  (define y
                    (lambda (z)
                      (lambda (x)
                        (+ z x)
                      )
                    )
                  )
                  ((y 3) 4)
                )',
                7
            ),
        );
    }
}
