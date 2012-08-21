<?php

require_once('libsso.php');

global $DB;

$key = $_GET['key'];
$keyval = $_GET['keyval'];

$r = $DB->get_record('local_simplesso_keys',array('id' => $key));
if($r->pskey == $keyval) {
	echo "VERIFIED";
} else {
	error_log("FAILURE $key $keyval");
	echo "FAILURE $key $keyval";
}

?>