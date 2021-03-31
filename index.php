<?php
require_once('Log.php');
$GLOBALS['log'] = new Log();
session_start();
include('tpls/header.tpl');
require_once('Views.php');
include_once('config.php');

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ".$config['connector_endpoint']);
}

if (isset($_GET['code']) && !empty($_GET['code'])) {
    $View = new Views('auth_check');
} elseif (!empty($_SESSION['code'])) {
    if (isset($_GET['property_menue'])) {
        $View = new Views('setup_properties');
    } elseif (isset($_GET['units_menue'])) {
        $View = new Views('setup_units');
    } elseif(!empty($_GET['activate'])) {
        $View = new Views('activate');
    } elseif(!empty($_GET['deactivate'])) {
        $View = new Views('deactivate');
    } elseif (isset($_GET['emails_menue'])) {
        $View = new Views('setup_emails');
    } else {
        $View = new Views('home');
    }
} else {
    $View = new Views('auth_redir');
}


include('tpls/footer.tpl');