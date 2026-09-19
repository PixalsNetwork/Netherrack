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

class ProxiedSession {

    private Connection $connection;
    private bool $networkCompression;
    private ?String $player_name, $xuid;
    private ?Uuid $uuid;


    public function __construct(Connection $connection, bool $compression, ?String $player_name, ?Uuid $uuid, ?String $xuid){
        $this->connection = $connection;
        $this->networkCompression = $compression;
        $this->player_name = $player_name;
        $this->uuid = $uuid;
        $this->xuid = $xuid;
    }

    public function getConnection() : Connection { return $this->connection; }



    public function getCompression() : bool { return $this->networkCompression; }

    public function getPlayerName() : ?String { return $this->player_name; }

    public function getXUID() : ?String { return $this->xuid; }

    public function getUUID() : ?Uuid { return $this->uuid; }

    public function setCompression(bool $newCompression) : void { $this->networkCompression = $newCompression; }

    public function setPlayerName(String $name) : void { $this->player_name = $name; }

    public function setXUID(String $data) : void { $this->xuid = $data; }

    public function setUUID(Uuid $uuid) : void { $this->uuid = $uuid; }






}