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


namespace Nether\network\engines\auth\authobjects;




final class JWT {

    public ?array $decryptedHeader;
    public ?array $decryptedPayload;

    public ?String $header;
    public ?String $payload;
    public ?String $signature;

    public function __construct(String $jwt) {
        $jwt_exploded = explode(".", $jwt);

        $header = $jwt_exploded[0];
        $payload = $jwt_exploded[1];
        $signature = $jwt_exploded[2];

        $this->header = $header;
        $this->payload = $payload;
        $this->signature = $signature;

        $this->decryptedHeader = $this->decryptJWT($header);
        $this->decryptedPayload = $this->decryptJWT($payload);

    }

    private function decryptJWT(String $payload) : ?array {
        $json_payload = base64_decode($payload);
        $data = json_decode($json_payload, true);

        return $data;
    }


}