<?php
class TinyLispTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->tl = new TinyLisp();
    }

    public function testBegin()
    {
        $this->assertEquals(2, $this->tl->run("(begin 1 2)"));
    }

    public function testQuote()
    {
        $this->assertEquals(array("hello", "world"), $this->tl->run("(quote (hello world))"));
    }

    public function testIf()
    {
        $this->assertEquals("2", $this->tl->run("(if 1 2 3)"));
        $this->assertEquals("3", $this->tl->run("(if 0 2 3)"));
    }

    public function testDefine()
    {
        $res = $this->tl->run("(begin (define x (quote (test))) x)");
        $this->assertEquals(array("test"), $res);
    }

    public function testPrint()
    {
        $this->expectOutputString('test');
        $res = $this->tl->run("(begin (define x (quote test)) (print x))");
    }

    public function testEq()
    {
        $this->assertTrue($this->tl->run("(eq? 1 (quote 1)))"));
        $this->assertFalse($this->tl->run("(eq? 1 (quote 0)))"));
        $this->assertTrue($this->tl->run("(eq? 1 1))"));
        $this->assertFalse($this->tl->run("(eq? 1 0))"));
    }

    public function testEqual()
    {
        $this->assertFalse($this->tl->run("(equal? 1 (quote 1)))"));
        $this->assertFalse($this->tl->run("(equal? 1 (quote 0)))"));
        $this->assertTrue($this->tl->run("(equal? 1 1))"));
        $this->assertFalse($this->tl->run("(equal? 1 0))"));
    }

    public function testCar()
    {
        $this->assertEquals("hello", $this->tl->run("(car (quote (hello world)))"));
    }

    public function testCdr()
    {
        $this->assertEquals(array("world"), $this->tl->run("(cdr (quote (hello world)))"));
    }

    public function testCons()
    {
        $this->assertEquals(array(1), $this->tl->run("(cons 1 (quote ()))"));
        $this->assertEquals(array(1, 2), $this->tl->run("(cons 1 (quote (2)))"));
    }

    public function testLambda()
    {
        $ret =  $this->tl->run("(begin (define second (lambda (x) (car (cdr x)))) (second (quote (1 2 3))))");
        $this->assertEquals(2, $ret);
    }

    public function testInteger()
    {
        $this->assertEquals(123, $this->tl->run("123"));
    }

    public function testExistingAtom()
    {
        $env = array("x" => 123);
        $this->assertEquals(123, $this->tl->run("x", $env));
    }

    public function testNonExistingAtom()
    {
        $this->setExpectedException('Exception', 'undefined atom x');
        $this->tl->run("x");
    }

    public function AdditionFunction($args)
    {
        return array_reduce(
            $args,
            function ($x, $y) {
                return floatval($x) + floatval($y);
            },
            0
        );
    }

    public function testDefinedFunction()
    {
        $env = array(
            "+" => array("TinyLispTest", "AdditionFunction")
        );

        $this->assertEquals(10, $this->tl->run("(+ 1 2 3 4)", $env));
    }
}
