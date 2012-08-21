<?php

require_once("../../config.php");
require_once("lib.php");
require_once("$CFG->dirroot/local/simplesso/libsso.php");

//error_reporting(0);

require_login();

$site = simplesso_site_for_name("webpa");
$identifier = $CFG->webpa_identifier;
$rslt = simplesso_api_call($site,$CFG->webpa_server."/api2/api.php",array('externalid' => $CFG->webpa_externalid, 'action' => 'forms', 'owner' => $USER->$identifier));
$forms = $rslt['forms'];
echo json_encode($forms);

?>