<?php

require_once('webhook_endpoint/ApaleoConnector.php');

/**
 * Class ApaleoManager
 * @copyright Stefan Wölflinger padalton86@gmail.com
 */
abstract class ApaleoManager
{

    var $connector;
    var $webhook_data;

    function __construct($data)
    {
        if (empty($data)) {
            $this->log('webhook_data empty!');
            exit();
        }
        $this->webhook_data = $data;
        $this->connector = new ApaleoConnector();

        if (method_exists($this, $this->webhook_data->function)) {
            $this->{$this->webhook_data->function}();
        } else {
            $this->log('Method ' . $this->webhook_data->function . ' does not exist !');
        }
    }

    final function log($message, $loglevel = "ERROR")
    {
        $GLOBALS['log']->fatal('[APALEO API] (' . get_class($this) . ') ' . $loglevel . ': ' . $message);
        //later maybe add email functionality ? Spam the Developer if anything goes wrong ?
    }

    abstract function created();

}