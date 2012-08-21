<?php

/**
 * Single Sign-on library for Moodle.
 *
 * Designed to connect with other sites.
 *
 * @author Morgan Harris
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

global $DB;

/**
 * Registers a site for SSO.
 * 
 * @return site object on success, false on error
 * @param string $name Site name
 * @param string $url URL to reach remote SSO
 * @param string $externalid ID of this server sent to remote server
 * @param string $password Initialisation password
 * @author Morgan Harris
 **/
function simplesso_register_site($name,$url,$externalid,$password)
{
	//first assert that we are connecting by https
	if(substr($url,0,6) != "https:")
		print_error("SimpleSSO connections must be over SSL/TLS. Make sure your $name SSO url is prefixed with https:// (not http://).");
	
	global $DB;
	
	//insert into our DB
	$site = new stdClass();
	$site->name = $name;
	$site->url = $url;
	$id = $DB->insert_record('local_simplesso_sites',$site);
	if ($id == null) {
		print_error("Couldn't register site locally.");
	}
	$site->id = $id;
		
	return $site;
}

/**
 * Determines if site has been registered.
 *
 * @return site record on success, false on failure
 * @param string $url URL to remote SSO
 * @author Morgan Harris
 **/
function simplesso_site_for_url($url)
{
	global $DB;
	return $DB->get_record('local_simplesso_sites',array('url' => $url));
}

function simplesso_site_for_name($name)
{
	global $DB;
	return $DB->get_record('local_simplesso_sites',array('name' => $name));
}

/**
 * Sign on to the site specified
 *
 * @return void
 * @author Morgan Harris
 **/
function simplesso_sign_on($site,$externalid,$userid = NULL,$ipaddr = NULL)
{
	global $USER, $DB;
	
	if($userid == NULL)
		$userid = $USER->username;
	if($ipaddr == NULL) {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			$ipaddr = $_SERVER['HTTP_CLIENT_IP'];
		else
			$ipaddr = $_SERVER['REMOTE_ADDR'];
	}
		
	$msg = array(
		"action" => "signon",
		"externalid" => $externalid,
		"user" => $userid,
		"ipaddr" => $ipaddr
	);
	$retval = simplesso_api_call($site,$site->url,$msg);
	
	if($retval['status'] == 'success')
	{
		return $retval['ident'];
	}
	
	return false;
	
}

function simplesso_api_call($site,$url,$params)
{
	
	global $DB;

	$nonce = simplesso_generate_key_pair($site->id);
	
	$params['key'] = $nonce->id;
	$params['keyval'] = $nonce->pskey;

	$retval = simplesso_send_post($params,$url);

	$DB->delete_records('local_simplesso_keys',array('id' => $nonce->id));
	
	return $retval;
	
}

/**
 * Generates and inserts a new key for a specific site.
 *
 * @param int $site The ID of the site. If not set, generates a truly random key.
 * @return key record
 * @author Morgan Harris
 **/
function simplesso_generate_key_pair($site = NULL)
{
	global $DB;
	
	//generate 64 random bytes
	$ret = "";
	$exists = 0;
	do {
		for($i = 0; $i < 32; $i++)
			$ret .= chr(round(mt_rand(0,255)));
		$ret = base64_encode($ret);
		if($site)
			$exists = $DB->count_records("local_simplesso_keys", array("site_id" => $site, "pskey" => $ret));
	} while($exists > 0);
	
	$r = new stdClass();
	$r->pskey = $ret;
	$r->site_id = $site;
	
	$id = $DB->insert_record('local_simplesso_keys',$r);
	$r->id = $id;

	return $r;
}

function simplesso_send_post($msg,$url)
{
    $c =  new curl(array('cache' => false));
    $response = $c->post($url, json_encode($msg));
    return json_decode($response,true);
}
