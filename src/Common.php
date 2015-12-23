<?php
/**
 * Class that contains common used functions
 * @author bsitnikovski
 */
class Common
{
    const NONE = '__NONE__';

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
     * Helper function that supports recursively searching an environment
     * @param mixed $env Environment
     * @param mixed $params Search string
     * @return mixed
     */
    public static function findInEnv($env, $params)
    {
        if (!($env instanceof Environment)) {
            return self::NONE;
        }

        foreach ($env->v as $key => $value) {
            if ($value instanceof Environment) {
                $val = self::findInEnv($value, $params);
                if ($val !== self::NONE) {
                    return $val;
                }
            } elseif ($key === $params) {
                return $value;
            }
        }

        return self::NONE;
    }
}
