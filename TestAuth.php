<?php


$api = json_decode(file_get_contents("https://authorization.franchise.minecraft-services.net/.well-known/openid-configuration"), true);
$keys = json_decode(file_get_contents($api["jwks_uri"]), true);
var_dump($keys);