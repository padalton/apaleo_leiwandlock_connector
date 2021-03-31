<?php
include('_db.php');
include('config.php');
include('webhook_endpoint/ApaleoConnector.php');
include('leiwandlock/LeiwandLockConnector.php');

class Views
{

    public function __construct($view)
    {
        $menu_current_view = $view;
        include('tpls/main_menu.tpl');
        if (method_exists($this, $view)) {
            $this->$view();
        } else {
            $this->home();
        }
    }

    function home()
    {
        global $db;
        $Connector = new ApaleoConnector($_SESSION['code']);
        $account = $Connector->callMethod("account/v1/accounts/current", "GET");
        echo "Authentication successful. Welcome " . $account->name;
        $_SESSION['account_code'] = $account->code;
        $Connector->close();
    }

    function auth_redir()
    {
        /** @var PDO $db */
        global $db, $config;
        $res = $db->query("SELECT UUID() id");
        $row = $res->fetch();
        $db->exec("INSERT INTO auth_codes (id, request_sent) VALUES ('" . $row['id'] . "',1)");
        $url = 'https://identity.apaleo.com/connect/authorize?response_type=code&scope=' . rawurlencode('offline_access reservations.read setup.read') . '&client_id=' . $config['apaleo']['clientId'] . '&redirect_uri=' . rawurlencode($config['connector_endpoint']) . '&state=' . $row['id'];
        echo '<a href="' . $url . '">please continue to Apaleo Connect page</a> <br> you will be redirected in 3 seconds ... <script type="text/javascript">setTimeout(function(){ window.location.href = "' . $url . '";}, 3000);</script>';
    }

    function auth_check()
    {
        if (!empty($_GET['state'])) {
            /** @var PDO $db */
            global $db,$config;
            $stmt = $db->prepare("SELECT * FROM auth_codes WHERE id = :id");
            $stmt->bindParam(':id', $_GET['state']);
            $stmt->execute();
            $auth = $stmt->fetch();
            if (!empty($auth['id'])) {
                $stmt = $db->prepare("UPDATE auth_codes SET auth_code = :code WHERE id = :id");
                $stmt->bindParam(':id', $_GET['state']);
                $stmt->bindParam(':code', $_GET['code']);
                $stmt->execute();
                $Connector = new ApaleoConnector($_GET['code']);
                $account = $Connector->callMethod("account/v1/accounts/current", "GET");
                echo "Authentication successful. Welcome " . $account->name;
                $_SESSION['code'] = $_GET['code'];
                $_SESSION['account_code'] = $account->code;
                $stmt = $db->prepare("UPDATE apaleo_tokens SET account_id = :account_id WHERE id = :id");
                $stmt->bindParam(':id', $_GET['code']);
                $stmt->bindParam(':account_id', $account->code);
                $stmt->execute();
                header("Location: ".$config['connector_endpoint']);
            } else {
                echo "something went wrong (unknown state/request)";
            }
        } else {
            echo "soemthing went wrong (no state param)";
        }
    }

    function setup_properties()
    {
        global $db;
        if (!empty($_GET['action'])) {
            switch ($_GET['action']):
                case "setup_save":
                    if (!empty($_POST['id'])) {
                        $stmt = $db->prepare("UPDATE leiwand_setup SET host = :host, ll_token = :token WHERE account_code = :code AND property_id = :id");
                    } else {
                        $stmt = $db->prepare("INSERT INTO leiwand_setup (id, account_code, property_id, host, ll_token) VALUES (UUID(), :code, :id, :host, :token)");
                    }
                    $stmt->bindParam(':code', $_SESSION['account_code']);
                    $stmt->bindParam(':id', $_POST['property_id']);
                    $stmt->bindParam(':host', $_POST['host']);
                    $stmt->bindParam(':token', $_POST['ll_token']);
                    $stmt->execute();
                    break;
                default:
                    break;
            endswitch;
        }
        $Connector = new ApaleoConnector($_SESSION['code']);
        $properties = $Connector->callMethod("inventory/v1/properties", "GET");
        echo '<div class="property_form_container">';
        foreach ($properties->properties as $property) {
            $test = $this->check_property_ll_connection($property->id);
            $connectivity = $test['connectivity'];
            $active = $test['active'];
            $setup = $this->get_ll_setup($property->id);
            include('tpls/property_form.tpl');
        }
        echo '</div>';
    }

