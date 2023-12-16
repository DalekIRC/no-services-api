<?php
require_once("../../common.php");

$key = $_SERVER['HTTP_X_API_KEY'] ?? null;
verify_api_key_or_die($key);

$d = json_decode(file_get_contents('php://input'), true);
$uid = (isset($d['uid'])) ? $d['uid'] : null;

$response = (object)[];
if ($uid)
    $response->uid = $uid;

foreach($d as $k => $v)
    error_log("$k => $v");

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

if ($d['method'] == "find")
{
    $user = new Account($d['account']);
    if (!$user->user)
    {
        $response->error = "No such account";
        $response->code = "NO_SUCH_ACCOUNT";
        die_json($response);
    }
    $response->user = $user->user;
    $response->success = "Success";
    die_json($response);
}

if ($d['method'] == "identify")
{
    if (!$d['auth'] || !$d['password'])
    {
        $response->error = "Invalid request";
        $response->code = "INVALID_REQ";
        die_json($response);
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

if ($d['method'] == "ajoin add")
{
    $response->type = "add";
    $response->channel = $d['channel'];
    $list = Account::get_meta_by_key($d['account'], "ajoin");
    foreach ($list as $chan)
    {
        if (strtolower($d['channel']) == strtolower($chan['meta_value']))
        { 
            $response->error = "That channel is already on your auto-join list";
            $response->code = "AJOIN_ENTRY_EXISTS";
            die_json($response);
        }
    }
    if (Account::add_meta($d['account'], "ajoin", $d['channel']))
    {
        $response->success = "Success";
        die_json($response);
    }
}
if ($d['method'] == "ajoin del")
{
    $response->type = "del";
    $response->channel = $d['channel'];
    if (Account::del_meta($d['account'], "ajoin", $d['channel']))
    {
        $response->success = "Success";
        die_json($response);
    }
    $response->error = "Channel was not on your autojoin list";
    $response->code = "AJOIN_ENTRY_DOES_NOT_EXIST";
    die_json($response);
}
if ($d['method'] == "ajoin list")
{
    $response->type = "list";
    if (($list = Account::get_meta_by_key("Valware", "ajoin")))
    {
        $cleaned = [];
        foreach($list as $meta)
            $cleaned[] = $meta['meta_value'];

        $response->success = "Success";
        $response->autojoin = (object)$cleaned;
        die_json($response);
    }
    $response->error = "Your auto-join list is empty";
    $response->code = "AJOIN_LIST_EMPTY";
    die_json($response);
}