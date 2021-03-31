<?php
session_start();
chdir(dirname(__FILE__) . '/../');
require_once('vendor/autoload.php');
require_once('_db.php');
require_once('Log.php');
$GLOBALS['log'] = new Log();
$GLOBALS['entrypoint'] = 'webhook';
// Parse the POST-Data and print to Info-Log
$webhook_data = json_decode(file_get_contents("php://input"));
$GLOBALS['log']->fatal("recieved WEBHOOK CALL ! DATA:".print_r($webhook_data,true));
//Die and throw Error if POST-Data is empty
if (empty(file_get_contents("php://input"))) {
    $GLOBALS['log']->fatal('[APALEO API] ERROR: No POST-Data sent!');
    http_response_code(400);
    die("[APALEO API] ERROR: No POST-Data sent!");
}

//Check if topic and type is set or json parsing worked
if (empty($webhook_data->topic) || empty($webhook_data->type)) {
    $GLOBALS['log']->fatal('[APALEO API] ERROR: topic or type empty in Webhook-Data!');
    http_response_code(400);
    die("[APALEO API] ERROR: topic or type empty!");
}

// change minus to underline in type for compatibility to function names
$webhook_data->function = str_replace("-", "_", $webhook_data->type);

//check if File for responsible Class exists
if (!file_exists('webhook_endpoint/' . $webhook_data->topic . 'Manager.php')) {
    $GLOBALS['log']->fatal('[APALEO API] ERROR: File for webhook_endpoint/' . $webhook_data->topic . 'Manager.php not found !');
    http_response_code(200);
    die('[APALEO API] ERROR: File for webhook_endpoint/' . $webhook_data->topic . 'Manager.php not found !');
}

//require class that is responsible for this Topic
require('webhook_endpoint/' . $webhook_data->topic . 'Manager.php');

//build dynamic Classname for Manager-Class
$manager_class = $webhook_data->topic . 'Manager';

$_SESSION['account_code'] = $webhook_data->accountId;

//Initiate new Manager-Object and load with webhook-data
$Manager = new $manager_class ($webhook_data);

//Check if Dynamic Object initiation worked and we get an Object from a class that implements ApaleoManager
if (!is_subclass_of($Manager, 'ApaleoManager')) {
    $GLOBALS['log']->fatal('[APALEO API] ERROR: Manager Class "' . $manager_class . '" for Topic "' . $webhook_data->topic . '" not implemented yet!');
    http_response_code(200);
    die("[APALEO API] ERROR: Manager Class '" . $manager_class . "' for Topic '" . $webhook_data->topic . "' not implemented yet!");
}
exit();