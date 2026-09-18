<?php


namespace Nether\player\sessions;




class SessionManager {

    private static array $sessions;
    
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
        unset(self::$sessions[$networkID]);
    }


}