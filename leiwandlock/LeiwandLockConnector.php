<?php


/**
 * Class LeiwandLockConnector
 * @copyright Stefan Wölflinger padalton86@gmail.com
 */
class LeiwandLockConnector
{
    /**
     * @var mixed|null
     */
    var $token = null;
    /**
     * @var string
     */
    var $end_point = "";
    /**
     * @var false|resource
     */
    public $curl;

    /**
     * LeiwandLockConnector constructor.
     * @throws Exception
     */
    public function __construct($end_point, $token)
    {
        /** @var MysqliManager $db */ //initialized in entryPoint.php Line 234
        global $db;

        if (empty($token)) $GLOBALS['log']->fatal('[LeiwandLockConnector] ERROR: Token not defined!');
        if (empty($end_point)) $GLOBALS['log']->fatal('[LeiwandLockConnector] ERROR: Endpoint not defined!');
        $this->token = $token;
        $this->end_point = $end_point;

        $this->curl = curl_init();

        //set this->authorization with Token
        $this->authorization = 'Authorization: Token ' . $this->token;
    }

    /**
     * @desc    Calls a Method of the RESTful LeiwandLock Backend Server-API https://backend.leiwandlock.internal/api/
     * @param string $ext Part of the URL after the edpoint URL
     * @param string $method HTTP Method to use (GET, POST, DELETE, PATCH, PUT, HEAD)
     * @param array $params Params to send as URL-Part (for example in GET-Method)
     * @param array $postParams Data to send in POSTFIELDS (in the Data-/Body-part of the Message)
     * @return mixed  returns the json-decoded response
     */
    public function callMethod($ext, $method, $params = array(), $postParams = array())
    {

        $getParams = "";
        if (!empty($params))
            $getParams = "?" . http_build_query($params);
        $url = $this->end_point . "/" . $ext . $getParams;
        //echo($url);
        // echo '<pre>postParams'.print_r(json_encode($postParams), true);

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

        // turn off ssl check
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);

        //set header Authorization
        $header = array();
        //$header[] = 'Content-Length: 0';
        $header[] = 'Content-Type: application/json';
        $header[] = $this->authorization;
        //curl_setopt($this->curl, CURLOPT_HEADER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);

        //POST
        if ($method == "POST") {
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postParams));
        }
        //DELETE
        if ($method == "DELETE") {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postParams);
        }
        //PATCH
        if ($method == "PATCH") {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postParams));
        }
        //PUT
        if ($method == "PUT") {
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postParams));
        }
        //HEAD
        if ($method == "HEAD") {
            // This changes the request method to HEAD
            curl_setopt($this->curl, CURLOPT_NOBODY, true);
        }

        $response = curl_exec($this->curl);

        if ($method == "HEAD") {
            // Edit: Fetch the HTTP-code (cred: @GZipp)
            $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $response = '{ "code" : "' . $code . '"}';
        } elseif (!$response) {
            echo curl_error($this->curl);
        }
        //echo '<pre>'.print_r($response, true);
        if (empty($response)) {
            $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $response = '{ "code" : "' . $code . '"}';
        }
        return json_decode($response);
    }

    /**
     * @desc    Closes the CURL-Connection
     */
    public function close()
    {
        curl_close($this->curl);
    }

}