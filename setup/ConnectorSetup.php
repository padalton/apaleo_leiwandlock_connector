<?php
include_once('config.php');

class ConnectorSetup
{
    function apaleoSubscribeWebhooks()
    {
        global $config;
        require_once('webhook_endpoint/ApaleoConnector.php');
        error_reporting(E_ERROR | E_PARSE);
        set_time_limit(2000);
        $GLOBALS['log']->fatal('----->Scheduler fired job of type apaleoSubscribeWebhooks()');
        $endpoint = $config['connector_endpoint']."webhook_endpoint/index.php";
        $payload = new stdClass();
        $payload->endpointUrl = $endpoint;
        $payload->topics = array("Reservation");
        $payload->propertyIds = array();

        $Connector = new ApaleoConnector();
        $response = $Connector->callWebhookSubscribe($payload);
        $GLOBALS['log']->fatal(print_r($response, true));
        echo "<pre>";
        print_r($response, true);
        echo "</pre>";
        $GLOBALS['log']->fatal('----->Scheduler ended job of type apaleoSubscribeWebhooks()');
        return true;
    }

}