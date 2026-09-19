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


namespace Nether\player\sessions;




final class Connection {

    private String $ip;
    private int $port;
    private String $networkID;

    public function __construct(String $con_address, string $nid){
        $address = explode(":", $con_address);
        $this->ip = $address[0];
        $this->port = $address[1];
        $this->networkID = $nid;
    }

    public function getAddress() : string { return $this->ip; }
    public function getPort() : int { return $this->port; }
    public function getRemoteAddress() : string { return $this->ip . ":" . strval($this->port); }
    public function getNetworkId() : string { return $this->networkID; }


}