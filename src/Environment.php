<?php
/**
 * Helper class for initializing environment
 * @author bsitnikovski
 */
class Environment
{
    public $v = array();

    /**
     * Default constructor
     * @param mixed $v Environment
     */
    public function Environment($v)
    {
        $this->v['car'] = function ($params) {
            if (count($params) != 1) {
                throw new Exception("bad syntax for car");
            }

            return $params[0][0];
        };

        $this->v['cdr'] = function ($params) {
            if (count($params) != 1) {
                throw new Exception("bad syntax for cdr");
            }

            array_shift($params[0]);

            return $params[0];
        };

        $this->v['eq?'] = function ($params) {
            if (count($params) != 2) {
                throw new Exception("bad syntax for eq?");
            }

            return $params[0] == $params[1];
        };

        $this->v['equal?'] = function ($params) {
            if (count($params) != 2) {
                throw new Exception("bad syntax for equal?");
            }

            return $params[0] === $params[1];
        };

        $this->v['cons'] = function ($params) {
            if (count($params) != 2 || !is_array($params[1])) {
                throw new Exception("bad syntax for cons");
            }

            return array_merge(array($params[0]), $params[1]);
        };

        $this->v['list?'] = function ($params) {
            if (count($params) != 1) {
                throw new Exception("bad syntax for list?");
            }

            return is_array($params[0]);
        };

        $this->v['list'] = function ($params) {
            return $params;
        };

        $this->v['begin'] = function ($params) {
            return end($params);
        };

        $this->v = array_merge($this->v, $v);
    }
}
