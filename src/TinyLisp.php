<?php
/**
 * A really tiny and extensible lisp interpreter
 * @author bsitnikovski
 */
class TinyLisp
{
    /**
     * Function that turns code to tokens
     * @param string $code
     * @return array Tokens
     */
    private function tokenize($code)
    {
        $tokens = explode(" ", trim(str_replace("(", " ( ", str_replace(")", " ) ", $code))));

        return array_filter($tokens, function ($val) {
            return $val != '';
        });
    }

    /**
     * Function that parses tokens to syntax tree
     * @param array $tokens
     * @return array Tree
     */
    private function parse($tokens)
    {
        return !empty($tokens) ? $this->read_from_tokens($tokens) : array();
    }

    /**
     * Function that parses tokens to syntax tree
     * @param array $tokens
     * @return array Tree
     */
    private function read_from_tokens(&$tokens)
    {
        if (count($tokens) == 0) {
            throw new Exception('unexpected EOF while reading');
        }

        $token = array_shift($tokens);

        if ($token == '(') {
            $list = array();
            while ($tokens[0] != ')') {
                $list[] = $this->read_from_tokens($tokens);
            }
            array_shift($tokens);
            return $list;
        } elseif ($token == ')') {
            throw new Exception('unexpected )');
        } else {
            return $token;
        }
    }

    /**
     * Function that evaluates a given syntax tree
     * @param array $params
     * @param array $env Environment to be used
     * @return mixed Result
     */
    private function evaluate($params, &$env)
    {
        if ($params[0] == 'begin') {
            for ($i=1; $i < count($params) - 1; $i++) {
                $this->evaluate($params[$i], $env);
            }

            return $this->evaluate($params[$i], $env);
        } elseif ($params[0] == 'quote') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for quote");
            }

            return $params[1];
        } elseif ($params[0] == 'if') {
            if (count($params) != 4) {
                throw new Exception("bad syntax for if");
            }

            $ret = $this->evaluate($params[1], $env) ? $params[2] : $params[3];

            return $this->evaluate($ret, $env);
        } elseif ($params[0] == 'define') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for define");
            }

            $env[$params[1]] = $this->evaluate($params[2], $env);
        } elseif ($params[0] == 'print') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for print");
            }

            print_r($this->evaluate($params[1], $env));
        } elseif ($params[0] == 'eq?') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for eq?");
            }

            return $this->evaluate($params[1], $env) == $this->evaluate($params[2], $env);
        } elseif ($params[0] == 'equal?') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for equal?");
            }

            return $this->evaluate($params[1], $env) === $this->evaluate($params[2], $env);
        } elseif ($params[0] == 'car') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for car");
            }

            return $this->evaluate($params[1], $env)[0];
        } elseif ($params[0] == 'cdr') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for cdr");
            }

            $retval = $this->evaluate($params[1], $env);
            array_shift($retval);

            return $retval;
        } elseif ($params[0] == 'cons') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for cons");
            }

            return array_merge(array($this->evaluate($params[1], $env)), ($this->evaluate($params[2], $env)));
        } elseif (is_numeric($params)) {
            return (float)$params;
        } elseif (is_string($params)) {
            if (isset($env[$params])) {
                return $env[$params];
            } else {
                throw new Exception("undefined atom $params");
            }
        } elseif (!is_array($params)) {
            return $params;
        } else {
            $proc = $this->evaluate($params[0], $env);
            $args = array();

            for ($i=1; $i<count($params); $i++) {
                $args[] = $this->evaluate($params[$i], $env);
            }

            return call_user_func_array($proc, array($args));
        }
    }

    /**
     * Friendly wrapper for evaluate()
     * @param string $code Code to be executed
     * @param array $env Environment to be used
     * @return mixed Result
     */
    public function run($code, &$env = array())
    {
        $tokens = $this->parse($this->tokenize($code));
        return !empty($tokens) ? $this->evaluate($tokens, $env) : null;
    }
}
