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




class SessionManager {

    private static array $sessions = [];
    
    public function createSession(ProxiedSession $session) {
        self::$sessions[$session->getConnection()->getNetworkId()] = $session;
    }

    public function getSession(string $networkID): ?ProxiedSession {
        return self::$sessions[$networkID];
    }

    public function getSessions() : array {
        return self::$sessions;
    }

    public function destroySession(String $networkID) : void {
        if(isset(self::$sessions[$networkID])) {
            unset(self::$sessions[$networkID]);
        }
    }


}