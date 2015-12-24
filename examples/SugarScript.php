<?php
require_once 'SugarFunction.php';
require_once '../AutoLoader.php';

if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}

require_once 'include/entryPoint.php';

global $current_user;
$current_user = BeanFactory::getBean("Users", "1");

$env = array(
    '*'               => array('SugarFunction', 'multiplication'),
    '+'               => array('SugarFunction', 'addition'),
    'new-bean'        => array('SugarFunction', 'newBean'),
    'get-bean'        => array('SugarFunction', 'getBean'),
    'get-property'    => array('SugarFunction', 'getProperty'),
    'execute-sql'     => array('SugarFunction', 'executeSQL'),
    'array-to-string' => array('SugarFunction', 'arrayToString'),
);

echo "SugarScript LISP machine activated.\n";
echo "Example: (begin (define user (get-bean (quote Users) 1)) (print (get-property (quote is_admin) user)))\n";
echo "Example: (print (execute-sql (quote (select id, user_name from users))))\n";
echo "Example: (print (get-property (quote id) (new-bean (quote Users) (quote ((user_name a) (first_name Test))))))\n";

while (true) {
    echo "> ";
    try {
        $x = new TinyLisp($env);
        echo $x->run(readline()) . "\n";
    } catch (Exception $e) {
        echo "ERROR: {$e->getMessage()}\n";
    }
}
