<?php

$settings->add(new admin_setting_heading('webpa_info',"Server Info","When registering this Moodle as a remote server with WebPA, the server ID is the External ID you specify below, and the callback URL is <strong>{$CFG->httpswwwroot}/local/simplesso/callback.php</strong>"));
$settings->add(new admin_setting_configtext('webpa_server',"WebPA Server",'Required. Full URL of your WebPA server. API calls will be prefixed with this. Must be HTTPS.','',PARAM_URL));
$settings->add(new admin_setting_configtext('webpa_externalid',"External ID",'An identifier for this server on the WebPA server.',''));
$settings->add(new admin_setting_configselect('webpa_identifier',"User Identifier",'Which field in a user\'s profile should be used as their username on WebPA.','username',
	array('username' => get_string('username'), 'idnumber' => get_string('idnumber'), 'id' => 'User ID')));

?>