    function check_property_ll_connection($property_id)
    {

        $setup = $this->get_ll_setup($property_id);
        $connectivity = false;
        if (!empty($setup['host']) && !empty($setup['ll_token'])) {
            $LeiwandCon = new LeiwandLockConnector($setup['host'], $setup['ll_token']);
            $test = $LeiwandCon->callMethod("tags/", "HEAD");
            if ($test->code == '200') $connectivity = true;
        }
        return array('active' => $setup['active'], 'connectivity' => $connectivity);
    }

    function get_ll_setup($property_id)
    {
        global $db;
        $stmt = $db->prepare("SELECT * FROM leiwand_setup WHERE account_code = :code AND property_id = :id");
        $stmt->bindParam(':code', $_SESSION['account_code']);
        $stmt->bindParam(':id', $property_id);
        $stmt->execute();
        $setup = $stmt->fetch();
        return $setup;
    }

    function setup_units()
    {
        global $db;
        if (empty($_GET['property_id'])) {
            $Connector = new ApaleoConnector($_SESSION['code']);
            $properties = $Connector->callMethod("inventory/v1/properties", "GET");
            $prop_list = array();
            foreach ($properties->properties as $property) {
                $prop_list[$property->id] = $property->name;
            }
            include('tpls/units_property_select.tpl');
        } else {
            if ($_GET['action'] == "units_save") {
                $this->sync_units();
                $this->update_active_rooms();
            }
            $connectivity = $this->check_property_ll_connection($_GET['property_id']);
            if (!$connectivity['connectivity']) {
                echo 'No Connection to LeiwandLock Server for Property-ID ' . $_GET['property_id'] . '!<br> Please check <a href="/?units_menue">Setup</a>';
            } else {
                $Connector = new ApaleoConnector($_SESSION['code']);
                $units = $Connector->callMethod("inventory/v1/units", "GET", array("propertyId" => $_GET['property_id']));
                $setup = $this->get_ll_setup($_GET['property_id']);
                $LeiwandCon = new LeiwandLockConnector($setup['host'], $setup['ll_token']);
                $tags = $LeiwandCon->callMethod("tags/", "GET");
                $tags_array = array();
                foreach ($tags as $tag) $tags_array[$tag->tag] = $tag->name;
                $stmt = $db->prepare("SELECT * FROM leiwand_units WHERE account_id = '" . $_SESSION['account_code'] . "' AND property_id = :property_id");
                $stmt->bindParam(':property_id', $_GET['property_id']);
                $stmt->execute();
                $db_units = $stmt->fetchAll();
                $db_units_array = array();
                foreach ($db_units as $db_unit) $db_units_array[$db_unit['room_id']] = $db_unit;
                include('tpls/unit_list_head.tpl');
                foreach ($units->units as $unit) {
                    if (!empty($tags_array[$unit->id]) && empty($db_units_array[$unit->id]['id'])) {
                        $stmt = $db->prepare("INSERT INTO leiwand_units (id, account_id, property_id, room_id, synced) VALUES (UUID(), '{$_SESSION['account_code']}', :property_id, '{$unit->id}', '1')");
                        $stmt->bindParam(':property_id', $_GET['property_id']);
                        $stmt->execute();
                        $db_units_array[$unit->id] = array(
                            'account_id' => $_SESSION['account_code'],
                            'property_id' => $_GET['property_id'],
                            'room_id' => $unit->id,
                            'synced' => 1,
                            'active' => 0
                        );
                    }
                    include('tpls/unit_list_item.tpl');
                }
                include('tpls/unit_list_footer.tpl');
            }
        }
    }

    function sync_units()
    {
        global $db;
        $setup = $this->get_ll_setup($_GET['property_id']);
        foreach ($_POST['sync'] as $room_id => $room_name) {
            $LeiwandCon = new LeiwandLockConnector($setup['host'], $setup['ll_token']);
            $tag_save = $LeiwandCon->callMethod("tags/", "POST", array(), array("tag" => $room_id, "name" => $room_name));
            $LeiwandCon->close();
            $stmt = $db->prepare("INSERT INTO leiwand_units (id, account_id, property_id, room_id, synced) VALUES (UUID(), '{$_SESSION['account_code']}', :property_id, '{$room_id}', '1')");
            $stmt->bindParam(':property_id', $_GET['property_id']);
            $stmt->execute();
        }
        echo "<b>Saved. Please remember to link the Locks to the Tags in Leiwand Lock Server!</b>";
    }

