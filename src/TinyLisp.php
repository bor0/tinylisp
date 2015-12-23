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
     * Helper function for the implementation of lambdas
     * when determining multiple scopes, we need to zip arguments with passed values
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function zip($array1, $array2)
    {
        $zipped = array();
        $l = min(count($array1), count($array2));

        for ($i = 0; $i < $l; $i++) {
            $zipped[$array1[$i]] = $array2[$i];
        }

        return $zipped;
    }

    /**
     * Function that evaluates a given syntax tree
     * @param array $params
     * @param array $env Environment to be used
     * @return mixed Result
     */
    public function evaluate($params, &$env, $multiple_env = false)
    {
        if ($params[0] == 'begin') {
            for ($i = 1; $i < count($params) - 1; $i++) {
                $this->evaluate($params[$i], $env, $multiple_env);
            }

            return $this->evaluate($params[$i], $env, $multiple_env);
        } elseif ($params[0] == 'quote') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for quote");
            }

            return $params[1];
        } elseif ($params[0] == 'lambda') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for lambda");
            }

            $parms = $params[1];
            $body = $params[2];

            return function ($args) use ($parms, $body, $env) {
                $newEnv = array(TinyLisp::zip($parms, $args), $env);

                $x = new TinyLisp();
                return $x->evaluate($body, $newEnv, true);
            };
        } elseif ($params[0] == 'if') {
            if (count($params) != 4) {
                throw new Exception("bad syntax for if");
            }

            $ret = $this->evaluate($params[1], $env, $multiple_env) ? $params[2] : $params[3];

            return $this->evaluate($ret, $env, $multiple_env);
        } elseif ($params[0] == 'define') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for define");
            }

            if ($multiple_env) {
                $env[0][$params[1]] = $this->evaluate($params[2], $env, $multiple_env);
            } else {
                $env[$params[1]] = $this->evaluate($params[2], $env, $multiple_env);
            }
        } elseif ($params[0] == 'print') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for print");
            }

            print_r($this->evaluate($params[1], $env, $multiple_env));
        } elseif ($params[0] == 'eq?') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for eq?");
            }

            return $this->evaluate($params[1], $env, $multiple_env)
                == $this->evaluate($params[2], $env, $multiple_env);
        } elseif ($params[0] == 'equal?') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for equal?");
            }

            return $this->evaluate($params[1], $env, $multiple_env)
                === $this->evaluate($params[2], $env, $multiple_env);
        } elseif ($params[0] == 'car') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for car");
            }

            return $this->evaluate($params[1], $env, $multiple_env)[0];
        } elseif ($params[0] == 'cdr') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for cdr");
            }

            $retval = $this->evaluate($params[1], $env, $multiple_env);
            array_shift($retval);

            return $retval;
        } elseif ($params[0] == 'cons') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for cons");
            }

            return array_merge(
                array($this->evaluate($params[1], $env, $multiple_env)),
                ($this->evaluate($params[2], $env, $multiple_env))
            );
        } elseif (is_numeric($params)) {
            return (float)$params;
        } elseif (is_string($params)) {
            if ($multiple_env) {
                for ($i = 0; $i < count($multiple_env); $i++) {
                    if (isset($env[$i][$params])) {
                        return $env[$i][$params];
                    }
                }
            } elseif (isset($env[$params])) {
                return $env[$params];
            } else {
                throw new Exception("undefined atom $params");
            }
        } elseif (!is_array($params)) {
            return $params;
        } else {
            $proc = $this->evaluate($params[0], $env, $multiple_env);
            $args = array();

            for ($i = 1; $i < count($params); $i++) {
                $args[] = $this->evaluate($params[$i], $env, $multiple_env);
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
    public function run($code, &$env = array(), $multiple_env = false)
    {
        $tokens = $this->parse($this->tokenize($code));
        return !empty($tokens) ? $this->evaluate($tokens, $env, $multiple_env) : null;
    }
}
