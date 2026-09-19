<?php

/**
 *   _   _      _   _                    _    
 *  | \ | | ___| |_| |__   ___ _ __ _ __ __ _  ___| | __
 *  |  \| |/ _ \ __| '_ \ / _ \ '__| '__/ _` |/ __| |/ /
 *  | |\  |  __/ |_| | | |  __/ |  | | | (_| | (__|   <
 *  |_| \_|\___|\__|_| |_|\___|_|  |_|  \__,_|\___|_|\_\
 *
 *  Netherrack
 *
 *  A Minecraft: Bedrock Edition networking & proxy project
 *
 *  Developed by:
 *  Pixals' Department of Innovation & Technology
 *
 *  © Pixals Technologies
 */


namespace Nether\railway\engines\config;


use Nether\ProxyServer;

final class ConfigEngine {


    private ProxyServer $server;
    private const FILE =  __DIR__ . "/../../../../" . "/config.json";

    public function __construct(ProxyServer $server) {
        $this->server = $server;
    }


    public function createProxyConfig() : void {

        if(!is_file(self::FILE)) {
            file_put_contents(self::FILE, $this->generateBasicConfig());
        }
    }

    public function readConfig() : array {
        $config = file_get_contents(self::FILE);
        $readJson = json_decode($config, true, flags: JSON_PRETTY_PRINT);

        return $readJson;
    }

    public function get(String $path) : mixed {
        $keys = explode(".", $path);
        
        $value = $this->readConfig();
        foreach($keys as $key) {
            $value = $value[$key];
        }

        return $value;
    }

    public function set(String $path, mixed $data) : void {
        $keys = explode(".", $path);
        $value = $this->readConfig();

        $ref =& $value;

        foreach($keys as $key) {
            $ref =& $ref[$key];
        }

        $ref = $data;

        $da = json_encode($value, JSON_PRETTY_PRINT);

        file_put_contents(self::FILE, $da);
        

    }

    private function generateBasicConfig() : String {
        $config = [
            "server_settings" => [
                "binding_address" => "0.0.0.0",
                "port" => 19132,
                "server_motd" => "Netherrack",
                "level_name" => "",
                "max_players" => 20,
                "default_gamemode" => 0
            ],
            "downstreams" => [
                "lobby" => [
                    "server_address" => "127.0.0.1",
                    "port" => 10133,
                ]
            ],
            "users" => [
                "oPinqzz" => [
                    "permissions" => [
                        "netherrack.player.transfer",
                        "netherrack.player.where"
                    ]
                ]
            ]
        ];

        $json_config = json_encode($config, JSON_PRETTY_PRINT);

        return $json_config;


    }



}