<?php
/**
 * Main class that defines Sugar wrapper functions for the Lisp interpreter
 * @author bsitnikovski
 */
class SugarFunction
{
    /**
     * This public static function does multiplication on a variable length of arguments
     * @param array $args
     */
    public static function multiplication($args)
    {
        return array_reduce(
            $args,
            function ($x, $y) {
                return floatval($x) * floatval($y);
            },
            1
        );
    }

    /**
     * This public static function does addition on a variable length of arguments
     * @param array $args
     */
    public static function addition($args)
    {
        return array_reduce(
            $args,
            function ($x, $y) {
                return floatval($x) + floatval($y);
            },
            0
        );
    }

    /**
     * This public static function is a wrapper for BeanFactory::getBean
     * @param array $args
     */
    public static function getBean($args)
    {
        if (count($args) != 2) {
            throw new Exception("Wrong number of arguments for getBean");
        }

        return BeanFactory::getBean($args[0], $args[1]);
    }

    /**
     * This public static function returns a given property for a given object
     * @param array $args
     */
    public static function getProperty($args)
    {
        if (count($args) != 2) {
            throw new Exception("Wrong number of arguments for getProperty");
        }

        $prop = $args[0];
        $obj = $args[1];
        return $obj->$prop;
    }

    /**
     * This public static function executes SQL
     * @param array $args
     */
    public static function executeSQL($args)
    {
        if (count($args) != 1) {
            throw new Exception("Wrong number of arguments for executeSQL");
        }

        $db = DBManagerFactory::getInstance();
        $sql = implode(" ", $args[0]);

        $result = $db->query($sql);

        return $db->fetchByAssoc($result);
    }
}
