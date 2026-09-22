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


namespace Nether\railway\transportObj;




final class DownstreamServer {

    private array $data;


    public function __construct(array $data){
        $this->data = $data;
    }

    public function getAddress() : String {
        return $this->data["server_address"];
    }

    public function getPort() :int {
        return $this->data["port"];
    }

    public function getDownstreamName() : String {
        return $this->data["name"];
    }

}