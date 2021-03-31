<?php
require_once('config.php');
require_once('_db.php');

/**
 * Class ApaleoConnector
 * @copyright Stefan Wölflinger padalton86@gmail.com
 */
class ApaleoConnector
{
    /**
     * @var mixed|null
     */
    var $clientId = null;
    /**
     * @var mixed|null
     */
    var $clientSecret = null;
    /**
     * @var string
     */
    var $end_points = array(
        "default" => "https://api.apaleo.com"
    );
    /**
     * @var false|resource
     */
    public $curl;

    /**
     * ApaleoConnector constructor.
     * @throws Exception
     */
    public function __construct($code = '')
    {
        /** @var PDO $db */
        global $db;

        global $config;

        $this->clientId = $config['apaleo']['clientId'];
        $this->clientSecret = $config['apaleo']['clientSecret'];

        if (empty($this->clientSecret)) $GLOBALS['log']->fatal('[ApaleoConnector] ERROR: clientSecret not defined $config[\'apaleo\'][\'clientSecret\'] !');
        if (empty($this->clientId)) $GLOBALS['log']->fatal('[ApaleoConnector] ERROR: clientId not defined $config[\'apaleo\'][\'clientId\'] !');

        $this->curl = curl_init();

        $authnow = new DateTime();
        $authnow->add(new DateInterval("PT30S"));
        $authnow_string = $authnow->format("Y-m-d H:i:s");
        if($GLOBALS['entrypoint'] == 'webhook'){
            $token_res = $db->query("SELECT access_token, token_type, refresh_token FROM apaleo_tokens WHERE expires >= '$authnow_string' AND account_id = '" . $_SESSION['account_code'] . "' LIMIT 1");
        }else{
            $token_res = $db->query("SELECT access_token, token_type, refresh_token FROM apaleo_tokens WHERE expires >= '$authnow_string' AND id = '" . $code . "' LIMIT 1");
        }
        $token_data = $token_res->fetch();
        $expired = false;
        if(empty($token_data['access_token'])){ //lets see if we have a expired token
            if($GLOBALS['entrypoint'] == 'webhook'){
                $token_res = $db->query("SELECT access_token, token_type, refresh_token FROM apaleo_tokens WHERE account_id = '" . $_SESSION['account_code'] . "' ORDER BY expires DESC LIMIT 1");
            }else{
                $token_res = $db->query("SELECT access_token, token_type, refresh_token FROM apaleo_tokens WHERE id = '" . $code . "' ORDER BY expires DESC LIMIT 1");
            }
            $token_data = $token_res->fetch();
            if(!empty($token_data['access_token'])){ // there is a expired one
                $expired = true;
            }
        }
        $token = $token_data['access_token'];
        $token_type = $token_data['token_type'];
        $refresh_token = $token_data['refresh_token'];

        if (empty($_GET['code']) && empty($token) && $GLOBALS['entrypoint'] != 'webhook') {
            session_destroy(); // new user or has to reauthenticate
            header("Location: ".$config['connector_endpoint']);
            die();
        }

        if (empty($token) && !$expired) {
            // request a Token https://apaleo.dev/guides/start/oauth-connection/auth-code-grant
            $header = array();
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
            $header[] = 'Accept: application/json';
            //$header[] = $this->authorization;
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($this->curl, CURLOPT_URL, "https://identity.apaleo.com/connect/token");
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, "client_id=$this->clientId&client_secret=$this->clientSecret&grant_type=authorization_code&code=$code&redirect_uri=" . rawurlencode($config['connector_endpoint']));

            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($this->curl);
            if (!$response)
                echo curl_error($this->curl);
            $auth_res = json_decode($response);
            if (!empty($auth_res->error)) echo "Authentication (get token) failed: " . $auth_res->error;

            // save the token with DateTime plus the expires value added in db apaleo_tokens
            $expires_date = new DateTime();
            $expires_date->add(new DateInterval("PT" . $auth_res->expires_in . "S"));
            $db->query("INSERT INTO apaleo_tokens (id,access_token,refresh_token,account_id,expires,scope,token_type) VALUES ('{$code}','{$auth_res->access_token}','{$auth_res->refresh_token}','{$_SESSION['account_code']}','{$expires_date->format("Y-m-d H:i:s")}','{$auth_res->scope}','{$auth_res->token_type}')");

            // use the nice new token
            $token = $auth_res->access_token;
            $token_type = $auth_res->token_type;
            curl_close($this->curl);
            $this->curl = curl_init();
        }else{ //refresh token
            // refresh a Token https://apaleo.dev/guides/start/oauth-connection/refresh-token
            $header = array();
            $header[] = 'Content-Type: application/x-www-form-urlencoded';
            $header[] = 'Accept: application/json';
            //$header[] = $this->authorization;
            curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($this->curl, CURLOPT_URL, "https://identity.apaleo.com/connect/token");
            curl_setopt($this->curl, CURLOPT_POST, true);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, "client_id=$this->clientId&client_secret=$this->clientSecret&grant_type=refresh_token&refresh_token=$refresh_token");

            curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($this->curl);
            if (!$response)
                echo curl_error($this->curl);
            $auth_res = json_decode($response);
            if (!empty($auth_res->error)) echo "Authentication (refresh token) failed: " . $auth_res->error;

            // save the token with DateTime plus the expires value added in db apaleo_tokens
            $expires_date = new DateTime();
            $expires_date->add(new DateInterval("PT" . $auth_res->expires_in . "S"));
            $db->query("UPDATE apaleo_tokens SET expires = '{$expires_date->format("Y-m-d H:i:s")}', access_token = '{$auth_res->access_token}' WHERE  refresh_token = '{$refresh_token}'");

            // use the nice new token
            $token = $auth_res->access_token;
            $token_type = $auth_res->token_type;
            curl_close($this->curl);
            $this->curl = curl_init();
        }

        //set this->authorization with Token
        $this->authorization = 'Authorization: ' . $token_type . ' ' . $token;
    }

    /**
     * @desc    Calls a Method of the RESTful Apaleo-API https://api.apaleo.com
     * @param string $ext Part of the URL after the edpoint URL
     * @param string $method HTTP Method to use (GET, POST, DELETE, PATCH, PUT, HEAD)
     * @param array $params Params to send as URL-Part (for example in GET-Method)
     * @param array $postParams Data to send in POSTFIELDS (in the Data-/Body-part of the Message)
     * @return mixed  returns the json-decoded response
     */
    public function callMethod($ext, $method, $params = array(), $postParams = array(), $api_endpoint = 'default')
    {

        $getParams = "";
        if (!empty($params))
            $getParams = "?" . http_build_query($params);
        $url = $this->end_points[$api_endpoint] . "/" . $ext . $getParams;
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
        if ($method == "PUT" && curl_getinfo($this->curl, CURLINFO_HTTP_CODE) == 429) {
            sleep(2);
            $response = curl_exec($this->curl);
        }
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

    public function callWebhookSubscribe($postParams = array())
    {

        $url = "https://webhook.apaleo.com/v1/subscriptions";

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

        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($postParams));

        $response = curl_exec($this->curl);

        if (!$response) {
            echo curl_error($this->curl);
        }
        //echo '<pre>'.print_r($response, true);
        if (empty($response)) {
            $code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $response = '{ "code" : "' . $code . '"}';
        }
        return json_decode($response);
    }

    public function callWebhookUnsubscribe($subscription)
    {

        $url = "https://webhook.apaleo.com/v1/subscriptions/".$subscription;

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
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");


        $response = curl_exec($this->curl);

        if (!$response) {
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