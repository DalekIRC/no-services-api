<?php
require_once("../../common.php");
error_log("CONNECT FROM ".$_SERVER['REMOTE_ADDR']."\n");
// our response object
$key = $_SERVER['HTTP_X_API_KEY'] ?? null;
verify_api_key_or_die($key);

$d = json_decode(file_get_contents('php://input'), true);
$uid = (isset($d['uid'])) ? $d['uid'] : null;

$response = (object)[];
if ($uid)
    $response->uid = $uid;

if ($d['method'] == "register")
{
    if (!$d['account'] || !$d['email'] || !$d['password'])
    {
        $response->error = "Invalid request";
        $response->code = "INVALID_REQ";
        die_json($request);
    }
    $error = NULL;
    $errorcode = NULL;
    $i = Account::Register($d['account'], $d['email'], $d['password'], $error, $errorcode);
    if (!$i)
    {
        $response->error = $error;
        $response->code = $errorcode;
        $response->account = $d['account'];
        die_json($response);
    }
    $response->account = $d['account'];
    $response->success = "Account registered to \"".$d['account']."\" with email \"".$d['email']."\""; 
    die_json($response);
}

if ($d['method'] == "identify")
{
    if (!$d['auth'] || !$d['password'])
    {
        $response->error = "Invalid request";
        $response->code = "INVALID_REQ";
        die_json($request);
    }
    $i = 0;
    if (strstr($d['auth'],"@"))
        $i = Account::identify(null, $d['auth'], $d['password']);
    else
        $i = Account::identify($d['auth'], null, $d['password']);

    if (!$i)
    {
        $response->account = $d['auth'];
        $response->error = "Invalid credentials";
        $response->code = "BAD_LOGIN";
        die_json($response);
    }
    $response->success = "Success";
    $response->account = $i;
    die_json($response);
}

if ($d['method'] == "list")
{
    $response->list = Account::list();
    die_json($response);
}