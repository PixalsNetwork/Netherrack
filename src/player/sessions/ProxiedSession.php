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

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ProxiedSession {

    private Connection $connection;
    private bool $networkCompression;
    private ?String $player_name, $xuid;
    private ?UuidInterface $uuid;


    public function __construct(Connection $connection, bool $compression, ?String $player_name, ?String $xuid){
        $this->connection = $connection;
        $this->networkCompression = $compression;
        $this->player_name = $player_name;
        $this->uuid = $this->calculateUuidFromXuid($xuid);
        $this->xuid = $xuid;
    }

    public function getConnection() : Connection { return $this->connection; }



    public function getCompression() : bool { return $this->networkCompression; }

    public function getName() : ?String { return $this->player_name; }

    public function getXUID() : ?String { return $this->xuid; }

    public function getUUID() : ?UuidInterface { return $this->uuid; }

    public function setCompression(bool $newCompression) : void { $this->networkCompression = $newCompression; }

    public function setPlayerName(String $name) : void { $this->player_name = $name; }

    public function setXUID(String $data) : void { $this->xuid = $data; }

    public function setUUID() : void {
        $this->uuid = $this->calculateUuidFromXuid($this->xuid);    
    }

    // Thanks, Altay!
    private function calculateUuidFromXuid(?string $xuid) : ?UuidInterface{
        if($xuid != null) {
            $hash = md5("pocket-auth-1-xuid:" . $xuid, binary: true);
		    $hash[6] = chr((ord($hash[6]) & 0x0f) | 0x30); // set version to 3
		    $hash[8] = chr((ord($hash[8]) & 0x3f) | 0x80); // set variant to RFC 4122

		    return Uuid::fromBytes($hash);
        } else {
            return null;
        }


	}






}