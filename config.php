<?php

global $config;

$config = [
    /* SQL Information */
    "sql" => [
        "ip" => "127.0.0.1",
        "password" => "securepassword",
        "username" => "ircd",
        "database" => "no-services"
    ],

    /* API Key for UnrealIRCd */
    "api keys" => [
        "put some api keys here",
  	"another api key",
    ],

    /* Minimum password strength
     * Minimum: 1 (very weak)
     * Maximum: 5 (very strong)
     */
    "minimum password strength" => 5,

];
