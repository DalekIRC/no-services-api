<?php

require_once "../../config.php";

require_once "class/account.php";
require_once "class/channel.php";
require_once "class/db.php";
function verify_api_key_or_die($key = NULL)
{
    global $config;
    if (!$key)
        permission_denied();

    if (!in_array($key, $config['api keys']))    
        permission_denied();

}
function get_config($setting)
{
        global $config;

        $item = $config;
        foreach(explode("::", $setting) as $x)
        {
                if (isset($item[$x]))
                        $item = $item[$x];
                else
                        return NULL;
        }
        return $item;
}

function permission_denied()
{
    die_json(["error" => "Permission denied"]);
}

function die_json($i)
{
    die(json_encode($i));
}