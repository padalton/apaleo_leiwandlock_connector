<?php

require_once('webhook_endpoint/ApaleoManager.php');

/**
 * Class SystemManager
 * @copyright Stefan Wölflinger padalton86@gmail.com
 */
class systemManager extends ApaleoManager
{
    public function healthcheck()
    {
        http_response_code(200);
        echo "LeiwandLock-Connector endpoint is alive! \n developed by Stefan Woelflinger (padalton86@gmail.com)";
        exit();
    }

    public function created()
    {
        // not to implement for this class
    }

}