    function update_active_rooms()
    {
        global $db;
        $setup = $this->get_ll_setup($_GET['property_id']);
        foreach ($_POST['active'] as $room_id => $room_name) {
            $stmt = $db->prepare("UPDATE leiwand_units SET active = 1 WHERE account_id = '{$_SESSION['account_code']}' AND property_id = :property_id AND room_id = :room_id");
            $stmt->bindParam(':property_id', $_GET['property_id']);
            $stmt->bindParam(':room_id', $room_id);
            $stmt->execute();
        }
    }

    function activate(){
        global $db,$config;
        $property_id = $_GET['activate'];
        $endpoint = $config['connector_endpoint']."webhook_endpoint/index.php";
        $payload = new stdClass();
        $payload->endpointUrl = $endpoint;
        $payload->topics = array("Reservation");
        $payload->propertyIds = array($property_id);

        $Connector = new ApaleoConnector($_SESSION['code']);
        $response = $Connector->callWebhookSubscribe($payload);
        if(!empty($response->id)) {
            echo "Property " . $property_id . " successful connected !";
            $stmt = $db->prepare("UPDATE leiwand_setup SET active = '1', subscription_id = :sub_id WHERE account_code = :code AND property_id = :id");
            $stmt->bindParam(':code', $_SESSION['account_code']);
            $stmt->bindParam(':id', $property_id);
            $stmt->bindParam(':sub_id', $response->id);
            $stmt->execute();
        }else{
            echo "something went wrong! Debug info: <br><pre>";
            echo print_r($response, true)."</pre>";
        }
    }

    function deactivate(){
        global $db;
        $property_id = $_GET['deactivate'];
        $stmt = $db->prepare("SELECT subscription_id FROM leiwand_setup WHERE account_code = :code AND property_id = :id");
        $stmt->bindParam(':code', $_SESSION['account_code']);
        $stmt->bindParam(':id', $property_id);
        $stmt->execute();
        $sub = $stmt->fetch();

        $Connector = new ApaleoConnector($_SESSION['code']);
        $response = $Connector->callWebhookUnsubscribe($sub['subscription_id']);
        if($response->code == '204') {
            echo "Property " . $property_id . " successful deactivated !";
            $stmt = $db->prepare("UPDATE leiwand_setup SET active = '0', subscription_id = NULL WHERE account_code = :code AND property_id = :id");
            $stmt->bindParam(':code', $_SESSION['account_code']);
            $stmt->bindParam(':id', $property_id);
            $stmt->execute();
        }else{
            echo "something went wrong! Debug info: <br><pre>";
            echo print_r($response, true)."</pre>";
        }
    }

    function setup_emails(){
        global $db;
        if($_GET['action'] == 'setup_save'){
            if (!empty($_POST['id'])) {
                $stmt = $db->prepare("UPDATE email_setup SET account_id = '{$_SESSION['account_code']}', SMTPSecure = :SMTPSecure, SMTPAuth = :SMTPAuth, Host = :Host, Port = :Port, Username = :Username, Password = :Password, FromAddress = :FromAddress WHERE id = :id");
                $stmt->bindParam(':id', $_POST['id']);
            } else {
                $stmt = $db->prepare("INSERT INTO email_setup (id, account_id, SMTPSecure, SMTPAuth, Host, Port, Username, Password, FromAddress) VALUES (UUID(), '{$_SESSION['account_code']}', :SMTPSecure, :SMTPAuth, :Host, :Port, :Username, :Password, :FromAddress)");
            }
            $stmt->bindParam(':SMTPSecure', $_POST['SMTPSecure']);
            $stmt->bindParam(':SMTPAuth', $_POST['SMTPAuth']);
            $stmt->bindParam(':Host', $_POST['Host']);
            $stmt->bindParam(':Port', $_POST['Port']);
            $stmt->bindParam(':Username', $_POST['Username']);
            $stmt->bindParam(':Password', $_POST['Password']);
            $stmt->bindParam(':FromAddress', $_POST['FromAddress']);
            $stmt->execute();
            echo "<b>saved!</b>";
        }

        $stmt = $db->prepare("SELECT * FROM email_setup WHERE account_id = :code ");
        $stmt->bindParam(':code', $_SESSION['account_code']);
        $stmt->execute();
        $setup = $stmt->fetch();
        include('tpls/email_form.tpl');
    }

}