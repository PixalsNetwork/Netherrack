<?php


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