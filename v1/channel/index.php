<?php
require_once("../../common.php");
require_once "../class/account.php";
require_once "../class/channel.php";
require_once "../class/db.php";

$key = $_SERVER['HTTP_X_API_KEY'] ?? null;
verify_api_key_or_die($key);


$d = json_decode(file_get_contents('php://input'), true);
$uid = (isset($d['uid'])) ? $d['uid'] : null;

if (!$uid)
	die(json_encode(["error" => "Invalid request"]));

if (isset($d['account']))
	$response->account = $d['account'];

if (isset($d['responder']))
	$response->responder = $d['responder'];

// our response object
$response = (object)[];
$response->uid = $d['uid'];
/** Register a channel */
if ($d['method'] == "register")
{
	if (!$d['channel'] || !$d['account'])
	{
		$response->error = "Invalid request";
		$response->code = "INVALID_REQ";
		die_json($response);
	}
	$error = NULL;
	$errorcode = NULL;
	$c = Channel::Register($d['channel'], $error, $errorcode);
	$response->channel = $d['channel'];
	if (!$c)
	{
		$response->error = "There has been an error.";
		$response->code = "ERROR";
		die_json($response);
	}
	$response->success = "Channel registered to \"".$d['account']."\""; 
	die_json($response);
}

/** Fetch information about a channel */
if ($d['method'] == "find")
{
	if (!$d['channel'])
	{
		$response->error = "Invalid request";
		$response->code = "INVALID_REQ";
		die_json($response);
	}
	$c = Channel::find($d['channel']);
	if (!$c)
	{
		$response->error = "That channel is not registered.";
		$response->code = "CHANNEL_NOT_REGISTERED";
		die_json($response);
	}
	$response->channel = $c;
	die_json($response);
}

if ($d['method'] == "list")
{
	$response->list = Channel::list();
	die($response);
}