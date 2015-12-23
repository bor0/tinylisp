<?php
/**
 * A really tiny and extensible lisp interpreter
 * @author bsitnikovski
 */
class TinyLisp
{
    public $env;

    /**
     * Function for initializing environment
     * @param mixed $v Environment
     */
    public function initEnvironment($environment = array())
    {
        $this->env = new Environment($environment);
    }

    /**
     * Default constructor
     * @param mixed $v Environment
     */
    public function TinyLisp($environment = array())
    {
        $this->initEnvironment($environment);
    }

    /**
     * Function that turns code to tokens
     * @param string $code
     * @return array Tokens
     */
    private function tokenize($code)
    {
        $lines = explode("\n", $code);
        $stripped_lines = array();

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line[0] != ';') {
                $stripped_lines[] = $line;
            }
        }

        $code = implode(" ", $stripped_lines);

        $tokens = explode(
            " ",
            trim(
                str_replace(
                    "(",
                    " ( ",
                    str_replace(
                        ")",
                        " ) ",
                        $code
                    )
                )
            )
        );

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
    public function evaluate($params)
    {
        if (is_numeric($params)) {
            return (float)$params;
        } elseif (is_string($params)) {
            $ret = Common::findInEnv($this->env, $params);
 
            if ($ret === Common::NONE) {
                throw new Exception("undefined atom $params");
            } else {
                return $ret;
            }
        } elseif (!is_array($params)) {
            return $params;
        } elseif ($params[0] == 'list?') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for list?");
            }

            return is_array($params[1]);
        } elseif ($params[0] == 'list') {
            $values = array();

            for ($i = 1; $i < count($params); $i++) {
                $values[] = $this->evaluate($params[$i]);
            }

            return $values;
        } elseif ($params[0] == 'begin') {
            for ($i = 1; $i < count($params) - 1; $i++) {
                $this->evaluate($params[$i]);
            }

            return $this->evaluate($params[$i]);
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
            $env = $this->env;

            $f = function ($args) use ($parms, $body, $env) {
                $newEnv = new Environment(Common::zip($parms, $args));
                $x = new TinyLisp(array($newEnv, $env));
                return $x->evaluate($body);
            };

            return $f;
        } elseif ($params[0] == 'if') {
            if (count($params) != 4) {
                throw new Exception("bad syntax for if");
            }

            $ret = $this->evaluate($params[1]) ? $this->evaluate($params[2]) : $this->evaluate($params[3]);

            return $ret;
        } elseif ($params[0] == 'define') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for define");
            }

            $multiple_scoped = false;
            foreach ($this->env as $key => $value) {
                if ($value instanceof Environment) {
                    $multiple_scoped = true;
                    break;
                }
            }

            $param = is_array($params[2]) ? $this->evaluate($params[2]) : $params[2];

            if ($multiple_scoped) {
                // multiple scopes, apply this only to the top most
                $this->env->v[0][$params[1]] = $param;
            } else {
                $this->env->v[$params[1]] = $param;
            }
        } elseif ($params[0] == 'print') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for print");
            }

            print_r($this->evaluate($params[1]));
        } elseif ($params[0] == 'eq?') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for eq?");
            }

            return $this->evaluate($params[1]) == $this->evaluate($params[2]);
        } elseif ($params[0] == 'equal?') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for equal?");
            }

            return $this->evaluate($params[1]) === $this->evaluate($params[2]);
        } elseif ($params[0] == 'car') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for car");
            }

            return $this->evaluate($params[1])[0];
        } elseif ($params[0] == 'cdr') {
            if (count($params) != 2) {
                throw new Exception("bad syntax for cdr");
            }

            $retval = $this->evaluate($params[1]);
            array_shift($retval);

            return $retval;
        } elseif ($params[0] == 'cons') {
            if (count($params) != 3) {
                throw new Exception("bad syntax for cons");
            }

            return array_merge(
                array($this->evaluate($params[1])),
                ($this->evaluate($params[2]))
            );
        } else {
            $proc = $this->evaluate($params[0]);
            $args = array();

            // for (define first car), $proc may return 'car', in which case we need to re-evaluate
            if (is_string($proc)) {
                array_shift($params);
                return $this->evaluate(array_merge(array($proc), $params));
            }

            for ($i = 1; $i < count($params); $i++) {
                $args[] = $this->evaluate($params[$i]);
            }

            if (is_callable($proc)) {
                return $proc($args);
            } elseif (is_array($proc) && method_exists($proc[0], $proc[1])) {
                return call_user_func_array($proc, array($args));
            } else {
                throw new Exception("Can't parse " . print_r($params, true));
            }
        }
    }

    /**
     * Friendly wrapper for evaluate()
     * @param string $code Code to be executed
     * @param array $env Environment to be used
     * @return mixed Result
     */
    public function run($code)
    {
        $tokens = $this->parse($this->tokenize($code));
        return !empty($tokens) ? $this->evaluate($tokens) : null;
    }
}
